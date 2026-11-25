"use client";

import type Pusher from "pusher-js";
import type { Channel } from "pusher-js";
import { apiRequest, getCsrfToken } from "@/lib/api/client";
import { API_URL, USE_MOCK } from "@/lib/config";

/**
 * Realtime chat over Pusher private channels.
 *
 * The PHP API publishes "new_message" on `private-chat.{userId}`. The browser
 * may only subscribe to its own channel: the subscription is signed by
 * POST /api/realtime/auth (session + CSRF). When Pusher is not configured or
 * unreachable, callers keep their polling fallback.
 */

interface RealtimeConfig {
  enabled: boolean;
  key: string | null;
  cluster: string;
}

/** Payload sent by ChatController (only the fields the UI relies on). */
export interface ChatEvent {
  conv_id: number;
  id: number;
  msg_type?: string;
  update_offer_id?: number;
}

let clientPromise: Promise<Pusher | null> | null = null;

async function createClient(): Promise<Pusher | null> {
  if (USE_MOCK || typeof window === "undefined") return null;
  const config = await apiRequest<RealtimeConfig>("/realtime/config").catch(() => null);
  if (!config?.enabled || !config.key) return null;
  const { default: PusherClient } = await import("pusher-js");
  return new PusherClient(config.key, {
    cluster: config.cluster,
    channelAuthorization: {
      endpoint: `${API_URL}/realtime/auth`,
      transport: "ajax",
      // Our own request so the session cookie and the CSRF header are sent.
      customHandler: ({ socketId, channelName }, callback) => {
        void (async () => {
          try {
            const token = await getCsrfToken();
            const response = await fetch(`${API_URL}/realtime/auth`, {
              method: "POST",
              credentials: "include",
              headers: { "Content-Type": "application/x-www-form-urlencoded", "X-CSRF-Token": token },
              body: new URLSearchParams({ socket_id: socketId, channel_name: channelName }),
            });
            if (!response.ok) throw new Error(`Realtime auth failed (${response.status})`);
            callback(null, await response.json());
          } catch (error) {
            callback(error instanceof Error ? error : new Error(String(error)), null);
          }
        })();
      },
    },
  });
}

/**
 * Listen to the signed-in user's chat events. `onStatus(true)` once the
 * private channel is live, `false` if realtime is unavailable.
 * Returns an unsubscribe function.
 */
export function subscribeToChat(
  userId: number,
  onEvent: (event: ChatEvent) => void,
  onStatus?: (live: boolean) => void,
): () => void {
  let channel: Channel | null = null;
  let cancelled = false;
  const live = () => onStatus?.(true);
  const failed = () => onStatus?.(false);

  clientPromise ??= createClient().catch(() => null);
  void clientPromise.then((client) => {
    if (cancelled) return;
    if (!client) {
      failed();
      return;
    }
    const name = `private-chat.${userId}`;
    // Another account signed in this tab earlier: drop its channel.
    for (const other of client.allChannels()) {
      if (other.name.startsWith("private-chat.") && other.name !== name) client.unsubscribe(other.name);
    }
    channel = client.channel(name) ?? client.subscribe(name);
    channel.bind("new_message", onEvent);
    channel.bind("pusher:subscription_succeeded", live);
    channel.bind("pusher:subscription_error", failed);
    if (channel.subscribed) live();
  });

  return () => {
    cancelled = true;
    channel?.unbind("new_message", onEvent);
    channel?.unbind("pusher:subscription_succeeded", live);
    channel?.unbind("pusher:subscription_error", failed);
  };
}
