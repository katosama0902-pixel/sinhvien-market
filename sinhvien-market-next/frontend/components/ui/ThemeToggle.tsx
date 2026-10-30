"use client";

import { useSyncExternalStore } from "react";
import { Moon, Sun } from "lucide-react";
import { cn } from "@/lib/cn";

const STORAGE_KEY = "svmarket_theme";

function subscribe(callback: () => void) {
  const observer = new MutationObserver(callback);
  observer.observe(document.documentElement, {
    attributes: true,
    attributeFilter: ["class"],
  });
  return () => observer.disconnect();
}

const getSnapshot = () => document.documentElement.classList.contains("dark");
const getServerSnapshot = () => false;

export function ThemeToggle({ className }: { className?: string }) {
  const dark = useSyncExternalStore(subscribe, getSnapshot, getServerSnapshot);

  function toggle() {
    const next = !dark;
    document.documentElement.classList.toggle("dark", next);
    try {
      // Chỉ lưu lựa chọn giao diện, không lưu dữ liệu nhạy cảm.
      localStorage.setItem(STORAGE_KEY, next ? "dark" : "light");
    } catch {
      /* trình duyệt chặn localStorage: bỏ qua */
    }
  }

  return (
    <button
      type="button"
      onClick={toggle}
      aria-label={dark ? "Chuyển sang giao diện sáng" : "Chuyển sang giao diện tối"}
      aria-pressed={dark}
      suppressHydrationWarning
      className={cn(
        "inline-flex h-10 w-10 items-center justify-center rounded-field border-2 border-border bg-surface text-fg",
        "transition-colors hover:border-primary hover:text-primary",
        className,
      )}
    >
      {dark ? (
        <Sun className="h-4 w-4" aria-hidden="true" />
      ) : (
        <Moon className="h-4 w-4" aria-hidden="true" />
      )}
    </button>
  );
}
