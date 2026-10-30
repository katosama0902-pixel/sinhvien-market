"use client";

import { useState } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import {
  ArrowLeft,
  KeyRound,
  Lock,
  Mail,
  ShieldAlert,
  ShieldCheck,
} from "lucide-react";
import { FlashBanner } from "@/components/auth/FlashBanner";
import { Alert } from "@/components/ui/Alert";
import { Button } from "@/components/ui/Button";
import { Input } from "@/components/ui/Input";
import { PasswordInput } from "@/components/ui/PasswordInput";
import { login } from "@/lib/api/auth";
import { isApiError } from "@/lib/api/types";
import { clearFlash, setFlash } from "@/lib/flash";
import { applyApiFieldErrors } from "@/lib/forms";
import { useGuestRedirect } from "@/lib/use-guest-redirect";
import { loginSchema, type LoginInput } from "@/lib/validation/auth";

export default function AdminLoginPage() {
  useGuestRedirect();
  const router = useRouter();

  const [rateLimitError, setRateLimitError] = useState<string | null>(null);
  const [generalError, setGeneralError] = useState<string | null>(null);

  const {
    register,
    handleSubmit,
    setError,
    formState: { errors, isSubmitting },
  } = useForm<LoginInput>({
    resolver: zodResolver(loginSchema),
    defaultValues: { email: "", password: "" },
  });

  async function onSubmit(values: LoginInput) {
    clearFlash();
    setGeneralError(null);

    try {
      const res = await login({
        role: "admin",
        email: values.email,
        password: values.password,
      });

      setRateLimitError(null);

      if (res.status === "ok") {
        setFlash({ variant: "success", message: res.message });
        router.push(res.redirectTo);
        return;
      }

      if (res.status === "verify_otp") {
        setFlash({ variant: "info", message: res.message });
        router.push("/verify-otp");
        return;
      }

      if (res.status === "locked") {
        router.push("/account-locked");
      }
    } catch (e) {
      if (isApiError(e)) {
        if (e.code === "VALIDATION_ERROR") {
          applyApiFieldErrors(e.fields, setError, ["email", "password"]);
          return;
        }
        if (e.code === "RATE_LIMITED") {
          setRateLimitError(e.message);
          return;
        }
        setGeneralError(e.message);
      } else {
        setGeneralError("Đã có lỗi xảy ra. Vui lòng thử lại.");
      }
    }
  }

  return (
    <div
      className="dark relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-8 text-slate-100"
      style={{
        background:
          "radial-gradient(ellipse at 60% 0%, rgba(236,72,153,0.18) 0%, transparent 50%), radial-gradient(ellipse at 10% 100%, rgba(99,102,241,0.22) 0%, transparent 50%), linear-gradient(180deg, #0f172a 0%, #020617 100%)",
      }}
    >
      <div
        aria-hidden="true"
        className="pointer-events-none absolute inset-0"
        style={{
          backgroundImage:
            "repeating-linear-gradient(0deg, transparent, transparent 40px, rgba(255,255,255,0.025) 40px, rgba(255,255,255,0.025) 41px), repeating-linear-gradient(90deg, transparent, transparent 40px, rgba(255,255,255,0.025) 40px, rgba(255,255,255,0.025) 41px)",
        }}
      />

      <main
        className="relative z-10 w-full max-w-md rounded-xl border border-white/10 p-6 shadow-[0_40px_100px_rgba(0,0,0,0.65)] backdrop-blur-2xl animate-slide-up sm:p-8"
        style={{ background: "rgba(30, 41, 59, 0.86)" }}
      >
        <div className="mb-6 text-center">
          <div className="mb-1 flex items-center justify-center gap-3">
            <div
              aria-hidden="true"
              className="flex h-12 w-12 shrink-0 items-center justify-center rounded-field text-white"
              style={{
                background: "linear-gradient(135deg, #ec4899, #f97316)",
                boxShadow: "0 8px 24px rgba(236, 72, 153, 0.5)",
              }}
            >
              <ShieldAlert className="h-6 w-6" />
            </div>
            <div className="text-left">
              <h1 className="text-xl font-black tracking-tight text-white">
                Admin<span className="text-pink-400">Panel</span>
              </h1>
              <p className="text-xs font-medium text-white/45">
                SinhVienMarket
              </p>
            </div>
          </div>

          <span
            className="mt-3 inline-flex items-center gap-1.5 rounded-full px-4 py-1.5 text-xs font-bold"
            style={{
              background: "rgba(236, 72, 153, 0.12)",
              border: "1px solid rgba(236, 72, 153, 0.28)",
              color: "#f9a8d4",
            }}
          >
            <Lock className="h-3.5 w-3.5" aria-hidden="true" />
            Yêu cầu xác thực
          </span>
        </div>

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

        <form onSubmit={handleSubmit(onSubmit)} noValidate className="space-y-4">
          <Input
            id="email"
            type="email"
            label="Email quản trị"
            placeholder="admin@sinhvienmarket.vn"
            autoComplete="email"
            required
            disabled={isSubmitting}
            icon={<Mail className="h-4 w-4" />}
            className="border-white/15 bg-white/6 text-white placeholder:text-white/35 focus:border-pink-400 focus:ring-pink-500/20"
            error={errors.email?.message}
            {...register("email")}
          />

          <PasswordInput
            id="password"
            label="Mật khẩu"
            placeholder="••••••••"
            autoComplete="current-password"
            required
            disabled={isSubmitting}
            icon={<KeyRound className="h-4 w-4" />}
            className="border-white/15 bg-white/6 text-white placeholder:text-white/35 focus:border-pink-400 focus:ring-pink-500/20"
            error={errors.password?.message}
            {...register("password")}
          />

          <div className="pt-2">
            <Button
              type="submit"
              size="lg"
              fullWidth
              loading={isSubmitting}
              loadingText="Đang xác thực..."
              className="border-0 text-white shadow-[0_6px_24px_rgba(236,72,153,0.4)] hover:shadow-[0_10px_30px_rgba(236,72,153,0.55)]"
              style={{
                background: "linear-gradient(135deg, #ec4899, #f97316)",
              }}
            >
              <ShieldCheck className="h-4 w-4" aria-hidden="true" />
              Xác thực &amp; Đăng nhập
            </Button>
          </div>
        </form>

        <div
          className="mt-5 flex items-center gap-2.5 rounded-field px-3.5 py-3 text-xs font-medium"
          style={{
            background: "rgba(251, 191, 36, 0.08)",
            border: "1px solid rgba(251, 191, 36, 0.22)",
            color: "rgba(253, 224, 71, 0.88)",
          }}
        >
          <Lock className="h-4 w-4 shrink-0" aria-hidden="true" />
          <span>
            Hệ thống bảo mật theo dõi mọi lần đăng nhập. Tối đa 5 lần sai / 15
            phút.
          </span>
        </div>

        <div className="mt-5 text-center">
          <Link
            href="/login-role"
            className="inline-flex items-center gap-1 text-xs font-medium text-white/45 transition-colors hover:text-white/80"
          >
            <ArrowLeft className="h-3.5 w-3.5" aria-hidden="true" />
            Quay lại cổng chính
          </Link>
        </div>
      </main>
    </div>
  );
}
