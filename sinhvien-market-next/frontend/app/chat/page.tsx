"use client";

import { useCallback, useEffect, useState } from "react";
import Link from "next/link";
import { ArrowRight, MessageCircle, PackageOpen, LoaderCircle } from "lucide-react";
import { useMainShell } from "@/components/layout/MainShell";
import {
  FlowAlert,
  UserFlowApp,
  UserFlowLayout,
} from "@/components/flows/FlowFrame";
import {
  getConversations,
  type ConversationSummary,
} from "@/lib/api/user-flows";

function ChatListContent() {
  const { user, loadingUser } = useMainShell();
  const [conversations, setConversations] = useState<ConversationSummary[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  const refresh = useCallback(async () => {
    if (!user) return;
    setLoading(true);
    try {
      setConversations(await getConversations(user));
      setError("");
    } catch (cause) {
      setError(cause instanceof Error ? cause.message : "Không tải được tin nhắn.");
    } finally {
      setLoading(false);
    }
  }, [user]);

  useEffect(() => {
    if (loadingUser || !user) return;
    void Promise.resolve().then(refresh);
  }, [loadingUser, refresh, user]);

  return (
    <UserFlowLayout
      title="Tin nhắn"
      description="Trao đổi với người bán để hỏi thông tin và thống nhất giao dịch."
    >
      <FlowAlert error={error} />
      {loading ? (
        <div className="rounded-2xl border border-border bg-surface py-16 text-center text-muted">
          <LoaderCircle className="mx-auto mb-2 h-6 w-6 animate-spin" />
          Đang tải hội thoại...
        </div>
      ) : conversations.length === 0 ? (
        <div className="rounded-2xl border border-dashed border-border bg-surface px-6 py-16 text-center">
          <MessageCircle className="mx-auto mb-3 h-10 w-10 text-muted/50" />
          <h2 className="font-bold text-fg">Chưa có cuộc trò chuyện</h2>
          <p className="mt-1 text-sm text-muted">
            Mở chi tiết một sản phẩm và chọn “Nhắn người bán” để bắt đầu.
          </p>
          <Link
            href="/products"
            className="mt-5 inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-bold text-white"
          >
            Xem sản phẩm <ArrowRight className="h-4 w-4" />
          </Link>
        </div>
      ) : (
        <div className="overflow-hidden rounded-2xl border border-border bg-surface">
          {conversations.map((conversation) => (
            <Link
              key={conversation.id}
              href={`/chat/${conversation.id}`}
              className="flex items-center gap-4 border-b border-border p-4 transition last:border-b-0 hover:bg-surface-2"
            >
              <span className="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-primary/10 font-black text-primary">
                {conversation.otherUserName.trim().charAt(0).toLocaleUpperCase("vi")}
              </span>
              <span className="min-w-0 flex-1">
                <span className="flex flex-wrap items-center gap-2 font-bold text-fg">
                  {conversation.otherUserName}
                  {conversation.unreadCount > 0 && (
                    <span className="rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-black text-white">
                      {conversation.unreadCount}
                    </span>
                  )}
                </span>
                <span className="mt-1 flex items-center gap-1.5 truncate text-xs text-muted">
                  <PackageOpen className="h-3.5 w-3.5 shrink-0" />
                  {conversation.productTitle} · {conversation.lastMessage}
                </span>
              </span>
              <span className="shrink-0 text-xs text-muted">
                {new Intl.DateTimeFormat("vi-VN", {
                  dateStyle: "short",
                  timeStyle: "short",
                }).format(new Date(conversation.updatedAt))}
              </span>
            </Link>
          ))}
        </div>
      )}
    </UserFlowLayout>
  );
}

export default function ChatPage() {
  return (
    <UserFlowApp>
      <ChatListContent />
    </UserFlowApp>
  );
}
