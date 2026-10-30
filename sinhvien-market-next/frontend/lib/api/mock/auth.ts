import {
  LOGIN_LOCK_MINUTES,
  LOGIN_MAX_ATTEMPTS,
  OTP_EXPIRE_MINUTES,
  OTP_MAX_FAILS,
  OTP_RESEND_MAX,
  OTP_RESEND_WINDOW_MINUTES,
  REGISTER_MAX,
  REGISTER_WINDOW_MINUTES,
  RESET_TOKEN_EXPIRE_MINUTES,
  SECURITY_QUESTIONS,
} from "@/lib/constants/auth";
import {
  forgotEmailSchema,
  loginSchema,
  registerSchema,
  resetPasswordSchema,
  zodFieldErrors,
} from "@/lib/validation/auth";
import {
  ApiError,
  type AuthUser,
  type ForgotAnswerResult,
  type LockInfo,
  type LoginRequest,
  type LoginResult,
  type MessageResult,
  type OtpSession,
  type RegisterRequest,
  type RegisterResult,
  type ResendOtpResult,
  type ResetPasswordRequest,
  type Role,
  type SecurityQuestionResult,
  type VerifyOtpResult,
} from "../types";

export const DEMO_OTP = "123456";
export const DEMO_SECURITY_ANSWER = "demo";

interface MockUser {
  id: number;
  name: string;
  email: string;
  password: string;
  role: Role;
  verified: boolean;
  lock: { reason: string | null; lockedAt: string; lockedUntil: string | null } | null;
  securityQuestion: "q1" | "q2" | "q3";
  securityAnswer: string;
}

const users: MockUser[] = [
  {
    id: 1,
    name: "Nguyễn Minh Anh",
    email: "student@demo.vn",
    password: "Demo1234",
    role: "student",
    verified: true,
    lock: null,
    securityQuestion: "q1",
    securityAnswer: DEMO_SECURITY_ANSWER,
  },
  {
    id: 2,
    name: "Quản trị viên",
    email: "admin@demo.vn",
    password: "Admin1234",
    role: "admin",
    verified: true,
    lock: null,
    securityQuestion: "q1",
    securityAnswer: DEMO_SECURITY_ANSWER,
  },
  {
    id: 5,
    name: "Quản trị viên Hệ thống",
    email: "admin@sinhvienmarket.edu.vn",
    password: "Admin123",
    role: "admin",
    verified: true,
    lock: null,
    securityQuestion: "q1",
    securityAnswer: DEMO_SECURITY_ANSWER,
  },
  {
    id: 3,
    name: "Trần Quốc Bảo",
    email: "unverified@demo.vn",
    password: "Demo1234",
    role: "student",
    verified: false,
    lock: null,
    securityQuestion: "q2",
    securityAnswer: DEMO_SECURITY_ANSWER,
  },
  {
    id: 4,
    name: "Lê Thu Hà",
    email: "locked@demo.vn",
    password: "Demo1234",
    role: "student",
    verified: true,
    lock: {
      reason: "Đăng tin lừa đảo nhiều lần",
      lockedAt: "2026-10-15T09:30:00+07:00",
      lockedUntil: null,
    },
    securityQuestion: "q3",
    securityAnswer: DEMO_SECURITY_ANSWER,
  },
];

let nextId = 100;

function delay(): Promise<void> {
  const ms = 300 + Math.floor(Math.random() * 500);
  return new Promise((resolve) => setTimeout(resolve, ms));
}

const norm = (s: string) => s.trim().toLowerCase();
const findUser = (email: string) => users.find((u) => norm(u.email) === norm(email));

function readStore<T>(key: string): T | null {
  if (typeof sessionStorage === "undefined") return null;
  try {
    const raw = sessionStorage.getItem(key);
    return raw ? (JSON.parse(raw) as T) : null;
  } catch {
    return null;
  }
}

function writeStore(key: string, value: unknown) {
  if (typeof sessionStorage === "undefined") return;
  try {
    sessionStorage.setItem(key, JSON.stringify(value));
  } catch {
    // ignore storage errors
  }
}

function removeStore(key: string) {
  if (typeof sessionStorage === "undefined") return;
  try {
    sessionStorage.removeItem(key);
  } catch {
    // ignore storage errors
  }
}

const KEY = {
  loginAttempts: "svm_mock_login_attempts_v2",
  registerAttempts: "svm_mock_register_attempts",
  session: "svm_mock_session",
  lock: "svm_mock_lock",
  otp: "svm_mock_otp",
} as const;

interface Counter {
  count: number;
  at: number;
}

