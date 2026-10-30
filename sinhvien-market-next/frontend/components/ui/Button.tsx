import type { ComponentProps } from "react";
import { cn } from "@/lib/cn";
import { Spinner } from "./Spinner";

type Variant = "primary" | "secondary" | "ghost" | "danger";
type Size = "md" | "lg";

const variants: Record<Variant, string> = {
  primary:
    "bg-linear-to-r from-primary via-secondary to-primary bg-[length:200%_100%] bg-left hover:bg-right text-white shadow-md-p hover:shadow-lg-p",
  secondary:
    "bg-surface text-fg border-2 border-border hover:border-primary hover:text-primary",
  ghost: "bg-transparent text-primary hover:bg-primary/10",
  danger: "bg-danger text-white hover:bg-danger/90",
};

const sizes: Record<Size, string> = {
  md: "px-5 py-2.5 text-sm",
  lg: "px-6 py-3 text-base",
};

export interface ButtonProps extends ComponentProps<"button"> {
  variant?: Variant;
  size?: Size;
  /** Đang gửi: vô hiệu hoá nút và hiện spinner. */
  loading?: boolean;
  /** Chữ hiển thị khi loading (mặc định giữ nguyên children). */
  loadingText?: string;
  fullWidth?: boolean;
}

export function Button({
  variant = "primary",
  size = "md",
  loading = false,
  loadingText,
  fullWidth = false,
  disabled,
  className,
  children,
  type = "button",
  ...rest
}: ButtonProps) {
  return (
    <button
      type={type}
      disabled={disabled || loading}
      aria-busy={loading || undefined}
      className={cn(
        "inline-flex items-center justify-center gap-2 rounded-field font-bold cursor-pointer",
        "transition-all duration-200 ease-smooth",
        "active:scale-[0.98] disabled:active:scale-100",
        loading
          ? "cursor-wait opacity-90"
          : "disabled:cursor-not-allowed disabled:opacity-55",
        variants[variant],
        sizes[size],
        fullWidth && "w-full",
        className,
      )}
      {...rest}
    >
      {loading && <Spinner size="sm" />}
      {loading && loadingText ? loadingText : children}
    </button>
  );
}
