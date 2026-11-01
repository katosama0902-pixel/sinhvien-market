"use client";

import type { ReactNode } from "react";
import { Suspense } from "react";
import Link from "next/link";
import { LockKeyhole } from "lucide-react";
import { MainShell, useMainShell } from "@/components/layout/MainShell";

export function UserFlowApp({ children }: { children: ReactNode }) {
  return (
    <Suspense
      fallback={
        <div className="mx-auto max-w-6xl px-4 py-16 text-center text-sm text-muted">
          Đang tải...
        </div>
      }
    >
      <MainShell>{children}</MainShell>
    </Suspense>
  );
}

export function FlowFrame({
  title,
  description,
  children,
}: {
  title: string;
  description: string;
  children: ReactNode;
}) {
  const { user, loadingUser } = useMainShell();

  return (
    <div className="mx-auto max-w-6xl px-4 py-8 sm:py-10">
      {loadingUser ? (
        <div className="py-16 text-center text-sm text-muted">
          Đang kiểm tra phiên đăng nhập...
        </div>
      ) : !user ? (
        <div className="mx-auto max-w-xl rounded-2xl border border-border bg-surface px-6 py-12 text-center">
          <LockKeyhole className="mx-auto mb-4 h-10 w-10 text-primary" />
          <h1 className="text-2xl font-black text-fg">
            Đăng nhập để tiếp tục
          </h1>
          <p className="mt-2 text-sm text-muted">
            Tính năng này dành cho tài khoản sinh viên.
          </p>
          <Link
            href="/login-role"
            className="mt-6 inline-flex rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white"
          >
            Đến cổng đăng nhập
          </Link>
        </div>
      ) : (
        <>
          <header className="mb-7">
            <h1 className="text-2xl font-black text-fg sm:text-3xl">{title}</h1>
            <p className="mt-1 text-sm text-muted">{description}</p>
          </header>
          {children}
        </>
      )}
    </div>
  );
}

export function UserFlowLayout({
  title,
  description,
  children,
}: {
  title: string;
  description: string;
  children: ReactNode;
}) {
  return (
    <FlowFrame title={title} description={description}>
      {children}
    </FlowFrame>
  );
}

export function FlowAlert({
  error,
  success,
}: {
  error?: string;
  success?: string;
}) {
  const message = error || success;
  if (!message) return null;
  return (
    <div
      role={error ? "alert" : "status"}
      className={`mb-5 rounded-xl border px-4 py-3 text-sm font-semibold ${
        error
          ? "border-rose-300 bg-rose-50 text-rose-700 dark:border-rose-900 dark:bg-rose-950/30 dark:text-rose-300"
          : "border-emerald-300 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-300"
      }`}
    >
      {message}
    </div>
  );
}
