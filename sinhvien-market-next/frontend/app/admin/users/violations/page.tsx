"use client";

import { Suspense, useEffect, useState } from "react";
import Link from "next/link";
import { useSearchParams } from "next/navigation";
import {
  AlertTriangle,
  ArrowLeft,
  Calendar,
  CheckCircle2,
  Clock,
  ExternalLink,
  Info,
  Lock,
  ShieldCheck,
  UserCog,
} from "lucide-react";
import { AdminShell } from "@/components/admin/AdminShell";
import { getAdminUserViolations } from "@/lib/api/admin";
import { isApiError } from "@/lib/api/types";
import type { AdminUserRecord, ViolationRecord } from "@/lib/types/admin";
import { formatDateTimeVi, formatDateVi } from "@/lib/validation/admin";

function StrikeStatusBadge({ count }: { count: number }) {
  if (count >= 3) {
    return (
      <span className="inline-flex items-center gap-1.5 rounded-full bg-red-600 px-4 py-2 text-sm font-bold text-white shadow-xs">
        <Lock className="h-4 w-4" aria-hidden="true" />
        Bị khóa vĩnh viễn
      </span>
    );
  }
  if (count === 2) {
    return (
      <span className="inline-flex items-center gap-1.5 rounded-full bg-amber-500 px-4 py-2 text-sm font-bold text-white shadow-xs">
        <AlertTriangle className="h-4 w-4" aria-hidden="true" />
        Đang bị khóa 7 ngày
      </span>
    );
  }
  if (count === 1) {
    return (
      <span className="inline-flex items-center gap-1.5 rounded-full bg-sky-500 px-4 py-2 text-sm font-bold text-white shadow-xs">
        <Info className="h-4 w-4" aria-hidden="true" />
        Đã cảnh cáo
      </span>
    );
  }
  return (
    <span className="inline-flex items-center gap-1.5 rounded-full bg-green-500 px-4 py-2 text-sm font-bold text-white shadow-xs">
      <CheckCircle2 className="h-4 w-4" aria-hidden="true" />
      Tài khoản sạch
    </span>
  );
}

