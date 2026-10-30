export interface PasswordStrength {
  score: 0 | 1 | 2 | 3 | 4;
  label: string;
  color: string;
  percent: number;
}

const LABELS = ["", "Yếu", "Trung bình", "Khá mạnh", "Mạnh"] as const;
const COLORS = ["", "#ef4444", "#f59e0b", "#3b82f6", "#10b981"] as const;

export function getPasswordStrength(value: string): PasswordStrength {
  let score = 0;
  if (value.length >= 8) score++;
  if (/[A-Z]/.test(value)) score++;
  if (/[0-9]/.test(value)) score++;
  if (/[^A-Za-z0-9]/.test(value)) score++;
  const s = score as PasswordStrength["score"];
  return { score: s, label: LABELS[s], color: COLORS[s], percent: s * 25 };
}
