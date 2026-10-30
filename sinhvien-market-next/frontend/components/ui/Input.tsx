import type { ComponentProps, ReactNode } from "react";
import { cn } from "@/lib/cn";
import {
  FieldShell,
  describedBy,
  fieldBase,
  fieldState,
} from "./field";

export interface InputProps extends Omit<ComponentProps<"input">, "id"> {
  id: string;
  label: string;
  error?: string;
  hint?: string;
  optional?: boolean;
  labelAside?: ReactNode;
  /** Icon đặt bên trái ô nhập (aria-hidden). */
  icon?: ReactNode;
}

export function Input({
  id,
  label,
  error,
  hint,
  optional,
  labelAside,
  icon,
  required,
  className,
  ...rest
}: InputProps) {
  return (
    <FieldShell
      id={id}
      label={label}
      required={required}
      optional={optional}
      error={error}
      hint={hint}
      labelAside={labelAside}
    >
      <div className="relative">
        {icon && (
          <span
            aria-hidden="true"
            className="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-muted"
          >
            {icon}
          </span>
        )}
        <input
          id={id}
          required={required}
          aria-invalid={error ? true : undefined}
          aria-describedby={describedBy(id, error, hint)}
          className={cn(
            fieldBase,
            fieldState(error),
            icon ? "pl-10" : undefined,
            className,
          )}
          {...rest}
        />
      </div>
    </FieldShell>
  );
}
