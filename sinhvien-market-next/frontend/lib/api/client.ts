import { API_TIMEOUT_MS, API_URL } from "@/lib/config";
import { ApiError, type ApiErrorBody } from "./types";

interface RequestOptions {
  method?: "GET" | "POST" | "PUT" | "PATCH" | "DELETE";
  body?: unknown;
}

let csrfToken: string | null = null;

async function getCsrfToken(): Promise<string> {
  if (csrfToken) return csrfToken;

  let response: Response;
  try {
    response = await fetch(`${API_URL}/auth/csrf`, {
      credentials: "include",
      headers: { Accept: "application/json" },
    });
  } catch {
    throw new ApiError(
      "NETWORK_ERROR",
      "Không lấy được token bảo mật từ máy chủ.",
      { status: 0 },
    );
  }
  let json: unknown;
  try {
    json = await response.json();
  } catch {
    throw new ApiError(
      "SERVER_ERROR",
      "Máy chủ không trả token bảo mật hợp lệ.",
      { status: response.status },
    );
  }
  if (!response.ok) {
    throw new ApiError(
      "SERVER_ERROR",
      "Không thể khởi tạo phiên bảo mật.",
      { status: response.status },
    );
  }
  const token = (json as { data?: { token?: unknown } })?.data?.token;
  if (typeof token !== "string" || token.length === 0) {
    throw new ApiError(
      "SERVER_ERROR",
      "Máy chủ không trả token bảo mật hợp lệ.",
      { status: response.status },
    );
  }
  csrfToken = token;
  return token;
}

export async function apiRequest<T>(
  path: string,
  { method = "GET", body }: RequestOptions = {},
): Promise<T> {
  const controller = new AbortController();
  const timer = setTimeout(() => controller.abort(), API_TIMEOUT_MS);

  let res: Response;
  try {
    const formData =
      typeof FormData !== "undefined" && body instanceof FormData;
    const token = method === "GET" ? null : await getCsrfToken();
    res = await fetch(`${API_URL}${path}`, {
      method,
      credentials: "include",
      signal: controller.signal,
      headers: {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest",
        ...(token ? { "X-CSRF-Token": token } : {}),
        ...(body !== undefined && !formData
          ? { "Content-Type": "application/json" }
          : {}),
      },
      body:
        body === undefined
          ? undefined
          : formData
            ? body
            : JSON.stringify(body),
    });
  } catch (e) {
    if (e instanceof DOMException && e.name === "AbortError") {
      throw new ApiError(
        "TIMEOUT",
        "Máy chủ phản hồi quá lâu. Vui lòng thử lại sau.",
        { status: 408 },
      );
    }
    throw new ApiError(
      "NETWORK_ERROR",
      "Không thể kết nối tới máy chủ. Vui lòng kiểm tra mạng và thử lại.",
      { status: 0 },
    );
  } finally {
    clearTimeout(timer);
  }

  let json: unknown = null;
  try {
    json = await res.json();
  } catch {
    // response empty or non-JSON
  }

  if (!res.ok) {
    const err = (json as Partial<ApiErrorBody> | null)?.error;
    if (err?.code && err.message) {
      throw new ApiError(err.code, err.message, {
        status: res.status,
        fields: err.fields,
        meta: err.meta,
      });
    }
    throw new ApiError(
      res.status >= 500 ? "SERVER_ERROR" : "UNKNOWN",
      res.status >= 500
        ? "Máy chủ đang gặp sự cố. Vui lòng thử lại sau."
        : "Đã có lỗi xảy ra. Vui lòng thử lại.",
      { status: res.status },
    );
  }

  return (json as { data: T }).data;
}
