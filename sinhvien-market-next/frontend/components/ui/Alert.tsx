import type { ReactNode } from "react";
import {
  AlertCircle,
  AlertTriangle,
  CheckCircle2,
  Info,
  type LucideIcon,
} from "lucide-react";
import { cn } from "@/lib/cn";

export type AlertVariant = "danger" | "success" | "info" | "warning";

const styles: Record<
  AlertVariant,
  { box: string; icon: LucideIcon; role: "alert" | "status" }
> = {
  danger: {
    box: "border-danger/30 bg-danger/10 text-danger",
    icon: AlertCircle,
    role: "alert",
  },
  warning: {
    box: "border-warning/40 bg-warning/10 text-warning",
    icon: AlertTriangle,
    role: "alert",
  },
  success: {
    box: "border-success/30 bg-success/10 text-success",
    icon: CheckCircle2,
    role: "status",
  },
  info: {
    box: "border-info/30 bg-info/10 text-info",
    icon: Info,
    role: "status",
  },
};

export interface AlertProps {
  variant?: AlertVariant;
  title?: string;
  children: ReactNode;
  className?: string;
}

export function Alert({
  variant = "info",
  title,
  children,
  className,
}: AlertProps) {
  const { box, icon: Icon, role } = styles[variant];
  return (
    <div
      role={role}
      className={cn(
        "flex items-start gap-3 rounded-field border px-4 py-3 text-sm",
        box,
        className,
      )}
    >
      <Icon className="mt-0.5 h-4 w-4 shrink-0" aria-hidden="true" />
      <div className="min-w-0">
        {title && <p className="font-bold">{title}</p>}
        <div className="font-medium">{children}</div>
      </div>
    </div>
  );
}
