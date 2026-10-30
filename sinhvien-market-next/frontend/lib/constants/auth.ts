export const SECURITY_QUESTIONS = [
  { value: "q1", label: "Tên trường cấp 1 của bạn là gì?" },
  { value: "q2", label: "Tên thú cưng đầu tiên của bạn?" },
  { value: "q3", label: "Bạn thân thời thơ ấu của bạn tên gì?" },
] as const;

export type SecurityQuestionId = (typeof SECURITY_QUESTIONS)[number]["value"];

export const LOGIN_MAX_ATTEMPTS = 5;
export const LOGIN_LOCK_MINUTES = 15;
export const OTP_LENGTH = 6;
export const OTP_MAX_FAILS = 5;
export const OTP_RESEND_MAX = 3;
export const OTP_RESEND_WINDOW_MINUTES = 10;
export const REGISTER_MAX = 3;
export const REGISTER_WINDOW_MINUTES = 10;
export const OTP_EXPIRE_MINUTES = 15;
export const RESET_TOKEN_EXPIRE_MINUTES = 15;

export const RECENT_ACCOUNTS_KEY = "svmarket_recent_accounts";