interface OtpState {
  email: string;
  failCount: number;
  resendCount: number;
  resendFirstAt: number;
  issuedAt: number;
}

const minutesLeft = (startedAt: number, windowMin: number) =>
  Math.max(1, Math.ceil((windowMin * 60_000 - (Date.now() - startedAt)) / 60_000));

const toAuthUser = (u: MockUser): AuthUser => ({
  id: u.id,
  name: u.name,
  email: u.email,
  role: u.role,
});

function startSession(u: MockUser) {
  writeStore(KEY.session, toAuthUser(u));
}

const questionLabel = (id: string) =>
  SECURITY_QUESTIONS.find((q) => q.value === id)?.label ?? "Câu hỏi bảo mật của bạn";

export async function mockLogin(input: LoginRequest): Promise<LoginResult> {
  await delay();

  let attempts = readStore<Counter>(KEY.loginAttempts);
  if (attempts && Date.now() - attempts.at > LOGIN_LOCK_MINUTES * 60_000) {
    attempts = null;
    removeStore(KEY.loginAttempts);
  }
  const count = attempts?.count ?? 0;
  if (count >= LOGIN_MAX_ATTEMPTS) {
    const mins = minutesLeft(attempts!.at, LOGIN_LOCK_MINUTES);
    throw new ApiError(
      "RATE_LIMITED",
      `Quá nhiều lần thất bại. Thử lại sau ${mins} phút.`,
      { status: 429, meta: { minutesLeft: mins } },
    );
  }

  const parsed = loginSchema.safeParse(input);
  if (!parsed.success) {
    throw new ApiError("VALIDATION_ERROR", "Dữ liệu không hợp lệ.", {
      status: 422,
      fields: zodFieldErrors(parsed.error),
    });
  }
  const { email, password } = parsed.data;

  const user = findUser(email);
  if (!user || user.password !== password) {
    writeStore(KEY.loginAttempts, { count: count + 1, at: Date.now() } satisfies Counter);
    const left = LOGIN_MAX_ATTEMPTS - (count + 1);
    if (left <= 0) {
      throw new ApiError(
        "RATE_LIMITED",
        `Tài khoản bị cấm ${LOGIN_LOCK_MINUTES} phút do sai quá nhiều.`,
        { status: 429, meta: { minutesLeft: LOGIN_LOCK_MINUTES } },
      );
    }
    throw new ApiError(
      "INVALID_CREDENTIALS",
      `Sai email hoặc mật khẩu. Còn ${left} lần thử.`,
      { status: 401, meta: { attemptsLeft: left } },
    );
  }

  if (user.role !== input.role) {
    throw new ApiError(
      "WRONG_ROLE",
      "Email này không có quyền hạn truy cập cổng đăng nhập này.",
      { status: 403 },
    );
  }

  if (user.lock) {
    const lock: LockInfo = { name: user.name, email: user.email, ...user.lock };
    writeStore(KEY.lock, lock);
    return { status: "locked", lock };
  }

  if (!user.verified) {
    writeStore(KEY.otp, {
      email: user.email,
      failCount: 0,
      resendCount: 0,
      resendFirstAt: 0,
      issuedAt: Date.now(),
    } satisfies OtpState);
    return {
      status: "verify_otp",
      email: user.email,
      message: "Xác minh bảo mật: Mã OTP đang được gửi vào email của bạn.",
    };
  }

  removeStore(KEY.loginAttempts);
  startSession(user);
  return {
    status: "ok",
    user: toAuthUser(user),
    redirectTo: user.role === "admin" ? "/admin" : "/products",
    message: `Chào mừng trở lại, ${user.name}!`,
  };
}

