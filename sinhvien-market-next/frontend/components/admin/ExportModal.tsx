"use client";

import { useState } from "react";
import { Download, FileSpreadsheet, Info } from "lucide-react";
import { exportAdminCsv } from "@/lib/api/admin";
import type { ExportType } from "@/lib/types/admin";
import { validateDateRange } from "@/lib/validation/admin";

interface ExportModalProps {
  open: boolean;
  type: ExportType;
  initialFromDate?: string;
  initialToDate?: string;
  onClose: () => void;
  onSuccess?: (message: string) => void;
}

export function ExportModal({
  open,
  type,
  initialFromDate = "",
  initialToDate = "",
  onClose,
  onSuccess,
}: ExportModalProps) {
  const [fromDate, setFromDate] = useState(initialFromDate);
  const [toDate, setToDate] = useState(initialToDate);
  const [error, setError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);

  if (!open) return null;

  const typeLabel =
    type === "users"
      ? "người dùng"
      : type === "products"
        ? "bài đăng"
        : "giao dịch";

  async function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const rangeErr = validateDateRange(fromDate, toDate);
    if (rangeErr) {
      setError(rangeErr);
      return;
    }
    setError(null);
    setSubmitting(true);
    try {
      const { filename, content } = await exportAdminCsv({
        type,
        fromDate: fromDate || undefined,
        toDate: toDate || undefined,
      });

      const blob = new Blob([content], { type: "text/csv;charset=utf-8;" });
      const url = URL.createObjectURL(blob);
      const link = document.createElement("a");
      link.href = url;
      link.download = filename;
      document.body.appendChild(link);
      link.click();
      link.remove();
      URL.revokeObjectURL(url);

      onSuccess?.(`Đã tải xuống báo cáo ${typeLabel} (${filename}).`);
      onClose();
    } catch {
      setError("Không thể xuất dữ liệu lúc này. Vui lòng thử lại.");
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <div
      className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm"
      onClick={(e) => {
        if (e.target === e.currentTarget) onClose();
      }}
      role="dialog"
      aria-modal="true"
      aria-labelledby="export-modal-title"
    >
      <div className="w-full max-w-[460px] rounded-[22px] bg-surface p-8 shadow-2xl border border-border">
        <div className="mb-4 flex h-14 w-14 items-center justify-center rounded-[16px] bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-[0_8px_24px_rgba(16,185,129,0.4)]">
          <FileSpreadsheet className="h-7 w-7" aria-hidden="true" />
        </div>
        <h3 id="export-modal-title" className="mb-1 text-xl font-black text-fg">
          Xuất Dữ Liệu
        </h3>
        <p className="mb-6 text-sm font-medium text-muted">
          Chọn khoảng thời gian để xuất báo cáo {typeLabel} (CSV)
        </p>

        {error && (
          <div
            role="alert"
            className="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-xs font-bold text-red-600 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300"
          >
            {error}
          </div>
        )}

        <form onSubmit={handleSubmit}>
          <div className="mb-5 grid grid-cols-2 gap-4">
            <div>
              <label
                htmlFor="export-from"
                className="mb-2 block text-xs font-bold text-fg"
              >
                Từ ngày
              </label>
              <input
                id="export-from"
                type="date"
                value={fromDate}
                max={toDate || undefined}
                onChange={(e) => setFromDate(e.target.value)}
                className="w-full rounded-xl border-2 border-border bg-bg px-3.5 py-2.5 text-sm font-medium text-fg outline-none transition-colors focus:border-emerald-500"
              />
            </div>
            <div>
              <label
                htmlFor="export-to"
                className="mb-2 block text-xs font-bold text-fg"
              >
                Đến ngày
              </label>
              <input
                id="export-to"
                type="date"
                value={toDate}
                min={fromDate || undefined}
                onChange={(e) => setToDate(e.target.value)}
                className="w-full rounded-xl border-2 border-border bg-bg px-3.5 py-2.5 text-sm font-medium text-fg outline-none transition-colors focus:border-emerald-500"
              />
            </div>
          </div>

          <div className="mb-6 flex items-start gap-2 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700 dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-blue-300">
            <Info className="mt-0.5 h-4 w-4 shrink-0" aria-hidden="true" />
            <span>Để trống nếu muốn xuất toàn bộ dữ liệu.</span>
          </div>

          <button
            type="submit"
            disabled={submitting}
            className="mb-2 inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 py-3.5 text-sm font-extrabold text-white transition-all hover:-translate-y-0.5 hover:shadow-[0_8px_24px_rgba(16,185,129,0.4)] disabled:opacity-60"
          >
            <Download className="h-4 w-4" aria-hidden="true" />
            {submitting ? "Đang tạo file..." : "Tải file CSV"}
          </button>
          <button
            type="button"
            onClick={onClose}
            className="w-full cursor-pointer rounded-xl bg-surface-2 py-3 text-sm font-bold text-muted transition-colors hover:text-fg"
          >
            Hủy bỏ
          </button>
        </form>
      </div>
    </div>
  );
}
