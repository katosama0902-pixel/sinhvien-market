import { LOCK_DURATION_OPTIONS } from "@/lib/constants/admin";
import type { LockDuration } from "@/lib/types/admin";

export function makeCategorySlug(name: string): string {
  const normalized = name
    .trim()
    .toLowerCase()
    .replace(/đ/g, "d")
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "");

  return normalized
    .replace(/\s+/g, "-")
    .replace(/[^a-z0-9-]/g, "")
    .replace(/-+/g, "-")
    .replace(/^-|-$/g, "");
}

export function validateCategoryName(name: string): string | null {
  const trimmed = name.trim();
  if (trimmed.length < 2) {
    return "Tên danh mục phải có ít nhất 2 ký tự.";
  }
  if (trimmed.length > 80) {
    return "Tên danh mục tối đa 80 ký tự.";
  }
  return null;
}

export function validateDateRange(fromDate: string, toDate: string): string | null {
  if (fromDate && toDate && fromDate > toDate) {
    return 'Lỗi: "Từ ngày" không được lớn hơn "Đến ngày"!';
  }
  return null;
}

export function computeLockedUntil(
  duration: LockDuration,
  now: Date = new Date(),
): string | null {
  const option = LOCK_DURATION_OPTIONS.find((item) => item.value === duration);
  if (!option || option.days === null) {
    return null;
  }
  const target = new Date(now.getTime() + option.days * 86_400_000);
  return target.toISOString();
}

export function calculateStrikeOutcome(
  currentStrikes: number,
  reason: string,
  userName: string,
  now: Date = new Date(),
): {
  nextStrike: number;
  action: "warning" | "suspend_7_days" | "ban_permanent";
  isLocked: boolean;
  lockedUntil: string | null;
  lockReason: string | null;
  message: string;
} {
  const nextStrike = Math.max(0, currentStrikes) + 1;
  const cleanReason = reason.trim();

  if (nextStrike === 1) {
    return {
      nextStrike,
      action: "warning",
      isLocked: false,
      lockedUntil: null,
      lockReason: null,
      message: `Đã tặng Gậy 1 cho ${userName}. Đã gửi email cảnh cáo.`,
    };
  }

  if (nextStrike === 2) {
    const lockedUntil = new Date(now.getTime() + 7 * 86_400_000).toISOString();
    return {
      nextStrike,
      action: "suspend_7_days",
      isLocked: true,
      lockedUntil,
      lockReason: `Tạm khóa 7 ngày do nhận 2 gậy vi phạm. Lý do gần nhất: ${cleanReason}`,
      message: `Đã tặng Gậy 2 cho ${userName}. Tài khoản bị KHÓA 7 NGÀY.`,
    };
  }

  return {
    nextStrike,
    action: "ban_permanent",
    isLocked: true,
    lockedUntil: null,
    lockReason: `Khóa vĩnh viễn do nhận ${nextStrike} gậy vi phạm. Lý do gần nhất: ${cleanReason}`,
    message: `Đã tặng Gậy ${nextStrike} cho ${userName}. Tài khoản bị KHÓA VĨNH VIỄN.`,
  };
}

export function formatVnd(amount: number | null | undefined): string {
  if (amount === null || amount === undefined) return "—";
  return `${Math.round(amount).toLocaleString("vi-VN")}đ`;
}

export function formatDateVi(iso: string | null | undefined): string {
  if (!iso) return "—";
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return "—";
  const dd = String(d.getDate()).padStart(2, "0");
  const mm = String(d.getMonth() + 1).padStart(2, "0");
  const yyyy = d.getFullYear();
  return `${dd}/${mm}/${yyyy}`;
}

export function formatDateTimeVi(iso: string | null | undefined): string {
  if (!iso) return "—";
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return "—";
  const dd = String(d.getDate()).padStart(2, "0");
  const mm = String(d.getMonth() + 1).padStart(2, "0");
  const yyyy = d.getFullYear();
  const hh = String(d.getHours()).padStart(2, "0");
  const min = String(d.getMinutes()).padStart(2, "0");
  return `${dd}/${mm}/${yyyy} ${hh}:${min}`;
}

export function formatShortDateTimeVi(iso: string | null | undefined): string {
  if (!iso) return "—";
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return "—";
  const dd = String(d.getDate()).padStart(2, "0");
  const mm = String(d.getMonth() + 1).padStart(2, "0");
  const hh = String(d.getHours()).padStart(2, "0");
  const min = String(d.getMinutes()).padStart(2, "0");
  return `${dd}/${mm} ${hh}:${min}`;
}

export function isWithinDateRange(
  iso: string,
  fromDate?: string,
  toDate?: string,
): boolean {
  const day = iso.slice(0, 10);
  if (fromDate && day < fromDate) return false;
  if (toDate && day > toDate) return false;
  return true;
}

function escapeCsvCell(val: string | number | null | undefined): string {
  const str = val === null || val === undefined ? "" : String(val);
  if (/[",\r\n]/.test(str)) {
    return `"${str.replace(/"/g, '""')}"`;
  }
  return str;
}

export function buildCsvContent(options: {
  headers: string[];
  rows: (string | number | null | undefined)[][];
  summaryRows?: (string | number | null | undefined)[][];
  fromDate?: string;
  toDate?: string;
}): string {
  const lines: string[] = [];

  if (options.fromDate || options.toDate) {
    lines.push(
      [
        "Báo cáo từ ngày:",
        options.fromDate || "Không giới hạn",
        "Đến ngày:",
        options.toDate || "Không giới hạn",
      ]
        .map(escapeCsvCell)
        .join(","),
    );
    lines.push("");
  }

  lines.push(options.headers.map(escapeCsvCell).join(","));

  for (const row of options.rows) {
    lines.push(row.map(escapeCsvCell).join(","));
  }

  if (options.summaryRows && options.summaryRows.length > 0) {
    lines.push("");
    lines.push(["--- THỐNG KÊ TỔNG HỢP ---"].map(escapeCsvCell).join(","));
    for (const sRow of options.summaryRows) {
      lines.push(sRow.map(escapeCsvCell).join(","));
    }
  }

  return `\uFEFF${lines.join("\r\n")}`;
}
