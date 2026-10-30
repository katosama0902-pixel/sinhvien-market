import { describe, expect, it } from "vitest";
import {
  forgotAnswerSchema,
  loginSchema,
  otpSchema,
  registerSchema,
  resetPasswordSchema,
  zodFieldErrors,
} from "@/lib/validation/auth";
import { getPasswordStrength } from "@/lib/validation/password-strength";

const validRegister = {
  name: "Nguyễn Văn A",
  email: "a@demo.vn",
  phone: "0912345678",
  securityQuestion: "q1" as const,
  securityAnswer: "abc",
  password: "Demo1234",
  passwordConfirm: "Demo1234",
};

describe("loginSchema", () => {
  it("chấp nhận email + mật khẩu hợp lệ", () => {
    expect(loginSchema.safeParse({ email: "a@b.vn", password: "x" }).success).toBe(true);
  });
  it("từ chối email sai định dạng", () => {
    const r = loginSchema.safeParse({ email: "abc", password: "x" });
    expect(r.success).toBe(false);
    if (!r.success) expect(zodFieldErrors(r.error).email).toBe("Email không hợp lệ.");
  });
  it("từ chối mật khẩu rỗng", () => {
    const r = loginSchema.safeParse({ email: "a@b.vn", password: "" });
    expect(r.success).toBe(false);
    if (!r.success) expect(zodFieldErrors(r.error).password).toBe("Vui lòng nhập mật khẩu.");
  });
});

describe("registerSchema", () => {
  it("chấp nhận dữ liệu hợp lệ", () => {
    expect(registerSchema.safeParse(validRegister).success).toBe(true);
  });
  it("cho phép bỏ trống số điện thoại", () => {
    expect(registerSchema.safeParse({ ...validRegister, phone: "" }).success).toBe(true);
  });
  it("chấp nhận số điện thoại +84", () => {
    expect(registerSchema.safeParse({ ...validRegister, phone: "+84912345678" }).success).toBe(true);
  });
  it("từ chối số điện thoại sai", () => {
    const r = registerSchema.safeParse({ ...validRegister, phone: "123" });
    expect(r.success).toBe(false);
    if (!r.success) expect(zodFieldErrors(r.error).phone).toBe("Số điện thoại không hợp lệ.");
  });
  it("từ chối họ tên quá ngắn", () => {
    const r = registerSchema.safeParse({ ...validRegister, name: "A" });
    expect(r.success).toBe(false);
  });
  it.each([
    ["Demo123", "ngắn hơn 8 ký tự"],
    ["demo1234", "thiếu chữ hoa"],
    ["DemoDemo", "thiếu chữ số"],
  ])("từ chối mật khẩu %s (%s)", (password) => {
    const r = registerSchema.safeParse({ ...validRegister, password, passwordConfirm: password });
    expect(r.success).toBe(false);
  });
  it("báo lỗi xác nhận mật khẩu không khớp", () => {
    const r = registerSchema.safeParse({ ...validRegister, passwordConfirm: "Khac1234" });
    expect(r.success).toBe(false);
    if (!r.success) expect(zodFieldErrors(r.error).passwordConfirm).toBe("Mật khẩu xác nhận không khớp.");
  });
  it("vẫn báo lỗi xác nhận khi trường khác cũng lỗi", () => {
    const r = registerSchema.safeParse({ ...validRegister, name: "", passwordConfirm: "Khac1234" });
    expect(r.success).toBe(false);
    if (!r.success) {
      const e = zodFieldErrors(r.error);
      expect(e.name).toBeDefined();
      expect(e.passwordConfirm).toBe("Mật khẩu xác nhận không khớp.");
    }
  });
  it("từ chối khi chưa chọn câu hỏi bảo mật", () => {
    const r = registerSchema.safeParse({ ...validRegister, securityQuestion: "" });
    expect(r.success).toBe(false);
    if (!r.success) expect(zodFieldErrors(r.error).securityQuestion).toBe("Vui lòng điền câu hỏi và câu trả lời bảo mật.");
  });
});

describe("forgotAnswerSchema", () => {
  it("từ chối câu trả lời rỗng", () => {
    expect(forgotAnswerSchema.safeParse({ email: "a@b.vn", securityAnswer: "  " }).success).toBe(false);
  });
});

describe("otpSchema", () => {
  it("chấp nhận 6 chữ số", () => {
    expect(otpSchema.safeParse({ otp: "123456" }).success).toBe(true);
  });
  it.each(["12345", "1234567", "12345a", ""])("từ chối %j", (otp) => {
    expect(otpSchema.safeParse({ otp }).success).toBe(false);
  });
});

describe("resetPasswordSchema", () => {
  const base = { email: "a@b.vn", token: "t", password: "NewPass123", passwordConfirm: "NewPass123" };
  it("chấp nhận dữ liệu hợp lệ", () => {
    expect(resetPasswordSchema.safeParse(base).success).toBe(true);
  });
  it("áp dụng cùng quy tắc mật khẩu chặt", () => {
    expect(resetPasswordSchema.safeParse({ ...base, password: "newpass123", passwordConfirm: "newpass123" }).success).toBe(false);
  });
  it("từ chối xác nhận không khớp", () => {
    expect(resetPasswordSchema.safeParse({ ...base, passwordConfirm: "x" }).success).toBe(false);
  });
});

describe("getPasswordStrength", () => {
  it.each([
    ["", 0],
    ["abc", 0],
    ["abcdefgh", 1],
    ["Abcdefgh", 2],
    ["Abcdefg1", 3],
    ["Abcdef1!", 4],
  ])("%j -> điểm %i", (pw, score) => {
    expect(getPasswordStrength(pw).score).toBe(score);
  });
  it("percent = score * 25", () => {
    expect(getPasswordStrength("Abcdef1!").percent).toBe(100);
  });
});
