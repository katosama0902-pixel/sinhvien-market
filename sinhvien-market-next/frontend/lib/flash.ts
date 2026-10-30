export type FlashVariant = "success" | "info" | "warning" | "danger";

export interface Flash {
  variant: FlashVariant;
  message: string;
  action?: { label: string; href: string };
}

const KEY = "svm_flash";
const EVENT = "svm:flash";

export function setFlash(flash: Flash): void {
  try {
    sessionStorage.setItem(KEY, JSON.stringify(flash));
  } catch {
    // ignore
  }
}

export function clearFlash(): void {
  try {
    sessionStorage.removeItem(KEY);
  } catch {
    // ignore
  }
  if (typeof window !== "undefined") window.dispatchEvent(new Event(EVENT));
}

export function subscribeFlash(callback: () => void): () => void {
  window.addEventListener(EVENT, callback);
  return () => window.removeEventListener(EVENT, callback);
}

export function getFlashSnapshot(): string {
  try {
    return sessionStorage.getItem(KEY) ?? "";
  } catch {
    return "";
  }
}

export function parseFlash(raw: string): Flash | null {
  if (!raw) return null;
  try {
    const v = JSON.parse(raw) as Partial<Flash>;
    if (
      typeof v.message === "string" &&
      (v.variant === "success" ||
        v.variant === "info" ||
        v.variant === "warning" ||
        v.variant === "danger")
    ) {
      return {
        variant: v.variant,
        message: v.message,
        action:
          v.action &&
          typeof v.action.label === "string" &&
          typeof v.action.href === "string"
            ? v.action
            : undefined,
      };
    }
  } catch {
    // ignore invalid json
  }
  return null;
}
