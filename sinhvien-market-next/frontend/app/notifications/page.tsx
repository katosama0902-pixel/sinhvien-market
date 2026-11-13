"use client";

import { useCallback, useEffect, useState } from "react";
import Link from "next/link";
import {
  Bell,
  CheckCheck,
  Clock3,
  LoaderCircle,
  PackageCheck,
} from "lucide-react";
import { useMainShell } from "@/components/layout/MainShell";
import {
  FlowAlert,
  UserFlowApp,
  UserFlowLayout,
} from "@/components/flows/FlowFrame";
import {
  getNotifications,
  markNotificationRead,
  type UserNotification,
} from "@/lib/api/user-flows";

function NotificationsContent() {
  const { user, loadingUser } = useMainShell();
  const [items, setItems] = useState<UserNotification[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  const refresh = useCallback(async () => {
    if (!user) return;
    setLoading(true);
    try {
      setItems(await getNotifications(user));
      setError("");
    } catch (cause) {
      setError(cause instanceof Error ? cause.message : "Không tải được thông báo.");
    } finally {
      setLoading(false);
    }
  }, [user]);

  useEffect(() => {
    if (loadingUser || !user) return;
    void Promise.resolve().then(refresh);
  }, [loadingUser, refresh, user]);

  async function markRead(id: number) {
    setError("");
    try {
      await markNotificationRead(id);
      setItems((current) =>
        current.map((item) => (item.id === id ? { ...item, isRead: true } : item)),
      );
    } catch (cause) {
      setError(cause instanceof Error ? cause.message : "Không cập nhật được thông báo.");
    }
  }

  const unreadCount = items.filter((item) => !item.isRead).length;

  return (
    <UserFlowLayout
      title="Thông báo"
      description="Cập nhật về tin đăng, tin nhắn và hoạt động tài khoản."
    >
      <FlowAlert error={error} />
      {loading ? (
        <div className="rounded-2xl border border-border bg-surface py-16 text-center text-muted">
          <LoaderCircle className="mx-auto mb-2 h-6 w-6 animate-spin" />
          Đang tải thông báo...
        </div>
      ) : items.length === 0 ? (
        <div className="rounded-2xl border border-dashed border-border bg-surface px-6 py-16 text-center">
          <Bell className="mx-auto mb-3 h-10 w-10 text-muted/50" />
          <h2 className="font-bold text-fg">Bạn đã cập nhật</h2>
          <p className="mt-1 text-sm text-muted">
            Khi có hoạt động mới, thông báo sẽ xuất hiện ở đây.
          </p>
        </div>
      ) : (
        <>
          <div className="mb-3 flex items-center justify-between text-sm text-muted">
            <span>{unreadCount ? `${unreadCount} chưa đọc` : "Đã đọc tất cả"}</span>
            <span>{items.length} thông báo</span>
          </div>
          <div className="overflow-hidden rounded-2xl border border-border bg-surface">
            {items.map((item) => {
              const content = (
                <div className="flex items-start gap-4 p-5">
                  <span
                    className={`mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-full ${
                      item.isRead
                        ? "bg-surface-2 text-muted"
                        : "bg-primary/10 text-primary"
                    }`}
                  >
                    {item.type === "product" ? (
                      <PackageCheck className="h-5 w-5" />
                    ) : (
                      <Bell className="h-5 w-5" />
                    )}
                  </span>
                  <div className="min-w-0 flex-1">
                    <div className="flex flex-wrap items-center gap-2">
                      <h2 className="font-bold text-fg">{item.title}</h2>
                      {!item.isRead && (
                        <span className="rounded-full bg-primary/10 px-2 py-0.5 text-[10px] font-extrabold text-primary">
                          MỚI
                        </span>
                      )}
                    </div>
                    <p className="mt-1 text-sm leading-6 text-muted">
                      {item.body}
                    </p>
                    <p className="mt-2 inline-flex items-center gap-1 text-xs text-muted">
                      <Clock3 className="h-3.5 w-3.5" />
                      {new Intl.DateTimeFormat("vi-VN", {
                        dateStyle: "medium",
                        timeStyle: "short",
                      }).format(new Date(item.createdAt))}
                    </p>
                  </div>
                  {!item.isRead && (
                    <button
                      type="button"
                      onClick={(event) => {
                        event.preventDefault();
                        event.stopPropagation();
                        void markRead(item.id);
                      }}
                      className="shrink-0 rounded-lg p-2 text-muted transition hover:bg-surface-2 hover:text-primary"
                      aria-label={`Đánh dấu đã đọc: ${item.title}`}
                    >
                      <CheckCheck className="h-4 w-4" />
                    </button>
                  )}
                </div>
              );
              return (
                <article
                  key={item.id}
                  className="border-b border-border last:border-b-0"
                >
                  {item.href ? (
                    <Link
                      href={item.href}
                      onClick={() => {
                        if (!item.isRead) void markRead(item.id);
                      }}
                      className={`block transition hover:bg-surface-2 ${
                        item.isRead ? "opacity-75" : ""
                      }`}
                    >
                      {content}
                    </Link>
                  ) : (
                    <div className={item.isRead ? "opacity-75" : ""}>
                      {content}
                    </div>
                  )}
                </article>
              );
            })}
          </div>
        </>
      )}
    </UserFlowLayout>
  );
}

export default function NotificationsPage() {
  return (
    <UserFlowApp>
      <NotificationsContent />
    </UserFlowApp>
  );
}
