import { cn } from "@/lib/cn";

const sizes = { sm: "h-4 w-4", md: "h-6 w-6", lg: "h-10 w-10" } as const;

export interface SpinnerProps {
  size?: keyof typeof sizes;
  /** Nhãn đọc cho trình đọc màn hình. */
  label?: string;
  className?: string;
}

export function Spinner({
  size = "md",
  label = "Đang tải",
  className,
}: SpinnerProps) {
  return (
    <svg
      role="status"
      aria-label={label}
      className={cn("animate-spin", sizes[size], className)}
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle
        className="opacity-25"
        cx="12"
        cy="12"
        r="10"
        stroke="currentColor"
        strokeWidth="4"
      />
      <path
        className="opacity-75"
        fill="currentColor"
        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
      />
    </svg>
  );
}
