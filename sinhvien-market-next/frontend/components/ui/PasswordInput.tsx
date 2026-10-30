"use client";

import { useState, type ComponentProps, type ReactNode } from "react";
import { Eye, EyeOff, Lock } from "lucide-react";
import { cn } from "@/lib/cn";
import {
  FieldShell,
  describedBy,
  fieldBase,
  fieldState,
} from "./field";

export interface PasswordInputProps
  extends Omit<ComponentProps<"input">, "id" | "type"> {
  id: string;
  label: string;
  error?: string;
  hint?: string;
  labelAside?: ReactNode;
  /** Icon bên trái (mặc định là ổ khoá). Truyền `null` để ẩn. */
  icon?: ReactNode;
  /** Nội dung đặt dưới ô nhập (ví dụ thanh độ mạnh mật khẩu). */
  footer?: ReactNode;
}

export function PasswordInput({
  id,
  label,
  error,
  hint,
  labelAside,
  icon,
  footer,
  required,
  className,
  ...rest
}: PasswordInputProps) {
  const [visible, setVisible] = useState(false);
  const leftIcon =
    icon === undefined ? <Lock className="h-4 w-4" /> : icon;

  return (
    <div>
      <FieldShell
        id={id}
        label={label}
        required={required}
        error={error}
        hint={hint}
        labelAside={labelAside}
      >
        <div className="relative">
          {leftIcon && (
            <span
              aria-hidden="true"
              className="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-muted"
            >
              {leftIcon}
            </span>
          )}
          <input
            id={id}
            type={visible ? "text" : "password"}
            required={required}
            aria-invalid={error ? true : undefined}
            aria-describedby={describedBy(id, error, hint)}
            className={cn(
              fieldBase,
              fieldState(error),
              leftIcon ? "pl-10" : undefined,
              "pr-12",
              className,
            )}
            {...rest}
          />
          <button
            type="button"
            onClick={() => setVisible((v) => !v)}
            aria-label={visible ? "Ẩn mật khẩu" : "Hiện mật khẩu"}
            aria-pressed={visible}
            className="absolute inset-y-0 right-1.5 my-1.5 flex w-9 cursor-pointer items-center justify-center rounded-sm text-muted transition-colors hover:text-primary"
          >
            {visible ? (
              <EyeOff className="h-4 w-4" aria-hidden="true" />
            ) : (
              <Eye className="h-4 w-4" aria-hidden="true" />
            )}
          </button>
        </div>
      </FieldShell>
      {footer}
    </div>
  );
}
