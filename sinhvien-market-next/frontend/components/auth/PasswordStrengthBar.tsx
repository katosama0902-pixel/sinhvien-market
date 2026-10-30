import { getPasswordStrength } from "@/lib/validation/password-strength";

export interface PasswordStrengthBarProps {
  password: string;
}

/**
 * Thanh đo độ mạnh mật khẩu (0–4 điểm: >=8 ký tự, chữ hoa, chữ số, ký tự đặc biệt).
 * Giữ đúng màu và nhãn từ bản PHP (`register.php`).
 */
export function PasswordStrengthBar({ password }: PasswordStrengthBarProps) {
  const { score, label, color, percent } = getPasswordStrength(password);

  return (
    <div className="mt-2" aria-live="polite">
      <div className="h-1.5 overflow-hidden rounded-full bg-surface-2">
        <div
          className="h-full rounded-full transition-all duration-300"
          style={{
            width: `${percent}%`,
            backgroundColor: score > 0 ? color : "transparent",
          }}
        />
      </div>
      {score > 0 && (
        <p
          className="mt-1 text-xs font-semibold"
          style={{ color }}
        >
          Độ mạnh: {label}
          {score === 4 ? " 💪" : ""}
        </p>
      )}
    </div>
  );
}
