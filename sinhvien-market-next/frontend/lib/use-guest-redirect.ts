"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";
import { getCurrentUser } from "@/lib/api/auth";

export function useGuestRedirect(): void {
  const router = useRouter();
  useEffect(() => {
    let alive = true;
    getCurrentUser()
      .then((user) => {
        if (alive && user) {
          router.replace(user.role === "admin" ? "/admin" : "/products");
        }
      })
      .catch(() => {
        // ignore unauthenticated state
      });
    return () => {
      alive = false;
    };
  }, [router]);
}
