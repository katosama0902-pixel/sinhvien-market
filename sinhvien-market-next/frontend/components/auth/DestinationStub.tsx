"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { CheckCircle2, LogOut } from "lucide-react";
import { AuthShell } from "@/components/auth/AuthShell";
import { FlashBanner } from "@/components/auth/FlashBanner";
import { Button } from "@/components/ui/Button";
import { getCurrentUser, logout } from "@/lib/api/auth";
import type { AuthUser, Role } from "@/lib/api/types";
import { clearFlash, setFlash } from "@/lib/flash";

export interface DestinationStubProps {
  expectedRole: Role;
  title: string;
}

export function DestinationStub({ expectedRole, title }: DestinationStubProps) {
  const router = useRouter();
  const [user, setUser] = useState<AuthUser | null>(null);
  const [loggingOut, setLoggingOut] = useState(false);

  useEffect(() => {
    let alive = true;
    getCurrentUser()
      .then((u) => {
        if (!alive) return;
        if (!u) {
          router.replace(expectedRole === "admin" ? "/admin-login" : "/login");
          return;
        }
        setUser(u);
      })
      .catch(() => {
        // ignore
      });
    return () => {
      alive = false;
    };
  }, [expectedRole, router]);

  async function handleLogout() {
    setLoggingOut(true);
    clearFlash();
    try {
      await logout();
      setFlash({ variant: "success", message: "Đã đăng xuất thành công." });
      router.push(expectedRole === "admin" ? "/admin-login" : "/login");
    } finally {
      setLoggingOut(false);
    }
  }

  return (
    <AuthShell
      headerIcon={<CheckCircle2 className="h-6 w-6" />}
      heading={title}
      subtitle="Trang đích tạm (stub) để kiểm tra luồng đăng nhập và đăng xuất."
    >
      <FlashBanner />

      {user ? (
        <div className="mb-5 space-y-1.5 rounded-field border border-border bg-surface-2 p-4 text-sm">
          <p>
            <span className="text-muted">Họ tên:</span>{" "}
            <strong className="text-fg">{user.name}</strong>
          </p>
          <p>
            <span className="text-muted">Email:</span>{" "}
            <strong className="text-fg">{user.email}</strong>
          </p>
          <p>
            <span className="text-muted">Vai trò:</span>{" "}
            <strong className="uppercase text-primary">{user.role}</strong>
          </p>
        </div>
      ) : (
        <p className="mb-5 text-center text-sm text-muted">
          Đang kiểm tra phiên đăng nhập...
        </p>
      )}

      <div className="space-y-2.5">
        <Button
          variant="danger"
          size="lg"
          fullWidth
          loading={loggingOut}
          loadingText="Đang đăng xuất..."
          onClick={handleLogout}
        >
          <LogOut className="h-4 w-4" aria-hidden="true" />
          Đăng xuất
        </Button>

        <div className="pt-1 text-center">
          <Link
            href="/login"
            className="text-xs font-medium text-muted hover:text-primary"
          >
            Quay về trang Đăng nhập
          </Link>
        </div>
      </div>
    </AuthShell>
  );
}