function ViolationsContent() {
  const params = useSearchParams();
  const id = Number(params.get("id"));
  const [data, setData] = useState<{
    user: AdminUserRecord;
    violations: ViolationRecord[];
  } | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    let alive = true;
    if (!Number.isInteger(id) || id <= 0) {
      Promise.resolve().then(() => {
        if (alive) setError("Không tìm thấy người dùng.");
      });
      return () => {
        alive = false;
      };
    }
    getAdminUserViolations(id)
      .then((res) => {
        if (alive) setData(res);
      })
      .catch((e) => {
        if (alive) {
          setError(isApiError(e) ? e.message : "Không thể tải lịch sử vi phạm.");
        }
      });
    return () => {
      alive = false;
    };
  }, [id]);

  if (error) {
    return (
      <div className="rounded-[20px] border border-border bg-surface p-16 text-center">
        <p className="mb-4 text-sm font-semibold text-muted">{error}</p>
        <Link
          href="/admin/users"
          className="text-sm font-bold text-primary no-underline hover:underline"
        >
          Quay lại danh sách người dùng
        </Link>
      </div>
    );
  }

  if (!data) {
    return <div className="p-16 text-center text-sm text-muted">Đang tải...</div>;
  }

  const { user, violations } = data;

  return (
    <div>
      <div className="mb-6 flex flex-col justify-between gap-4 md:flex-row md:items-center">
        <div>
          <h2 className="mb-1 flex items-center text-2xl font-black text-fg">
            <Clock className="mr-2 h-6 w-6 text-amber-500" aria-hidden="true" />
            Lịch sử vi phạm
          </h2>
          <p className="m-0 text-sm text-muted">
            Tài khoản: <strong className="text-fg">{user.name}</strong> ({user.email})
          </p>
        </div>
        <div className="flex flex-wrap gap-2">
          <Link
            href={`/admin/users/detail?id=${user.id}`}
            className="inline-flex items-center justify-center gap-2 rounded-full border-[1.5px] border-border bg-surface px-5 py-2.5 text-sm font-extrabold text-fg no-underline transition-all hover:-translate-y-0.5 hover:shadow-md"
          >
            <UserCog className="h-4 w-4" aria-hidden="true" />
            Hồ sơ
          </Link>
          <Link
            href="/admin/users"
            className="inline-flex items-center justify-center gap-2 rounded-full border-[1.5px] border-border bg-surface px-5 py-2.5 text-sm font-extrabold text-fg no-underline transition-all hover:-translate-y-0.5 hover:shadow-md"
          >
            <ArrowLeft className="h-4 w-4" aria-hidden="true" />
            Quay lại
          </Link>
        </div>
      </div>

      <div className="mb-6 grid grid-cols-1 gap-5 md:grid-cols-3">
        <div className="relative flex flex-col justify-center overflow-hidden rounded-[20px] border border-red-200 bg-red-50/50 p-6 shadow-xs md:col-span-1 dark:border-red-500/20 dark:bg-red-500/5">
          <AlertTriangle
            className="pointer-events-none absolute -right-3 -bottom-3 h-24 w-24 text-red-500/10"
            aria-hidden="true"
          />
          <h3 className="relative z-10 mb-3 text-[11px] font-black tracking-widest text-red-600 uppercase dark:text-red-500">
            Tổng số gậy
          </h3>
          <div className="relative z-10 mb-4 text-[3.5rem] leading-none font-black text-red-600 dark:text-red-500">
            {user.strikeCount}/3
          </div>
          <div className="relative z-10">
            <StrikeStatusBadge count={user.strikeCount} />
          </div>
        </div>

        <div className="rounded-[20px] border border-border bg-surface p-6 shadow-xs md:col-span-2">
          <h3 className="mb-5 text-[11px] font-black tracking-widest text-muted uppercase">
            Thông tin tài khoản
          </h3>
          <div className="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div className="rounded-xl border border-border bg-surface-2 p-4">
              <div className="mb-1 flex items-center gap-1.5 text-xs font-bold text-muted">
                <Calendar className="h-3.5 w-3.5" aria-hidden="true" />
                Ngày tham gia
              </div>
              <div className="text-sm font-extrabold text-fg">
                {formatDateTimeVi(user.createdAt)}
              </div>
            </div>
            <div className="rounded-xl border border-border bg-surface-2 p-4">
              <div className="mb-1 flex items-center gap-1.5 text-xs font-bold text-muted">
                <ShieldCheck className="h-3.5 w-3.5" aria-hidden="true" />
                Trạng thái hiện tại
              </div>
              {user.isLocked ? (
                <div className="text-sm font-extrabold text-red-600">
                  Bị khóa (
                  {user.lockedUntil
                    ? `đến ${formatDateVi(user.lockedUntil)}`
                    : "Vĩnh viễn"}
                  )
                </div>
              ) : (
                <div className="text-sm font-extrabold text-green-600">Hoạt động</div>
              )}
            </div>
            {user.lockReason && (
              <div className="rounded-xl border border-red-100 bg-red-50 p-4 sm:col-span-2 dark:border-red-500/10 dark:bg-red-500/5">
                <div className="mb-1 text-xs font-bold text-red-500/80">
                  Lý do khóa hiện tại
                </div>
                <div className="text-sm font-extrabold text-red-600 dark:text-red-400">
                  {user.lockReason}
                </div>
              </div>
            )}
          </div>
        </div>
      </div>

      <div className="overflow-hidden rounded-[20px] border border-border bg-surface shadow-xs">
        <div className="border-b border-border bg-surface-2/50 px-6 py-4">
          <h3 className="m-0 text-sm font-extrabold text-fg">Nhật ký nhận gậy</h3>
        </div>

        {violations.length === 0 ? (
          <div className="flex flex-col items-center justify-center p-12 text-center">
            <ShieldCheck className="mb-3 h-14 w-14 text-green-500/50" aria-hidden="true" />
            <p className="m-0 text-sm font-bold text-muted">
              Người dùng này chưa có vi phạm nào.
            </p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full min-w-[600px] border-collapse text-left">
              <thead>
                <tr className="border-b border-border text-[11px] font-bold tracking-widest text-muted uppercase">
                  <th className="px-6 py-3.5">Gậy thứ</th>
                  <th className="px-6 py-3.5">Thời gian</th>
                  <th className="px-6 py-3.5">Người xử lý</th>
                  <th className="px-6 py-3.5">Lý do / Bằng chứng</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-border/60">
                {violations.map((v) => {
                  const when = formatDateTimeVi(v.createdAt).split(" ");
                  return (
                    <tr key={v.id} className="transition-colors hover:bg-surface-2/50">
                      <td className="px-6 py-4 whitespace-nowrap">
                        <span className="inline-flex rounded-full bg-red-100 px-3 py-1.5 text-xs font-black text-red-700 dark:bg-red-500/20 dark:text-red-300">
                          Gậy {v.strikeNumber}
                        </span>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <div className="mb-0.5 text-sm font-bold text-fg">
                          {when[0]}
                        </div>
                        <div className="text-xs font-medium text-muted">{when[1]}</div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <span className="inline-flex rounded-full border border-border bg-surface-2 px-3 py-1.5 text-xs font-bold text-fg">
                          {v.adminName}
                        </span>
                      </td>
                      <td className="min-w-[250px] px-6 py-4">
                        <div className="mb-2 text-sm leading-relaxed font-bold whitespace-pre-line text-fg">
                          {v.reason}
                        </div>
                        {v.evidenceUrl && (
                          <a
                            href={v.evidenceUrl}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="inline-flex items-center gap-1.5 rounded-lg border border-border px-3 py-1.5 text-xs font-bold text-muted no-underline transition-colors hover:text-fg"
                          >
                            <ExternalLink className="h-3.5 w-3.5" aria-hidden="true" />
                            Xem bằng chứng
                          </a>
                        )}
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
}

export default function AdminUserViolationsPage() {
  const [refreshKey, setRefreshKey] = useState(0);
  return (
    <AdminShell
      title="Lịch sử vi phạm"
      onResetMock={() => setRefreshKey((k) => k + 1)}
    >
      <Suspense
        fallback={<div className="p-16 text-center text-sm text-muted">Đang tải...</div>}
      >
        <ViolationsContent key={refreshKey} />
      </Suspense>
    </AdminShell>
  );
}
