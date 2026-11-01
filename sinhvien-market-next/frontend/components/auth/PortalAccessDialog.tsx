"use client";

import { ShieldAlert } from "lucide-react";

interface PortalAccessDialogProps {
  title: string;
  description: string;
  onClose: () => void;
  onConfirm?: () => void;
  confirmLabel?: string;
  cancelLabel?: string;
  busy?: boolean;
}

export function PortalAccessDialog({
  title,
  description,
  onClose,
  onConfirm,
  confirmLabel = "Đã hiểu",
  cancelLabel = "Ở lại",
  busy = false,
}: PortalAccessDialogProps) {
  return (
    <div className="modal-backdrop-enter fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm">
      <section
        role="alertdialog"
        aria-modal="true"
        aria-labelledby="portal-access-title"
        aria-describedby="portal-access-description"
        className="modal-enter w-full max-w-md rounded-3xl border border-rose-500/20 bg-surface p-7 text-fg shadow-2xl"
      >
        <div className="mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-500/10 text-rose-500">
          <ShieldAlert className="h-7 w-7" aria-hidden="true" />
        </div>
        <h2 id="portal-access-title" className="text-xl font-black text-fg">
          {title}
        </h2>
        <p
          id="portal-access-description"
          className="mt-3 text-sm leading-6 text-muted"
        >
          {description}
        </p>
        <div className="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
          {onConfirm && (
            <button
              type="button"
              onClick={onClose}
              disabled={busy}
              className="rounded-xl border border-border px-4 py-3 text-sm font-bold text-fg transition hover:bg-surface-2 disabled:opacity-50"
            >
              {cancelLabel}
            </button>
          )}
          <button
            type="button"
            onClick={onConfirm ?? onClose}
            disabled={busy}
            className="rounded-xl bg-gradient-to-r from-rose-500 to-red-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-rose-500/20 transition hover:brightness-110 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-500 disabled:cursor-wait disabled:opacity-60"
          >
            {busy ? "Đang xử lý..." : confirmLabel}
          </button>
        </div>
      </section>
    </div>
  );
}
