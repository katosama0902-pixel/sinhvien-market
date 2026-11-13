"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { LoaderCircle, PackagePlus } from "lucide-react";
import {
  FlowAlert,
  UserFlowApp,
  UserFlowLayout,
} from "@/components/flows/FlowFrame";
import { useMainShell } from "@/components/layout/MainShell";
import { Button } from "@/components/ui/Button";
import { getMarketplaceCategories } from "@/lib/api/marketplace";
import { createListing } from "@/lib/api/user-flows";
import type { AdminCategory, ProductCondition, ProductType } from "@/lib/types/admin";

const fieldClass =
  "mt-1.5 w-full rounded-xl border border-border bg-bg px-4 py-3 text-sm text-fg outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10";

function CreateListingContent() {
  const { user } = useMainShell();
  const router = useRouter();
  const [categories, setCategories] = useState<AdminCategory[]>([]);
  const [loadingCategories, setLoadingCategories] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState("");
  const [form, setForm] = useState({
    title: "",
    description: "",
    categoryId: "",
    type: "sale" as ProductType,
    condition: "used" as ProductCondition,
    price: "",
    startPrice: "",
    floorPrice: "",
    decreaseAmount: "5000",
    stepMinutes: "10",
  });
  const [image, setImage] = useState<File | undefined>();

  useEffect(() => {
    let active = true;
    getMarketplaceCategories()
      .then((items) => {
        if (active) setCategories(items);
      })
      .catch((cause: unknown) => {
        if (active) {
          setError(cause instanceof Error ? cause.message : "Không tải được danh mục.");
        }
      })
      .finally(() => {
        if (active) setLoadingCategories(false);
      });
    return () => {
      active = false;
    };
  }, []);

  async function submit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    if (!user) return;
    setSaving(true);
    setError("");
    try {
      const created = await createListing(user, {
        title: form.title,
        description: form.description,
        categoryId: Number(form.categoryId),
        type: form.type,
        condition: form.condition,
        image,
        price:
          form.type === "exchange"
            ? null
            : Number(form.type === "auction" ? form.startPrice : form.price),
        ...(form.type === "auction"
          ? {
              startPrice: Number(form.startPrice),
              floorPrice: Number(form.floorPrice),
              decreaseAmount: Number(form.decreaseAmount),
              stepMinutes: Number(form.stepMinutes),
            }
          : {}),
      });
      router.push(`/my-listings?created=${created.id}`);
    } catch (cause) {
      setError(
        cause instanceof Error ? cause.message : "Không thể tạo tin đăng.",
      );
    } finally {
      setSaving(false);
    }
  }

  return (
    <UserFlowLayout
      title="Đăng sản phẩm"
      description="Mô tả rõ ràng để sinh viên khác dễ tìm thấy món đồ của bạn."
    >
      <FlowAlert error={error} />
      <form
        onSubmit={submit}
        className="mx-auto max-w-3xl space-y-5 rounded-2xl border border-border bg-surface p-5 sm:p-7"
      >
        <label className="block text-sm font-bold text-fg">
          Tiêu đề tin đăng *
          <input
            required
            minLength={5}
            maxLength={150}
            className={fieldClass}
            placeholder="Ví dụ: Giáo trình Giải tích 1 còn mới"
            value={form.title}
            onChange={(event) =>
              setForm((current) => ({ ...current, title: event.target.value }))
            }
          />
        </label>

        <label className="block text-sm font-bold text-fg">
          Mô tả chi tiết *
          <textarea
            required
            minLength={10}
            maxLength={5000}
            rows={5}
            className={fieldClass}
            placeholder="Mô tả tình trạng, phụ kiện đi kèm, địa điểm giao..."
            value={form.description}
            onChange={(event) =>
              setForm((current) => ({
                ...current,
                description: event.target.value,
              }))
            }
          />
        </label>

        <div className="grid gap-4 sm:grid-cols-2">
          <label className="block text-sm font-bold text-fg">
            Danh mục *
            <select
              required
              className={fieldClass}
              disabled={loadingCategories}
              value={form.categoryId}
              onChange={(event) =>
                setForm((current) => ({
                  ...current,
                  categoryId: event.target.value,
                }))
              }
            >
              <option value="">
                {loadingCategories ? "Đang tải..." : "Chọn danh mục"}
              </option>
              {categories.map((category) => (
                <option key={category.id} value={category.id}>
                  {category.name}
                </option>
              ))}
            </select>
          </label>
          <label className="block text-sm font-bold text-fg">
            Tình trạng *
            <select
              className={fieldClass}
              value={form.condition}
              onChange={(event) =>
                setForm((current) => ({
                  ...current,
                  condition: event.target.value as ProductCondition,
                }))
              }
            >
              <option value="new">Mới</option>
              <option value="like_new">Như mới</option>
              <option value="used">Đã sử dụng</option>
            </select>
          </label>
        </div>

        <label className="block text-sm font-bold text-fg">
          Hình thức *
          <select
            className={fieldClass}
            value={form.type}
            onChange={(event) =>
              setForm((current) => ({
                ...current,
                type: event.target.value as ProductType,
              }))
            }
          >
            <option value="sale">Mua bán</option>
            <option value="exchange">Trao đổi</option>
            <option value="auction">Đấu giá ngược</option>
          </select>
        </label>

        {form.type === "sale" && (
          <label className="block text-sm font-bold text-fg">
            Giá bán (VNĐ) *
            <input
              required
              type="number"
              min={1000}
              step={1000}
              className={fieldClass}
              value={form.price}
              onChange={(event) =>
                setForm((current) => ({ ...current, price: event.target.value }))
              }
            />
          </label>
        )}

        {form.type === "auction" && (
          <div className="grid gap-4 sm:grid-cols-2">
            {(
              [
                ["startPrice", "Giá khởi điểm (VNĐ) *"],
                ["floorPrice", "Giá sàn (VNĐ) *"],
                ["decreaseAmount", "Mức giảm mỗi lượt (VNĐ) *"],
                ["stepMinutes", "Khoảng thời gian mỗi lượt (phút) *"],
              ] as const
            ).map(([key, label]) => (
              <label key={key} className="block text-sm font-bold text-fg">
                {label}
                <input
                  required
                  type="number"
                  min={key === "stepMinutes" ? 1 : 1000}
                  step={key === "stepMinutes" ? 1 : 1000}
                  className={fieldClass}
                  value={form[key]}
                  onChange={(event) =>
                    setForm((current) => ({
                      ...current,
                      [key]: event.target.value,
                    }))
                  }
                />
              </label>
            ))}
          </div>
        )}

        {form.type === "exchange" && (
          <p className="rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-200">
            Với tin trao đổi, hãy ghi rõ món đồ bạn muốn đổi trong phần mô tả.
            Thỏa thuận sẽ được thực hiện qua tin nhắn với người bán.
          </p>
        )}

        <label className="block text-sm font-bold text-fg">
          Ảnh sản phẩm
          <input
            type="file"
            accept="image/jpeg,image/png,image/webp,image/gif"
            className={`${fieldClass} file:mr-3 file:rounded-lg file:border-0 file:bg-primary/10 file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-primary`}
            onChange={(event) => {
              const file = event.target.files?.[0];
              if (!file) {
                setImage(undefined);
                return;
              }
              if (file.size > 3 * 1024 * 1024) {
                setError("Ảnh sản phẩm không được vượt quá 3MB.");
                event.target.value = "";
                setImage(undefined);
                return;
              }
              setError("");
              setImage(file);
            }}
          />
          {image && (
            <span className="mt-1 block text-xs font-normal text-muted">
              {image.name}
            </span>
          )}
        </label>

        <div className="rounded-xl bg-surface-2 px-4 py-3 text-xs leading-5 text-muted">
          Tin đăng mới có thể cần được duyệt trước khi hiển thị công khai. Ảnh
          được giới hạn tối đa 3MB.
        </div>

        <div className="flex justify-end">
          <Button type="submit" disabled={saving || loadingCategories}>
            {saving ? (
              <LoaderCircle className="h-4 w-4 animate-spin" />
            ) : (
              <PackagePlus className="h-4 w-4" />
            )}
            {saving ? "Đang đăng..." : "Đăng tin"}
          </Button>
        </div>
      </form>
    </UserFlowLayout>
  );
}

export default function CreateListingPage() {
  return (
    <UserFlowApp>
      <CreateListingContent />
    </UserFlowApp>
  );
}
