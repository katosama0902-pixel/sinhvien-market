import { API_TIMEOUT_MS, API_URL } from "@/lib/config";
import { ApiError, type ApiErrorBody } from "./types";

interface RequestOptions {
  method?: "GET" | "POST" | "PUT" | "PATCH" | "DELETE";
  body?: unknown;
}

export async function apiRequest<T>(
  path: string,
  { method = "GET", body }: RequestOptions = {},
): Promise<T> {
  const controller = new AbortController();
  const timer = setTimeout(() => controller.abort(), API_TIMEOUT_MS);

  let res: Response;
  try {
    res = await fetch(`${API_URL}${path}`, {
      method,
      credentials: "include",
      signal: controller.signal,
      headers: {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest",
        ...(body !== undefined ? { "Content-Type": "application/json" } : {}),
      },
      body: body !== undefined ? JSON.stringify(body) : undefined,
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
