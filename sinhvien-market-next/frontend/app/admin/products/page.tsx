"use client";

import { useEffect, useMemo, useState } from "react";
import {
  Check,
  CheckCircle2,
  FileSpreadsheet,
  Search,
  ShoppingBag,
  Trash2,
  X,
  XCircle,
} from "lucide-react";
import { AdminShell, useAdminShell } from "@/components/admin/AdminShell";
import { ExportModal } from "@/components/admin/ExportModal";
import {
  adminInputClass,
  adminLabelClass,
  Modal,
  ModalActions,
} from "@/components/admin/Modal";
import {
  approveProduct,
  deleteProduct,
  getAdminProducts,
  rejectProduct,
} from "@/lib/api/admin";
import { isApiError } from "@/lib/api/types";
import { cn } from "@/lib/cn";
import { PRODUCT_STATUS_MAP, PRODUCT_TYPE_MAP } from "@/lib/constants/admin";
import type { AdminProduct } from "@/lib/types/admin";
import { formatDateTimeVi, formatVnd } from "@/lib/validation/admin";

type Tab = "pending" | "all";

const REJECT_PRESETS = [
  "Hình ảnh không rõ ràng hoặc không đúng sản phẩm",
  "Mô tả thiếu thông tin hoặc sai danh mục",
  "Sản phẩm thuộc nhóm hàng cấm trên sàn",
  "Giá đăng bất thường, nghi vấn lừa đảo",
];