export async function mockRegister(input: RegisterRequest): Promise<RegisterResult> {
  await delay();

  let reg = readStore<Counter>(KEY.registerAttempts);
  if (reg && Date.now() - reg.at > REGISTER_WINDOW_MINUTES * 60_000) {
    reg = null;
    removeStore(KEY.registerAttempts);
  }
  if ((reg?.count ?? 0) >= REGISTER_MAX) {
    const mins = minutesLeft(reg!.at, REGISTER_WINDOW_MINUTES);
    throw new ApiError(
      "RATE_LIMITED",
      `Bạn đã tạo quá nhiều tài khoản. Vui lòng đợi ${mins} phút rồi thử lại.`,
      { status: 429, meta: { minutesLeft: mins } },
    );
  }

  const parsed = registerSchema.safeParse(input);
  const fields = parsed.success ? {} : zodFieldErrors(parsed.error);
  if (!fields.email && findUser(input.email)) {
    fields.email = "Email này đã tồn tại.";
  }
  if (Object.keys(fields).length > 0 || !parsed.success) {
    throw new ApiError("VALIDATION_ERROR", "Dữ liệu không hợp lệ.", {
      status: 422,
      fields,
    });
  }

  const d = parsed.data;
  users.push({
    id: nextId++,
    name: d.name,
    email: d.email,
    password: d.password,
    role: "student",
    verified: false,
    lock: null,
    securityQuestion: d.securityQuestion,
    securityAnswer: norm(d.securityAnswer),
  });

  writeStore(KEY.registerAttempts, {
    count: (reg?.count ?? 0) + 1,
    at: reg?.at ?? Date.now(),
  } satisfies Counter);
  writeStore(KEY.otp, {
    email: d.email,
    failCount: 0,
    resendCount: 0,
    resendFirstAt: 0,
    issuedAt: Date.now(),
  } satisfies OtpState);

  return {
    status: "verify_otp",
    email: d.email,
    message: `Đăng ký thành công! Vui lòng nhập mã OTP đã được gửi đến email ${d.email}.`,
  };
}

function requireOtpState(): OtpState {
  const s = readStore<OtpState>(KEY.otp);
  if (!s) {
    throw new ApiError(
      "NO_VERIFY_SESSION",
      "Không có phiên xác minh. Vui lòng đăng nhập lại.",
      { status: 401 },
    );
  }
  return s;
}

function resendRemainingOf(s: OtpState): number {
  const expired =
    s.resendFirstAt > 0 &&
    Date.now() - s.resendFirstAt >= OTP_RESEND_WINDOW_MINUTES * 60_000;
  return expired ? OTP_RESEND_MAX : Math.max(0, OTP_RESEND_MAX - s.resendCount);
}

export async function mockGetOtpSession(): Promise<OtpSession> {
  await delay();
  const s = requireOtpState();
  return { email: s.email, resendRemaining: resendRemainingOf(s) };
}

export async function mockVerifyOtp(otp: string): Promise<VerifyOtpResult> {
  await delay();
  const s = requireOtpState();

  if (s.failCount >= OTP_MAX_FAILS) {
    removeStore(KEY.otp);
    throw new ApiError(
      "OTP_SESSION_CANCELLED",
      "Quá nhiều lần nhập sai OTP. Phiên xác minh đã bị hủy vì an toàn. Đăng nhập lại.",
      { status: 403 },
    );
  }

  if (otp.trim() !== DEMO_OTP) {
    const failCount = s.failCount + 1;
    const remaining = OTP_MAX_FAILS - failCount;
    if (remaining <= 0) {
      removeStore(KEY.otp);
      throw new ApiError(
        "OTP_SESSION_CANCELLED",
        "Quá nhiều lần nhập sai. Phiên xác minh bị hủy.",
        { status: 403 },
      );
    }
    writeStore(KEY.otp, { ...s, failCount } satisfies OtpState);
    throw new ApiError(
      "OTP_INVALID",
      `Mã OTP không chính xác. Còn ${remaining} lần thử.`,
      { status: 400, meta: { remaining } },
    );
  }

  if (Date.now() - s.issuedAt > OTP_EXPIRE_MINUTES * 60_000) {
    throw new ApiError(
      "OTP_EXPIRED",
      "Mã OTP đã hết hạn. Vui lòng nhấn Gửi lại mã.",
      { status: 400 },
    );
  }

  const user = findUser(s.email);
  if (!user) {
    removeStore(KEY.otp);
    throw new ApiError("NO_VERIFY_SESSION", "Không có phiên xác minh. Vui lòng đăng nhập lại.", {
      status: 401,
    });
  }
  user.verified = true;
  removeStore(KEY.otp);
  startSession(user);
  return {
    user: toAuthUser(user),
    redirectTo: "/products",
    message: "Xác minh thành công! Chào mừng bạn.",
  };
}

