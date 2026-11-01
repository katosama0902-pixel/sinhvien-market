export type Role = "student" | "admin";

export interface AuthUser {
  id: number;
  name: string;
  email: string;
  role: Role;
}

export interface LockInfo {
  name?: string;
  email: string;
  reason: string | null;
  lockedAt: string | null;
  lockedUntil: string | null;
}

export interface LoginRequest {
  role: Role;
  email: string;
  password: string;
}

export type LoginResult =
  | {
      status: "ok";
      user: AuthUser;
      redirectTo: "/products" | "/admin";
      message: string;
    }
  | { status: "verify_otp"; email: string; message: string }
  | { status: "locked"; lock: LockInfo };

export interface RegisterRequest {
  name: string;
  email: string;
  phone: string;
  securityQuestion: "q1" | "q2" | "q3";
  securityAnswer: string;
  password: string;
  passwordConfirm: string;
}

export interface RegisterResult {
  status: "verify_otp";
  email: string;
  message: string;
}

export interface OtpSession {
  email: string;
  role?: Role;
  resendRemaining: number;
}

export interface VerifyOtpResult {
  user: AuthUser;
  redirectTo: "/products" | "/admin";
  message: string;
}

export interface ResendOtpResult {
  resendRemaining: number;
  message: string;
}

export interface SecurityQuestionResult {
  email: string;
  question: string;
}

export interface ForgotAnswerResult {
  message: string;
  devResetLink?: string;
}

export interface ResetPasswordRequest {
  email: string;
  token: string;
  password: string;
  passwordConfirm: string;
}

export interface MessageResult {
  message: string;
}

export type ApiErrorCode =
  | "VALIDATION_ERROR"
  | "INVALID_CREDENTIALS"
  | "RATE_LIMITED"
  | "EMAIL_EXISTS"
  | "REGISTRATION_FAILED"
  | "OTP_DELIVERY_FAILED"
  | "WRONG_ROLE"
  | "NO_VERIFY_SESSION"
  | "OTP_INVALID"
  | "OTP_EXPIRED"
  | "OTP_SESSION_CANCELLED"
  | "ACCOUNT_LOCKED"
  | "EMAIL_NOT_FOUND"
  | "NO_SECURITY_QUESTION"
  | "SECURITY_ANSWER_INCORRECT"
  | "RESET_EMAIL_FAILED"
  | "RESET_LINK_INVALID"
  | "INVALID_PASSWORD"
  | "NOT_AUTHENTICATED"
  | "UNAUTHORIZED"
  | "ACCOUNT_LOCKED"
  | "NOT_FOUND"
  | "FORBIDDEN"
  | "INVALID_CSRF"
  | "LISTING_SOLD"
  | "LISTING_HAS_TRANSACTION"
  | "INVALID_TRANSITION"
  | "EXCHANGE_UNSUPPORTED"
  | "INVALID_PURCHASE"
  | "AUCTION_ENDED"
  | "TRANSACTION_NOT_FOUND"
  | "PRODUCT_UNAVAILABLE"
  | "ALREADY_CHECKED_IN"
  | "LISTING_INACTIVE"
  | "BUMP_COOLDOWN"
  | "INSUFFICIENT_COINS"
  | "NOT_LOCKED"
  | "NOT_IMPLEMENTED"
  | "NETWORK_ERROR"
  | "TIMEOUT"
  | "SERVER_ERROR"
  | "UNKNOWN";

export interface ApiErrorMeta {
  attemptsLeft?: number;
  minutesLeft?: number;
  remaining?: number;
  resendRemaining?: number;
}

export interface ApiErrorBody {
  error: {
    code: ApiErrorCode;
    message: string;
    fields?: Record<string, string>;
    meta?: ApiErrorMeta;
  };
}

export class ApiError extends Error {
  readonly code: ApiErrorCode;
  readonly status: number;
  readonly fields?: Record<string, string>;
  readonly meta?: ApiErrorMeta;

  constructor(
    code: ApiErrorCode,
    message: string,
    options: {
      status?: number;
      fields?: Record<string, string>;
      meta?: ApiErrorMeta;
    } = {},
  ) {
    super(message);
    this.name = "ApiError";
    this.code = code;
    this.status = options.status ?? 400;
    this.fields = options.fields;
    this.meta = options.meta;
  }
}

export function isApiError(e: unknown): e is ApiError {
  return e instanceof ApiError;
}
