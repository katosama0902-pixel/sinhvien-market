/** Cấu hình đọc từ biến môi trường (xem .env.example). Không chứa secret. */

export const USE_MOCK = process.env.NEXT_PUBLIC_USE_MOCK === "true";

export const API_URL = (
  process.env.NEXT_PUBLIC_API_URL ?? "/api"
).replace(/\/$/, "");

/** Thời gian chờ tối đa cho một lời gọi API thật (ms). */
export const API_TIMEOUT_MS = 15_000;
