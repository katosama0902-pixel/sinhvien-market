import type { ComponentProps, ReactNode } from "react";
import { ChevronDown } from "lucide-react";
import { cn } from "@/lib/cn";
import {
  FieldShell,
  describedBy,
  fieldBase,
  fieldState,
} from "./field";

export interface SelectOption {
  value: string;
  label: string;
}

export interface SelectProps extends Omit<ComponentProps<"select">, "id"> {
  id: string;
  label: string;
  options: SelectOption[];
  /** Dòng đầu (giá trị rỗng), ví dụ "Chọn câu hỏi bảo mật". */
  placeholder?: string;
  error?: string;
  hint?: string;
  labelAside?: ReactNode;
  /** Icon đặt bên trái ô chọn (aria-hidden). */
  icon?: ReactNode;
}

export function Select({
  id,
  label,
  options,
  placeholder,
  error,
  hint,
  labelAside,
  icon,
  required,
  className,
  ...rest
}: SelectProps) {
  return (
    <FieldShell
      id={id}
      label={label}
      required={required}
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
        <select
          id={id}
          required={required}
          aria-invalid={error ? true : undefined}
          aria-describedby={describedBy(id, error, hint)}
          className={cn(
            fieldBase,
            fieldState(error),
            icon ? "pl-10" : undefined,
            "cursor-pointer appearance-none pr-10",
            className,
          )}
          {...rest}
        >
          {placeholder && <option value="">{placeholder}</option>}
          {options.map((o) => (
            <option key={o.value} value={o.value}>
              {o.label}
            </option>
          ))}
        </select>
        <ChevronDown
          aria-hidden="true"
          className="pointer-events-none absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted"
        />
      </div>
    </FieldShell>
  );
}
