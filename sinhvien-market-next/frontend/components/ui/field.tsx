import type { ReactNode } from "react";
import { cn } from "@/lib/cn";

export interface FieldShellProps {
  id: string;
  label: string;
  required?: boolean;
  optional?: boolean;
  error?: string;
  hint?: string;
  labelAside?: ReactNode;
  children: ReactNode;
}

export function errorId(id: string) {
  return `${id}-error`;
}
export function hintId(id: string) {
  return `${id}-hint`;
}

export function FieldShell({
  id,
  label,
  required,
  optional,
  error,
  hint,
  labelAside,
  children,
}: FieldShellProps) {
  return (
    <div>
      <div className="mb-1.5 flex items-center justify-between gap-2">
        <label htmlFor={id} className="text-sm font-semibold text-fg">
          {label}
          {required && (
            <span className="ml-0.5 text-danger" aria-hidden="true">
              *
            </span>
          )}
          {optional && (
            <span className="ml-1 text-xs font-normal text-muted">
              (tuỳ chọn)
            </span>
          )}
        </label>
        {labelAside}
      </div>
      {children}
      {hint && !error && (
        <p id={hintId(id)} className="mt-1.5 text-xs text-muted">
          {hint}
        </p>
      )}
      {error && (
        <p
          id={errorId(id)}
          role="alert"
          className="mt-1.5 text-xs font-medium text-danger"
        >
          {error}
        </p>
      )}
    </div>
  );
}

export function describedBy(id: string, error?: string, hint?: string) {
  if (error) return errorId(id);
  if (hint) return hintId(id);
  return undefined;
}

export const fieldBase =
  "w-full rounded-field border-2 bg-surface px-3.5 py-2.5 text-sm text-fg " +
  "placeholder:text-muted/70 transition-all duration-200 ease-smooth " +
  "focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/15 " +
  "disabled:cursor-not-allowed disabled:opacity-60";

export function fieldState(error?: string) {
  return cn(
    error
      ? "border-danger focus:border-danger focus:ring-danger/15"
      : "border-border",
  );
}