function ProductsContent() {
  const { showToast } = useAdminShell();
  const [tab, setTab] = useState<Tab>("pending");
  const [products, setProducts] = useState<AdminProduct[] | null>(null);
  const [version, setVersion] = useState(0);
  const [query, setQuery] = useState("");
  const [busyId, setBusyId] = useState<number | null>(null);
  const [rejecting, setRejecting] = useState<AdminProduct | null>(null);
  const [rejectReason, setRejectReason] = useState("");
  const [deleting, setDeleting] = useState<AdminProduct | null>(null);
  const [exportOpen, setExportOpen] = useState(false);

  useEffect(() => {
    let alive = true;
    getAdminProducts(tab).then((data) => {
      if (alive) setProducts(data);
    });
    return () => {
      alive = false;
    };
  }, [tab, version]);

  const visible = useMemo(() => {
    if (!products) return [];
    const q = query.trim().toLowerCase();
    if (!q) return products;
    return products.filter(
      (p) =>
        p.title.toLowerCase().includes(q) ||
        p.sellerName.toLowerCase().includes(q) ||
        p.sellerEmail.toLowerCase().includes(q),
    );
  }, [products, query]);

  function switchTab(next: Tab) {
    if (next === tab) return;
    setProducts(null);
    setTab(next);
  }

  async function run(
    id: number,
    action: () => Promise<{ message: string }>,
    variant: "success" | "warning" = "success",
  ) {
    setBusyId(id);
    try {
      const res = await action();
      showToast(res.message, variant);
      setVersion((v) => v + 1);
    } catch (e) {
      showToast(
        isApiError(e) ? e.message : "Đã có lỗi xảy ra. Vui lòng thử lại.",
        "error",
      );
    } finally {
      setBusyId(null);
    }
  }

  async function confirmReject() {
    if (!rejecting) return;
    const target = rejecting;
    const reason = rejectReason;
    setRejecting(null);
    setRejectReason("");
    await run(target.id, () => rejectProduct(target.id, reason), "warning");
  }

  async function confirmDelete() {
    if (!deleting) return;
    const target = deleting;
    setDeleting(null);
    await run(target.id, () => deleteProduct(target.id));
  }

  return (
    <div>
      <div className="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <h2 className="m-0 flex items-center gap-2 text-xl font-extrabold text-fg">
          <ShoppingBag className="h-5 w-5 text-amber-500" aria-hidden="true" />
          Kiểm duyệt bài đăng
        </h2>
        <button
          type="button"
          onClick={() => setExportOpen(true)}
          className="inline-flex cursor-pointer items-center justify-center gap-2 rounded-full bg-emerald-500 px-5 py-2.5 text-sm font-bold text-white shadow-xs transition-all hover:-translate-y-0.5 hover:bg-emerald-600"
        >
          <FileSpreadsheet className="h-4 w-4" aria-hidden="true" />
          Xuất Excel (CSV)
        </button>
      </div>

      <div className="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div
          role="tablist"
          className="flex w-fit gap-1 rounded-xl bg-surface-2 p-1"
        >
          {(
            [
              ["pending", "Chờ duyệt"],
              ["all", "Tất cả bài đăng"],
            ] as const
          ).map(([value, label]) => (
            <button
              key={value}
              type="button"
              role="tab"
              aria-selected={tab === value}
              onClick={() => switchTab(value)}
              className={cn(
                "cursor-pointer rounded-lg px-5 py-2 text-sm font-bold transition-all",
                tab === value
                  ? "bg-surface text-fg shadow-xs"
                  : "text-muted hover:text-fg",
              )}
            >
              {label}
              {value === "pending" && tab === "pending" && products && (
                <span className="ml-1.5 rounded-full bg-amber-500 px-2 py-0.5 text-[10px] font-black text-white">
                  {products.length}
                </span>
              )}
            </button>
          ))}
        </div>

        <div className="relative w-full sm:w-72">
          <Search
            className="pointer-events-none absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-muted"
            aria-hidden="true"
          />
          <input
            type="search"
            value={query}
            onChange={(e) => setQuery(e.target.value)}
            placeholder="Tìm theo tên bài, người bán..."
            aria-label="Tìm bài đăng"
            className={cn(adminInputClass, "py-2.5 pl-10")}
          />
        </div>
      </div>

      <div className="overflow-hidden rounded-[20px] border border-border bg-surface shadow-xs">
        {products === null ? (
          <div className="p-16 text-center text-sm text-muted">Đang tải...</div>
        ) : visible.length === 0 ? (
          <div className="flex flex-col items-center gap-3 p-16 text-center text-muted">
            <CheckCircle2 className="h-12 w-12 text-green-500" aria-hidden="true" />
            <p className="m-0 text-sm font-semibold">
              {query
                ? "Không tìm thấy bài đăng phù hợp."
                : tab === "pending"
                  ? "Không có bài đăng nào chờ duyệt ✅"
                  : "Chưa có bài đăng nào."}
            </p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full min-w-[900px] border-collapse text-left">
              <thead>
                <tr className="border-b border-border bg-surface-2 text-[11px] font-bold tracking-wider text-muted uppercase">
                  <th className="px-5 py-3.5">Sản phẩm</th>
                  <th className="px-5 py-3.5">Người đăng</th>
                  <th className="px-5 py-3.5">Loại</th>
                  <th className="px-5 py-3.5">Giá</th>
                  <th className="px-5 py-3.5">Trạng thái</th>
                  <th className="px-5 py-3.5">Ngày đăng</th>
                  <th className="min-w-[200px] px-5 py-3.5 text-right">
                    Hành động
                  </th>
                </tr>
              </thead>
              <tbody className="divide-y divide-border/60">
                {visible.map((p) => {
                  const status = PRODUCT_STATUS_MAP[p.status];
                  const type = PRODUCT_TYPE_MAP[p.type];
                  const busy = busyId === p.id;
                  return (
                    <tr
                      key={p.id}
                      className="transition-colors hover:bg-surface-2/50"
                    >
                      <td className="px-5 py-4">
                        <div className="max-w-[260px] truncate text-sm font-bold text-fg">
                          {p.title}
                        </div>
                        <div className="mt-0.5 max-w-[260px] truncate text-xs text-muted">
                          {p.categoryName}
                        </div>
                        {p.status === "cancelled" && p.rejectionReason && (
                          <div className="mt-1 max-w-[260px] truncate text-xs text-red-500">
                            Lý do: {p.rejectionReason}
                          </div>
                        )}
                      </td>
                      <td className="px-5 py-4">
                        <div className="text-sm font-bold text-fg">
                          {p.sellerName}
                        </div>
                        <div className="text-xs text-muted">{p.sellerEmail}</div>
                      </td>
                      <td className="px-5 py-4 whitespace-nowrap">
                        <span
                          className={cn(
                            "inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-bold",
                            type.badgeClass,
                          )}
                        >
                          {type.label}
                        </span>
                      </td>
                      <td className="px-5 py-4 text-sm font-bold whitespace-nowrap text-fg">
                        {formatVnd(p.price)}
                      </td>
                      <td className="px-5 py-4 whitespace-nowrap">
                        <span
                          className={cn(
                            "inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-bold",
                            status.badgeClass,
                          )}
                        >
                          {status.label}
                        </span>
                      </td>
                      <td className="px-5 py-4 text-xs font-medium whitespace-nowrap text-muted">
                        {formatDateTimeVi(p.createdAt)}
                      </td>
                      <td className="px-5 py-4">
                        <div className="flex flex-wrap items-center justify-end gap-2">
                          {p.status === "pending" && (
                            <>
                              <button
                                type="button"
                                disabled={busy}
                                onClick={() =>
                                  run(p.id, () => approveProduct(p.id))
                                }
                                className="inline-flex cursor-pointer items-center gap-1.5 rounded-lg bg-gradient-to-br from-green-500 to-emerald-600 px-3 py-1.5 text-xs font-bold text-white transition-all hover:-translate-y-0.5 disabled:cursor-wait disabled:opacity-60"
                              >
                                <Check className="h-3.5 w-3.5" aria-hidden="true" />
                                Duyệt
                              </button>
                              <button
                                type="button"
                                disabled={busy}
                                onClick={() => {
                                  setRejectReason("");
                                  setRejecting(p);
                                }}
                                className="inline-flex cursor-pointer items-center gap-1.5 rounded-lg bg-gradient-to-br from-amber-500 to-orange-500 px-3 py-1.5 text-xs font-bold text-white transition-all hover:-translate-y-0.5 disabled:cursor-wait disabled:opacity-60"
                              >
                                <X className="h-3.5 w-3.5" aria-hidden="true" />
                                Từ chối
                              </button>
                            </>
                          )}
                          {p.status !== "sold" && (
                            <button
                              type="button"
                              disabled={busy}
                              onClick={() => setDeleting(p)}
                              title="Xóa"
                              aria-label={`Xóa bài ${p.title}`}
                              className="inline-flex h-8 w-8 cursor-pointer items-center justify-center rounded-lg border border-red-300 bg-transparent text-red-400 transition-all hover:border-red-500 hover:bg-red-500 hover:text-white disabled:cursor-wait disabled:opacity-60 dark:border-red-800"
                            >
                              <Trash2 className="h-4 w-4" aria-hidden="true" />
                            </button>
                          )}
                        </div>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        )}
      </div>

      <Modal
        open={rejecting !== null}
        onClose={() => setRejecting(null)}
        tone="amber"
        icon={<XCircle className="h-7 w-7" aria-hidden="true" />}
        title="Từ chối bài đăng"
        subtitle={rejecting?.title}
      >
        <form
          onSubmit={(e) => {
            e.preventDefault();
            void confirmReject();
          }}
        >
          <label htmlFor="reject-reason" className={adminLabelClass}>
            Lý do từ chối (gửi cho người đăng)
          </label>
          <textarea
            id="reject-reason"
            rows={3}
            value={rejectReason}
            onChange={(e) => setRejectReason(e.target.value)}
            placeholder="Nhập lý do hoặc chọn nhanh bên dưới..."
            className={cn(adminInputClass, "mb-3 resize-y")}
          />
          <div className="mb-6 flex flex-wrap gap-2">
            {REJECT_PRESETS.map((preset) => (
              <button
                key={preset}
                type="button"
                onClick={() => setRejectReason(preset)}
                className="cursor-pointer rounded-full border border-border bg-surface-2 px-3 py-1 text-xs font-semibold text-muted transition-colors hover:border-primary hover:text-primary"
              >
                {preset}
              </button>
            ))}
          </div>
          <ModalActions
            tone="amber"
            submitLabel="Xác nhận từ chối"
            onCancel={() => setRejecting(null)}
          />
        </form>
      </Modal>

      <Modal
        open={deleting !== null}
        onClose={() => setDeleting(null)}
        tone="red"
        icon={<Trash2 className="h-7 w-7" aria-hidden="true" />}
        title="Xóa bài đăng?"
        subtitle="Hành động này sẽ được ghi vào nhật ký Admin."
      >
        <p className="mb-6 rounded-xl bg-surface-2 px-4 py-3 text-sm font-semibold text-fg">
          {deleting?.title}
        </p>
        <ModalActions
          type="button"
          tone="red"
          submitLabel="Xóa bài đăng"
          onSubmit={() => void confirmDelete()}
          onCancel={() => setDeleting(null)}
        />
      </Modal>

      <ExportModal
        key={exportOpen ? "open" : "closed"}
        open={exportOpen}
        type="products"
        onClose={() => setExportOpen(false)}
        onSuccess={(msg) => showToast(msg, "success")}
      />
    </div>
  );
}

export default function AdminProductsPage() {
  const [refreshKey, setRefreshKey] = useState(0);
  return (
    <AdminShell
      title="Kiểm duyệt bài đăng"
      onResetMock={() => setRefreshKey((k) => k + 1)}
    >
      <ProductsContent key={refreshKey} />
    </AdminShell>
  );
}
