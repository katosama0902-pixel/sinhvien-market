"use client";

import { useState } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import {
  ArrowLeft,
  HelpCircle,
  KeyRound,
  Mail,
  Send,
  ShieldCheck,
} from "lucide-react";
import { AuthShell } from "@/components/auth/AuthShell";
import { FlashBanner } from "@/components/auth/FlashBanner";
import { Alert } from "@/components/ui/Alert";
import { Button } from "@/components/ui/Button";
import { Input } from "@/components/ui/Input";
import {
  answerSecurityQuestion,
  getSecurityQuestion,
} from "@/lib/api/auth";
import { isApiError } from "@/lib/api/types";
import { clearFlash, setFlash } from "@/lib/flash";
import { applyApiFieldErrors } from "@/lib/forms";
import { useGuestRedirect } from "@/lib/use-guest-redirect";
import {
  forgotAnswerSchema,
  forgotEmailSchema,
  type ForgotAnswerInput,
  type ForgotEmailInput,
} from "@/lib/validation/auth";

export default function ForgotPasswordPage() {
  useGuestRedirect();
  const router = useRouter();

  const [step2Data, setStep2Data] = useState<{
    email: string;
    question: string;
  } | null>(null);
  const [generalError, setGeneralError] = useState<string | null>(null);

  const emailForm = useForm<ForgotEmailInput>({
    resolver: zodResolver(forgotEmailSchema),
    defaultValues: { email: "" },
  });

  const answerForm = useForm<ForgotAnswerInput>({
    resolver: zodResolver(forgotAnswerSchema),
    defaultValues: { email: "", securityAnswer: "" },
  });

  async function onSubmitEmail(values: ForgotEmailInput) {
    clearFlash();
    setGeneralError(null);

    try {
      const res = await getSecurityQuestion(values.email);
      setStep2Data({ email: res.email, question: res.question });
      answerForm.reset({ email: res.email, securityAnswer: "" });
    } catch (e) {
      if (isApiError(e)) {
        if (e.code === "VALIDATION_ERROR") {
          applyApiFieldErrors(e.fields, emailForm.setError, ["email"]);
          return;
        }
        setGeneralError(e.message);
      } else {
        setGeneralError("Đã có lỗi xảy ra. Vui lòng thử lại.");
      }
    }
  }

  async function onSubmitAnswer(values: ForgotAnswerInput) {
    clearFlash();
    setGeneralError(null);

    try {
      const res = await answerSecurityQuestion({
        email: values.email,
        securityAnswer: values.securityAnswer,
      });
      setFlash({
        variant: "success",
        message: res.message,
        action: res.devResetLink
          ? {
              label: "Mở liên kết đặt lại mật khẩu (chế độ Mock)",
              href: res.devResetLink,
            }
          : undefined,
      });
      router.push("/login");
    } catch (e) {
      if (isApiError(e)) {
        if (e.code === "VALIDATION_ERROR") {
          applyApiFieldErrors(e.fields, answerForm.setError, [
            "email",
            "securityAnswer",
          ]);
          return;
        }
        setGeneralError(e.message);
      } else {
        setGeneralError("Đã có lỗi xảy ra. Vui lòng thử lại.");
      }
    }
  }

  function handleChangeEmail() {
    setStep2Data(null);
    setGeneralError(null);
  }

  return (
    <AuthShell
      headerIcon={<KeyRound className="h-6 w-6" />}
      heading={
        <>
          Quên{" "}
          <span className="bg-linear-to-r from-primary to-accent bg-clip-text text-transparent">
            Mật Khẩu
          </span>
        </>
      }
      subtitle={
        step2Data
          ? "Trả lời câu hỏi bảo mật để tiếp tục"
          : "Nhập email của bạn để bắt đầu khôi phục"
      }
      footer={
        <Link
          href="/login"
          className="inline-flex items-center gap-1 text-sm font-medium text-muted transition-colors hover:text-primary"
        >
          <ArrowLeft className="h-4 w-4" aria-hidden="true" />
          Quay lại Đăng nhập
        </Link>
      }
    >
      <FlashBanner />

      {generalError && (
        <Alert variant="danger" className="mb-4">
          {generalError}
        </Alert>
      )}

      {!step2Data ? (
        <form
          key="step-1"
          onSubmit={emailForm.handleSubmit(onSubmitEmail)}
          noValidate
          className="space-y-4"
        >
          <Input
            id="email"
            type="email"
            label="Email tài khoản"
            placeholder="you@student.edu.vn"
            autoComplete="email"
            autoFocus
            required
            disabled={emailForm.formState.isSubmitting}
            icon={<Mail className="h-4 w-4" />}
            error={emailForm.formState.errors.email?.message}
            {...emailForm.register("email")}
          />

          <Button
            type="submit"
            size="lg"
            fullWidth
            loading={emailForm.formState.isSubmitting}
            loadingText="Đang xử lý..."
          >
            <Send className="h-4 w-4" aria-hidden="true" />
            Xác Nhận Email
          </Button>
        </form>
      ) : (
        <form
          key="step-2"
          onSubmit={answerForm.handleSubmit(onSubmitAnswer)}
          noValidate
          className="space-y-4"
        >
          <div className="flex items-center justify-between gap-2 rounded-field bg-surface-2 px-3.5 py-2.5 text-xs">
            <span className="truncate text-muted">
              Email: <strong className="text-fg">{step2Data.email}</strong>
            </span>
            <button
              type="button"
              onClick={handleChangeEmail}
              className="shrink-0 cursor-pointer font-bold text-primary hover:text-primary-dark"
            >
              Đổi email
            </button>
          </div>

          <Input
            id="security_answer"
            type="text"
            label={step2Data.question}
            placeholder="Nhập câu trả lời của bạn"
            autoFocus
            required
            disabled={answerForm.formState.isSubmitting}
            icon={<HelpCircle className="h-4 w-4 text-primary" />}
            error={answerForm.formState.errors.securityAnswer?.message}
            {...answerForm.register("securityAnswer")}
          />

          <Button
            type="submit"
            size="lg"
            fullWidth
            loading={answerForm.formState.isSubmitting}
            loadingText="Đang xử lý..."
          >
            <ShieldCheck className="h-4 w-4" aria-hidden="true" />
            Khôi Phục Mật Khẩu
          </Button>
        </form>
      )}
    </AuthShell>
  );
}
