"use client";

import { useSyncExternalStore } from "react";
import { X } from "lucide-react";
import {
  getRecentAccountsSnapshot,
  parseRecentAccounts,
  removeRecentAccount,
  subscribeRecentAccounts,
} from "@/lib/recent-accounts";

const getServerSnapshot = () => "";

function avatarGradient(email: string): string {
  let hash = 0;
  for (let i = 0; i < email.length; i++) {
    hash = (hash << 5) - hash + email.charCodeAt(i);
    hash |= 0;
  }
  const hue = Math.abs(hash % 360);
  return `linear-gradient(135deg, hsl(${hue}, 70%, 60%), hsl(${(hue + 40) % 360}, 70%, 50%))`;
}

export interface RecentAccountsProps {
  onSelect: (email: string) => void;
}

export function RecentAccounts({ onSelect }: RecentAccountsProps) {
  const raw = useSyncExternalStore(
    subscribeRecentAccounts,
    getRecentAccountsSnapshot,
    getServerSnapshot,
  );
  const list = parseRecentAccounts(raw);
  if (list.length === 0) return null;

  return (
    <div className="mb-4">
      <p className="mb-3 text-center text-xs font-semibold text-muted">
        Tiếp tục với tài khoản gần đây
      </p>
      <div className="mb-4 flex flex-wrap justify-center gap-3">
        {list.map((acc) => {
          const initial = (acc.name.trim().charAt(0) || "?").toUpperCase();
          return (
            <div key={acc.email} className="relative text-center">
              <button
                type="button"
                onClick={() => removeRecentAccount(acc.email)}
                aria-label={`Xoá tài khoản gần đây ${acc.name} (${acc.email})`}
                className="absolute -top-1 -right-1 z-10 flex h-5 w-5 cursor-pointer items-center justify-center rounded-full bg-surface-2 text-muted shadow-xs transition-colors hover:bg-danger hover:text-white"
              >
                <X className="h-3 w-3" aria-hidden="true" />
              </button>
              <button
                type="button"
                onClick={() => onSelect(acc.email)}
                title={`${acc.name} (${acc.email})`}
                className="group flex cursor-pointer flex-col items-center rounded-sm px-1.5 py-1 transition-transform hover:-translate-y-0.5"
              >
                <span
                  aria-hidden="true"
                  className="flex h-11 w-11 items-center justify-center rounded-full text-base font-extrabold text-white shadow-sm transition-shadow group-hover:shadow-md-p"
                  style={{ background: avatarGradient(acc.email) }}
                >
                  {initial}
                </span>
                <span className="mt-1 max-w-[68px] truncate text-xs font-medium text-muted group-hover:text-fg">
                  {acc.name}
                </span>
              </button>
            </div>
          );
        })}
      </div>

      <div className="flex items-center gap-3 text-xs text-muted">
        <div className="h-px flex-1 bg-border" />
        <span>hoặc dùng email khác</span>
        <div className="h-px flex-1 bg-border" />
      </div>
    </div>
  );
}
