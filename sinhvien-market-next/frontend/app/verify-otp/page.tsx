"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { ArrowLeft, CheckCircle2, Mail, RotateCw } from "lucide-react";
import { AuthShell } from "@/components/auth/AuthShell";
import { FlashBanner } from "@/components/auth/FlashBanner";
import { Alert } from "@/components/ui/Alert";
import { Button } from "@/components/ui/Button";
import { Input } from "@/components/ui/Input";
import { getOtpSession, resendOtp, verifyOtp } from "@/lib/api/auth";
import { ApiError } from "@/lib/api/types";
import { OTP_LENGTH, OTP_RESEND_MAX } from "@/lib/constants/auth";
import { setFlash } from "@/lib/flash";
import { applyApiFieldErrors } from "@/lib/forms";
import { addRecentAccount } from "@/lib/recent-accounts";
import { useGuestRedirect } from "@/lib/use-guest-redirect";
import { otpSchema, type OtpInput } from "@/lib/validation/auth";

export default function VerifyOtpPage() {
  useGuestRedirect();
  const router = useRouter();

  const [sessionEmail, setSessionEmail] = useState<string | null>(null);
  const [sessionRole, setSessionRole] = useState<"student" | "admin">("student");
  const [resendRemaining, setResendRemaining] =
    useState<number>(OTP_RESEND_MAX);
  const [loadingSession, setLoadingSession] = useState(true);
  const [resending, setResending] = useState(false);
  const [serverError, setServerError] = useState<string | null>(null);
  const [serverSuccess, setServerSuccess] = useState<string | null>(null);

  const {
    register,
    handleSubmit,
    setValue,
    setError,
    formState: { errors, isSubmitting },
  } = useForm<OtpInput>({
    resolver: zodResolver(otpSchema),
    defaultValues: { otp: "" },
  });

  useEffect(() => {
    let cancelled = false;
    getOtpSession()
      .then((session) => {
        if (cancelled) return;
        setSessionEmail(session.email);
        setSessionRole(session.role ?? "student");
        setResendRemaining(session.resendRemaining);
        setLoadingSession(false);
      })
      .catch((err) => {
        if (cancelled) return;
        if (err instanceof ApiError && err.code === "NO_VERIFY_SESSION") {
          router.replace("/login");
          return;
        }
        setServerError("Không thể tải phiên xác minh OTP.");
        setLoadingSession(false);
      });
    return () => {
      cancelled = true;
    };
  }, [router]);

  const onSubmit = async (values: OtpInput) => {
    setServerError(null);
    setServerSuccess(null);
    try {
      const res = await verifyOtp(values.otp);
      addRecentAccount(res.user.name, res.user.email);
      setFlash({ variant: "success", message: res.message });
      router.push(res.redirectTo);
    } catch (err) {
      if (err instanceof ApiError) {
        if (
          err.code === "OTP_SESSION_CANCELLED" ||
          err.code === "NO_VERIFY_SESSION"
        ) {
          setFlash({ variant: "danger", message: err.message });
          router.push("/login");
          return;
        }
        if (!applyApiFieldErrors(err.fields, setError, ["otp"])) {
          setServerError(err.message);
        }
      } else {
        setServerError("Đã có lỗi xảy ra. Vui lòng thử lại.");
      }
    }
  };

  const handleResend = async () => {
    if (resending || resendRemaining <= 0) return;
    setResending(true);
    setServerError(null);
    setServerSuccess(null);
    try {
      const res = await resendOtp();
      setResendRemaining(res.resendRemaining);
      setServerSuccess(res.message);
    } catch (err) {
      if (err instanceof ApiError) {
        if (err.code === "NO_VERIFY_SESSION") {
          router.push("/login");
          return;
        }
        if (err.code === "RATE_LIMITED") {
          setResendRemaining(0);
        }
        setServerError(err.message);
      } else {
        setServerError("Không thể gửi lại mã OTP. Vui lòng thử lại.");
      }
    } finally {
      setResending(false);
    }
  };

  const otpReg = register("otp");

  return (
    <AuthShell
      subtitle="Xác minh địa chỉ email của bạn"
      footer={
        <Link
          href={sessionRole === "admin" ? "/admin-login" : "/login"}
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

      {serverSuccess && (
        <Alert variant="success" className="mb-4">
          {serverSuccess}
        </Alert>
      )}

      <div className="mb-5 rounded-[14px] border border-primary/20 bg-primary-subtle/75 p-4 text-center">
        <div className="mb-1 flex justify-center text-primary">
          <Mail className="h-7 w-7" aria-hidden="true" />
        </div>
        <p className="mb-1 text-xs text-muted">
          Mã OTP 6 chữ số đã được gửi tới:
        </p>
        <p className="break-all text-sm font-bold text-primary">
          {loadingSession ? "Đang tải..." : (sessionEmail ?? "—")}
        </p>
        <p className="mt-1 text-xs text-muted">
          Mã có hiệu lực trong <strong>5 phút</strong>
        </p>
      </div>

      <form onSubmit={handleSubmit(onSubmit)} noValidate className="space-y-4">
        <Input
          id="otp"
          label="Nhập mã OTP"
          type="text"
          inputMode="numeric"
          autoComplete="one-time-code"
          maxLength={OTP_LENGTH}
          placeholder="------"
          autoFocus
          disabled={loadingSession}
          error={errors.otp?.message}
          className="text-center text-2xl font-bold tracking-[0.5em] placeholder:tracking-[0.4em]"
          {...otpReg}
          onChange={(e) => {
            const digitsOnly = e.target.value
              .replace(/[^0-9]/g, "")
              .slice(0, OTP_LENGTH);
            e.target.value = digitsOnly;
            setValue("otp", digitsOnly, {
              shouldValidate: Boolean(errors.otp),
            });
          }}
        />

        <Button
          type="submit"
          fullWidth
          disabled={loadingSession}
          loading={isSubmitting}
          loadingText="Đang kiểm tra..."
        >
          <CheckCircle2 className="h-4 w-4" aria-hidden="true" />
          <span>Xác Minh Ngay</span>
        </Button>
      </form>

      <div className="mt-5 border-t border-border pt-4 text-center">
        <p className="mb-2 text-xs text-muted">Không nhận được mã?</p>
        {resendRemaining > 0 ? (
          <>
            <Button
              type="button"
              variant="secondary"
              loading={resending}
              loadingText="Đang gửi..."
              disabled={loadingSession}
              onClick={handleResend}
            >
              <RotateCw className="h-3.5 w-3.5" aria-hidden="true" />
              <span>Gửi lại mã OTP</span>
            </Button>
            <p className="mt-1.5 text-xs text-muted">
              Còn {resendRemaining}/{OTP_RESEND_MAX} lượt gửi lại
            </p>
          </>
        ) : (
          <>
            <Button type="button" variant="secondary" disabled>
              <RotateCw className="h-3.5 w-3.5" aria-hidden="true" />
              <span>Gửi lại mã OTP</span>
            </Button>
            <p className="mt-1.5 text-xs font-medium text-danger">
              Đã dùng hết lượt. Đợi 10 phút để gửi lại.
            </p>
          </>
        )}
      </div>
    </AuthShell>
  );
}
