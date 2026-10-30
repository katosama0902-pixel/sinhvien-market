import type { ReactNode } from "react";
import { Logo } from "@/components/ui/Logo";
import { ThemeToggle } from "@/components/ui/ThemeToggle";
import { cn } from "@/lib/cn";

export interface AuthShellProps {
  subtitle?: string;
  heading?: ReactNode;
  headerIcon?: ReactNode;
  maxWidth?: "md" | "lg";
  children: ReactNode;
  footer?: ReactNode;
}

export function AuthShell({
  subtitle,
  heading,
  headerIcon,
  maxWidth = "md",
  children,
  footer,
}: AuthShellProps) {
  return (
    <div
      className="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-8 sm:px-6"
      style={{
        background:
          "linear-gradient(135deg, #1e1b4b 0%, #312e81 45%, #4f46e5 100%)",
      }}
    >
      <div
        aria-hidden="true"
        className="pointer-events-none absolute -top-48 -right-48 h-[680px] w-[680px] rounded-full animate-float-orb"
        style={{
          background:
            "radial-gradient(circle, rgba(139,92,246,0.35) 0%, transparent 62%)",
        }}
      />
      <div
        aria-hidden="true"
        className="pointer-events-none absolute -bottom-36 -left-36 h-[500px] w-[500px] rounded-full animate-float-orb"
        style={{
          background:
            "radial-gradient(circle, rgba(236,72,153,0.28) 0%, transparent 62%)",
          animationDirection: "alternate-reverse",
          animationDuration: "10s",
        }}
      />

      <div className="fixed top-4 right-4 z-20">
        <ThemeToggle className="border-white/25 bg-white/15 text-white backdrop-blur-md hover:border-white/50 hover:bg-white/25 hover:text-white" />
      </div>

      <main
        className={cn(
          "relative z-10 w-full rounded-xl border border-white/60 bg-surface/95 p-6 shadow-[0_30px_80px_rgba(0,0,0,0.28)] backdrop-blur-xl animate-slide-up sm:p-10 dark:border-slate-700/70 dark:bg-surface/92",
          maxWidth === "lg" ? "max-w-[520px]" : "max-w-[480px]",
        )}
      >
        <div className="mb-6 text-center">
          {headerIcon ? (
            <div className="mb-3 flex justify-center">
              <div
                aria-hidden="true"
                className="flex h-12 w-12 items-center justify-center rounded-lg text-white shadow-md-p"
                style={{
                  background: "linear-gradient(135deg, #6366f1, #8b5cf6)",
                }}
              >
                {headerIcon}
              </div>
            </div>
          ) : (
            <div className="mb-2 flex justify-center">
              <Logo href="/login-role" />
            </div>
          )}

          {heading && (
            <h1 className="text-2xl font-black tracking-tight text-fg">
              {heading}
            </h1>
          )}

          {subtitle && (
            <p className="mt-1 text-sm font-medium text-muted">{subtitle}</p>
          )}
        </div>

        {children}

        {footer && <div className="mt-6 text-center">{footer}</div>}
      </main>
    </div>
  );
}
