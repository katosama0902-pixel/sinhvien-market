import { z } from "zod";
import { OTP_LENGTH } from "@/lib/constants/auth";

const emailField = z
  .string()
  .trim()
  .min(1, "Email không hợp lệ.")
  .pipe(z.email("Email không hợp lệ."));

export const passwordField = z
  .string()
  .min(8, "Mật khẩu phải có ít nhất 8 ký tự.")
  .refine(
    (v) => /[A-Z]/.test(v) && /[0-9]/.test(v),
    "Mật khẩu phải chứa ít nhất 1 chữ hoa và 1 chữ số.",
  );

export const loginSchema = z.object({
  email: emailField,
  password: z.string().min(1, "Vui lòng nhập mật khẩu."),
});
export type LoginInput = z.infer<typeof loginSchema>;

const SECURITY_MSG = "Vui lòng điền câu hỏi và câu trả lời bảo mật.";

export const registerSchema = z
  .object({
    name: z
      .string()
      .trim()
      .min(2, "Họ tên từ 2 - 100 ký tự.")
      .max(100, "Họ tên từ 2 - 100 ký tự."),
    email: emailField,
    phone: z
      .string()
      .trim()
      .refine(
        (v) => v === "" || /^(0|\+84)[0-9]{8,10}$/.test(v),
        "Số điện thoại không hợp lệ.",
      ),
    securityQuestion: z.enum(["q1", "q2", "q3"], { error: SECURITY_MSG }),
    securityAnswer: z.string().trim().min(1, SECURITY_MSG),
    password: passwordField,
    passwordConfirm: z.string(),
  })
  .refine((d) => d.password === d.passwordConfirm, {
    path: ["passwordConfirm"],
    message: "Mật khẩu xác nhận không khớp.",
    when: () => true,
  });
export type RegisterInput = z.infer<typeof registerSchema>;

export const forgotEmailSchema = z.object({ email: emailField });
export type ForgotEmailInput = z.infer<typeof forgotEmailSchema>;

export const forgotAnswerSchema = z.object({
  email: emailField,
  securityAnswer: z.string().trim().min(1, "Vui lòng nhập câu trả lời."),
});
export type ForgotAnswerInput = z.infer<typeof forgotAnswerSchema>;

export const otpSchema = z.object({
  otp: z
    .string()
    .trim()
    .regex(new RegExp(`^[0-9]{${OTP_LENGTH}}$`), `Mã OTP gồm ${OTP_LENGTH} chữ số.`),
});
export type OtpInput = z.infer<typeof otpSchema>;

export const resetPasswordSchema = z
  .object({
    email: emailField,
    token: z.string().min(1),
    password: passwordField,
    passwordConfirm: z.string(),
  })
  .refine((d) => d.password === d.passwordConfirm, {
    path: ["passwordConfirm"],
    message: "Mật khẩu xác nhận không khớp.",
    when: () => true,
  });
export type ResetPasswordInput = z.infer<typeof resetPasswordSchema>;

export function zodFieldErrors(error: z.ZodError): Record<string, string> {
  const out: Record<string, string> = {};
  for (const issue of error.issues) {
    const key = String(issue.path[0] ?? "_");
    if (!(key in out)) out[key] = issue.message;
  }
  return out;
}
