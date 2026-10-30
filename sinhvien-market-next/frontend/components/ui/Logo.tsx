import Link from "next/link";
import { Store } from "lucide-react";
import { cn } from "@/lib/cn";

export interface LogoProps {
  /** Đặt href="" để hiển thị tĩnh (không phải link). */
  href?: string;
  className?: string;
  /** Dùng chữ trắng cho nền tối (trang admin, chọn vai trò). */
  onDark?: boolean;
}

export function Logo({ href = "/login-role", className, onDark }: LogoProps) {
  const content = (
    <>
      <span
        aria-hidden="true"
        className="flex h-9 w-9 items-center justify-center rounded-field bg-linear-to-br from-primary to-secondary text-white shadow-sm-p"
      >
        <Store className="h-5 w-5" />
      </span>
      <span
        className={cn(
          "text-xl font-black tracking-tight",
          onDark
            ? "text-white"
            : "bg-linear-to-r from-primary to-accent bg-clip-text text-transparent",
        )}
      >
        SinhVienMarket
      </span>
    </>
  );

  const classes = cn("inline-flex items-center gap-2.5", className);

  if (!href) return <span className={classes}>{content}</span>;
  return (
    <Link href={href} className={classes} aria-label="SinhVienMarket">
      {content}
    </Link>
  );
}