export async function mockResendOtp(): Promise<ResendOtpResult> {
  await delay();
  const s = requireOtpState();

  let { resendCount, resendFirstAt } = s;
  if (resendFirstAt > 0 && Date.now() - resendFirstAt >= OTP_RESEND_WINDOW_MINUTES * 60_000) {
    resendCount = 0;
    resendFirstAt = 0;
  }
  if (resendCount >= OTP_RESEND_MAX) {
    const mins = minutesLeft(resendFirstAt, OTP_RESEND_WINDOW_MINUTES);
    throw new ApiError(
      "RATE_LIMITED",
      `Bạn đã gửi lại OTP quá nhiều lần. Vui lòng đợi ${mins} phút rồi thử lại.`,
      { status: 429, meta: { minutesLeft: mins, resendRemaining: 0 } },
    );
  }

  const next: OtpState = {
    ...s,
    resendCount: resendCount + 1,
    resendFirstAt: resendCount === 0 ? Date.now() : resendFirstAt,
    issuedAt: Date.now(),
  };
  writeStore(KEY.otp, next);
  return {
    resendRemaining: OTP_RESEND_MAX - next.resendCount,
    message: "Đã gửi lại mã OTP. Vui lòng kiểm tra email.",
  };
}

export async function mockGetSecurityQuestion(
  email: string,
): Promise<SecurityQuestionResult> {
  await delay();
  const parsed = forgotEmailSchema.safeParse({ email });
  if (!parsed.success) {
    throw new ApiError("VALIDATION_ERROR", "Dữ liệu không hợp lệ.", {
      status: 422,
      fields: zodFieldErrors(parsed.error),
    });
  }
  const user = findUser(parsed.data.email);
  if (!user) {
    throw new ApiError("EMAIL_NOT_FOUND", "Email không tồn tại.", { status: 404 });
  }
  if (!user.securityQuestion) {
    throw new ApiError(
      "NO_SECURITY_QUESTION",
      "Email không tồn tại hoặc chưa cài đặt câu hỏi bảo mật.",
      { status: 404 },
    );
  }
  return { email: user.email, question: questionLabel(user.securityQuestion) };
}

function makeResetToken(email: string, ttlMinutes = RESET_TOKEN_EXPIRE_MINUTES) {
  const exp = Date.now() + ttlMinutes * 60_000;
  return `mock.${exp}.${btoa(norm(email))}`;
}

function checkResetToken(email: string, token: string): boolean {
  const [prefix, exp, enc] = token.split(".");
  if (prefix !== "mock" || !exp || !enc) return false;
  if (Number(exp) < Date.now()) return false;
  try {
    return atob(enc) === norm(email);
  } catch {
    return false;
  }
}

export async function mockAnswerSecurityQuestion(input: {
  email: string;
  securityAnswer: string;
}): Promise<ForgotAnswerResult> {
  await delay();
  const user = findUser(input.email);
  if (!user) {
    throw new ApiError("EMAIL_NOT_FOUND", "Email không tồn tại.", { status: 404 });
  }
  if (norm(input.securityAnswer) !== user.securityAnswer) {
    throw new ApiError(
      "SECURITY_ANSWER_INCORRECT",
      "Câu trả lời bảo mật không chính xác!",
      { status: 400 },
    );
  }
  const token = makeResetToken(user.email);
  return {
    message: "Hãy kiểm tra email để lấy liên kết đổi mật khẩu.",
    devResetLink: `/reset-password?token=${encodeURIComponent(token)}&email=${encodeURIComponent(user.email)}`,
  };
}

export async function mockResetPassword(
  input: ResetPasswordRequest,
): Promise<MessageResult> {
  await delay();
  const user = findUser(input.email);
  if (!user || !checkResetToken(input.email, input.token)) {
    throw new ApiError("RESET_LINK_INVALID", "Liên kết hết hạn hoặc sai.", {
      status: 400,
    });
  }
  const parsed = resetPasswordSchema.safeParse(input);
  if (!parsed.success) {
    throw new ApiError("VALIDATION_ERROR", "Dữ liệu không hợp lệ.", {
      status: 422,
      fields: zodFieldErrors(parsed.error),
    });
  }
  user.password = parsed.data.password;
  return { message: "Đổi mật khẩu thành công! Bạn có thể đăng nhập ngay." };
}

export async function mockGetLockInfo(): Promise<LockInfo> {
  await delay();
  const lock = readStore<LockInfo>(KEY.lock);
  if (!lock) {
    throw new ApiError("NOT_AUTHENTICATED", "Bạn cần đăng nhập để truy cập trang này.", {
      status: 401,
    });
  }
  const user = findUser(lock.email);
  if (user && !user.lock) {
    removeStore(KEY.lock);
    throw new ApiError("NOT_LOCKED", "Tài khoản không bị khoá.", { status: 409 });
  }
  return lock;
}

export async function mockGetCurrentUser(): Promise<AuthUser | null> {
  return readStore<AuthUser>(KEY.session);
}

export async function mockLogout(): Promise<void> {
  await delay();
  removeStore(KEY.session);
  removeStore(KEY.lock);
}
