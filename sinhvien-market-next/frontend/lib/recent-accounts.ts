import { RECENT_ACCOUNTS_KEY } from "@/lib/constants/auth";

export interface RecentAccount {
  name: string;
  email: string;
}

const MAX_ITEMS = 3;
const EVENT = "svm:recent-accounts";

function read(): RecentAccount[] {
  return parseRecentAccounts(getRecentAccountsSnapshot());
}

function write(list: RecentAccount[]) {
  try {
    localStorage.setItem(RECENT_ACCOUNTS_KEY, JSON.stringify(list.slice(0, MAX_ITEMS)));
  } catch {
    // ignore storage errors
  }
  window.dispatchEvent(new Event(EVENT));
}

export function addRecentAccount(name: string, email: string): void {
  const list = read().filter((a) => a.email !== email);
  list.unshift({ name, email });
  write(list);
}

export function removeRecentAccount(email: string): void {
  write(read().filter((a) => a.email !== email));
}

export function subscribeRecentAccounts(callback: () => void): () => void {
  window.addEventListener(EVENT, callback);
  window.addEventListener("storage", callback);
  return () => {
    window.removeEventListener(EVENT, callback);
    window.removeEventListener("storage", callback);
  };
}

export function getRecentAccountsSnapshot(): string {
  try {
    return localStorage.getItem(RECENT_ACCOUNTS_KEY) ?? "";
  } catch {
    return "";
  }
}

export function parseRecentAccounts(raw: string): RecentAccount[] {
  if (!raw) return [];
  try {
    const v: unknown = JSON.parse(raw);
    if (!Array.isArray(v)) return [];
    return v
      .filter(
        (a): a is RecentAccount =>
          typeof a === "object" &&
          a !== null &&
          typeof (a as RecentAccount).name === "string" &&
          typeof (a as RecentAccount).email === "string",
      )
      .slice(0, MAX_ITEMS);
  } catch {
    return [];
  }
}
