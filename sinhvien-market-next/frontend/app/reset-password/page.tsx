"use client";

import { Suspense, useEffect, useState } from "react";
import Link from "next/link";
import { useRouter, useSearchParams } from "next/navigation";
import { useForm, useWatch } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { ArrowLeft, CheckCircle2, ShieldCheck } from "lucide-react";
import { AuthShell } from "@/components/auth/AuthShell";
import { FlashBanner } from "@/components/auth/FlashBanner";
import { PasswordStrengthBar } from "@/components/auth/PasswordStrengthBar";
import { Alert } from "@/components/ui/Alert";
import { Button } from "@/components/ui/Button";
import { PasswordInput } from "@/components/ui/PasswordInput";
import { Spinner } from "@/components/ui/Spinner";
import { resetPassword } from "@/lib/api/auth";
import { ApiError } from "@/lib/api/types";
import { setFlash } from "@/lib/flash";
import { applyApiFieldErrors } from "@/lib/forms";
import { useGuestRedirect } from "@/lib/use-guest-redirect";
import {
  resetPasswordSchema,
  type ResetPasswordInput,
} from "@/lib/validation/auth";

function ResetPasswordContent() {
  useGuestRedirect();
  const router = useRouter();
  const searchParams = useSearchParams();

  const token = searchParams.get("token")?.trim() ?? "";
  const email = searchParams.get("email")?.trim() ?? "";

  const [serverError, setServerError] = useState<string | null>(null);

  const {
    register,
    handleSubmit,
    control,
    setValue,
    setError,
    formState: { errors, isSubmitting },
  } = useForm<ResetPasswordInput>({
    resolver: zodResolver(resetPasswordSchema),
    defaultValues: {
      email,
      token,
      password: "",
      passwordConfirm: "",
    },
  });

  const passwordValue = useWatch({ control, name: "password" }) ?? "";

  useEffect(() => {
    if (!token || !email) {
      setFlash({ variant: "danger", message: "Liên kết không hợp lệ." });
      router.replace("/login");
      return;
    }
    setValue("email", email);
    setValue("token", token);
  }, [token, email, router, setValue]);

  const onSubmit = async (values: ResetPasswordInput) => {
    setServerError(null);
    try {
      const res = await resetPassword(values);
      setFlash({ variant: "success", message: res.message });
      router.push("/login");
    } catch (err) {
      if (err instanceof ApiError) {
        if (err.code === "RESET_LINK_INVALID") {
          setFlash({ variant: "danger", message: err.message });
          router.push("/login");
          return;
        }
        if (
          !applyApiFieldErrors(err.fields, setError, [
            "password",
            "passwordConfirm",
          ])
        ) {
          setServerError(err.message);
        }
      } else {
        setServerError("Đã có lỗi xảy ra. Vui lòng thử lại.");
      }
    }
  };

  if (!token || !email) {
    return null;
  }

  return (
    <AuthShell
      headerIcon={<ShieldCheck className="h-6 w-6" />}
      heading="Đặt Lại Mật Khẩu"
      subtitle={`Tài khoản: ${email}`}
      footer={
        <Link
          href="/login"
          className="inline-flex items-center gap-1.5 text-sm font-medium text-muted transition-colors hover:text-primary"
        >
          <ArrowLeft className="h-4 w-4" aria-hidden="true" />
          <span>Quay lại đăng nhập</span>
        </Link>
      }
    >
      <FlashBanner />

      {serverError && (
        <Alert variant="danger" className="mb-4">
          {serverError}
        </Alert>
      )}

      <form onSubmit={handleSubmit(onSubmit)} noValidate className="space-y-4">
        <input type="hidden" {...register("email")} />
        <input type="hidden" {...register("token")} />

        <div>
          <PasswordInput
            id="password"
            label="Mật khẩu mới"
            placeholder="Ít nhất 8 ký tự, 1 chữ hoa, 1 số"
            autoComplete="new-password"
            error={errors.password?.message}
            {...register("password")}
          />
          <PasswordStrengthBar password={passwordValue} />
        </div>

        <PasswordInput
          id="passwordConfirm"
          label="Xác nhận mật khẩu mới"
          placeholder="Nhập lại mật khẩu mới"
          autoComplete="new-password"
          error={errors.passwordConfirm?.message}
          {...register("passwordConfirm")}
        />

        <Button
          type="submit"
          fullWidth
          loading={isSubmitting}
          loadingText="Đang cập nhật..."
        >
          <CheckCircle2 className="h-4 w-4" aria-hidden="true" />
          <span>Lưu Mật Khẩu Mới</span>
        </Button>
      </form>
    </AuthShell>
  );
}

export default function ResetPasswordPage() {
  return (
    <Suspense
      fallback={
        <AuthShell
          headerIcon={<ShieldCheck className="h-6 w-6" />}
          heading="Đặt Lại Mật Khẩu"
          subtitle="Đang kiểm tra liên kết..."
        >
          <div className="flex justify-center py-8 text-primary">
            <Spinner size="md" />
          </div>
        </AuthShell>
      }
    >
      <ResetPasswordContent />
    </Suspense>
  );
}
