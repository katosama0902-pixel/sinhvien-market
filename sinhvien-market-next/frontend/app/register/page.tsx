"use client";

import { useState } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { useForm, useWatch } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import {
  HelpCircle,
  LockKeyhole,
  Mail,
  PenLine,
  Phone,
  User,
  UserPlus,
} from "lucide-react";
import { AuthShell } from "@/components/auth/AuthShell";
import { FlashBanner } from "@/components/auth/FlashBanner";
import { GoogleAuthButton } from "@/components/auth/GoogleAuthButton";
import { PasswordStrengthBar } from "@/components/auth/PasswordStrengthBar";
import { Alert } from "@/components/ui/Alert";
import { Button } from "@/components/ui/Button";
import { Input } from "@/components/ui/Input";
import { PasswordInput } from "@/components/ui/PasswordInput";
import { Select } from "@/components/ui/Select";
import { register as registerAccount } from "@/lib/api/auth";
import { isApiError } from "@/lib/api/types";
import { SECURITY_QUESTIONS } from "@/lib/constants/auth";
import { clearFlash, setFlash } from "@/lib/flash";
import { applyApiFieldErrors } from "@/lib/forms";
import { useGuestRedirect } from "@/lib/use-guest-redirect";
import { registerSchema, type RegisterInput } from "@/lib/validation/auth";

const REGISTER_FIELDS = [
  "name",
  "email",
  "phone",
  "securityQuestion",
  "securityAnswer",
  "password",
  "passwordConfirm",
] as const;

export default function RegisterPage() {
  useGuestRedirect();
  const router = useRouter();

  const [rateLimitError, setRateLimitError] = useState<string | null>(null);
  const [generalError, setGeneralError] = useState<string | null>(null);
  const [googleNotice, setGoogleNotice] = useState<string | null>(null);

  const {
    register,
    handleSubmit,
    control,
    setError,
    formState: { errors, isSubmitting },
  } = useForm<RegisterInput>({
    resolver: zodResolver(registerSchema),
    defaultValues: {
      name: "",
      email: "",
      phone: "",
      securityQuestion: undefined,
      securityAnswer: "",
      password: "",
      passwordConfirm: "",
    },
  });

  const watchedPassword = useWatch({ control, name: "password", defaultValue: "" });

  async function onSubmit(values: RegisterInput) {
    clearFlash();
    setGeneralError(null);
    setGoogleNotice(null);

    try {
      const res = await registerAccount(values);
      setRateLimitError(null);
      setFlash({ variant: "success", message: res.message });
      router.push("/verify-otp");
    } catch (e) {
      if (isApiError(e)) {
        if (e.code === "VALIDATION_ERROR") {
          applyApiFieldErrors(e.fields, setError, REGISTER_FIELDS);
          return;
        }
        if (e.code === "RATE_LIMITED") {
          setRateLimitError(e.message);
          return;
        }
        setGeneralError(e.message);
      } else {
        setGeneralError("Không thể tạo tài khoản lúc này.");
      }
    }
  }

  return (
    <AuthShell
      maxWidth="lg"
      subtitle="Tạo tài khoản miễn phí và bắt đầu mua bán ngay!"
      footer={
        <p className="text-sm text-muted">
          Đã có tài khoản?{" "}
          <Link
            href="/login"
            className="font-bold text-primary hover:text-primary-dark"
          >
            Đăng nhập
          </Link>
        </p>
      }
    >
      <FlashBanner />

      {rateLimitError && (
        <Alert variant="danger" className="mb-4">
          {rateLimitError}
        </Alert>
      )}

      {generalError && (
        <Alert variant="danger" className="mb-4">
          {generalError}
        </Alert>
      )}

      {googleNotice && (
        <Alert variant="info" className="mb-4">
          {googleNotice}
        </Alert>
      )}

      <form onSubmit={handleSubmit(onSubmit)} noValidate className="space-y-4">
        <Input
          id="name"
          type="text"
          label="Họ và tên"
          placeholder="Nguyễn Văn A"
          autoComplete="name"
          required
          disabled={isSubmitting}
          icon={<User className="h-4 w-4" />}
          error={errors.name?.message}
          {...register("name")}
        />

        <Input
          id="email"
          type="email"
          label="Email"
          placeholder="you@student.edu.vn"
          autoComplete="email"
          required
          disabled={isSubmitting}
          icon={<Mail className="h-4 w-4" />}
          error={errors.email?.message}
          {...register("email")}
        />

        <Input
          id="phone"
          type="tel"
          label="Số điện thoại"
          placeholder="0901234567"
          autoComplete="tel"
          optional
          disabled={isSubmitting}
          icon={<Phone className="h-4 w-4" />}
          error={errors.phone?.message}
          {...register("phone")}
        />

        <Select
          id="security_question"
          label="Câu hỏi bảo mật"
          placeholder="Chọn câu hỏi bảo mật"
          required
          disabled={isSubmitting}
          icon={<HelpCircle className="h-4 w-4" />}
          labelAside={
            <span className="text-xs text-muted">
              (dùng để khôi phục mật khẩu)
            </span>
          }
          options={SECURITY_QUESTIONS.map((q) => ({
            value: q.value,
            label: q.label,
          }))}
          error={errors.securityQuestion?.message}
          {...register("securityQuestion")}
        />

        <Input
          id="security_answer"
          type="text"
          label="Câu trả lời"
          placeholder="Nhập câu trả lời"
          required
          disabled={isSubmitting}
          icon={<PenLine className="h-4 w-4" />}
          error={errors.securityAnswer?.message}
          {...register("securityAnswer")}
        />

        <PasswordInput
          id="password"
          label="Mật khẩu"
          placeholder="Tối thiểu 8 ký tự (có chữ hoa và chữ số)"
          autoComplete="new-password"
          required
          disabled={isSubmitting}
          error={errors.password?.message}
          footer={<PasswordStrengthBar password={watchedPassword || ""} />}
          {...register("password")}
        />

        <PasswordInput
          id="password_confirm"
          label="Xác nhận mật khẩu"
          placeholder="Nhập lại mật khẩu"
          autoComplete="new-password"
          required
          disabled={isSubmitting}
          icon={<LockKeyhole className="h-4 w-4" />}
          error={errors.passwordConfirm?.message}
          {...register("passwordConfirm")}
        />

        <div className="pt-1">
          <Button
            type="submit"
            size="lg"
            fullWidth
            loading={isSubmitting}
            loadingText="Đang tạo tài khoản..."
          >
            <UserPlus className="h-4 w-4" aria-hidden="true" />
            Tạo tài khoản
          </Button>
        </div>
      </form>

      <div className="my-5 flex items-center gap-3 text-xs text-muted">
        <div className="h-px flex-1 bg-border" />
        <span>hoặc</span>
        <div className="h-px flex-1 bg-border" />
      </div>

      <GoogleAuthButton
        label="Đăng ký với Google"
        disabled={isSubmitting}
        onNotice={(msg) => setGoogleNotice(msg)}
      />
    </AuthShell>
  );
}
