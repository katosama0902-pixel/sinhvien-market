"use client";

import { Suspense, useEffect, useState } from "react";
import Link from "next/link";
import { useSearchParams } from "next/navigation";
import {
  Clock,
  Filter,
  RotateCcw,
  Search,
  ShieldCheck,
  ShoppingBag,
  Tag,
  User,
  Zap,
} from "lucide-react";
import { MainShell } from "@/components/layout/MainShell";
import {
  getMarketplaceCategories,
  getMarketplaceProducts,
} from "@/lib/api/marketplace";
import type { MarketplaceProduct } from "@/lib/api/marketplace";
import type {
  AdminCategory,
  ProductCondition,
  ProductType,
} from "@/lib/types/admin";
import { cn } from "@/lib/cn";
import { formatDateVi, formatVnd } from "@/lib/validation/admin";

const CONDITION_MAP: Record<
  ProductCondition,
  { label: string; badgeClass: string }
> = {
  new: {
    label: "🟢 Mới 100%",
    badgeClass:
      "bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300",
  },
  like_new: {
    label: "🔵 Như mới (90%+)",
    badgeClass:
      "bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300",
  },
  used: {
    label: "🟡 Đã qua sử dụng",
    badgeClass:
      "bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300",
  },
};

function ProductsCatalogInner() {
  const searchParams = useSearchParams();
  const initialQ = searchParams.get("q") ?? "";
  const initialCat = Number(searchParams.get("category") ?? "0") || 0;
  const initialType = (searchParams.get("type") ?? "") as "" | ProductType;

  const [loading, setLoading] = useState(true);
  const [products, setProducts] = useState<MarketplaceProduct[]>([]);
  const [categories, setCategories] = useState<AdminCategory[]>([]);
  const [loadError, setLoadError] = useState("");

  const [keyword, setKeyword] = useState(initialQ);
  const [categoryId, setCategoryId] = useState<number>(initialCat);
  const [typeFilter, setTypeFilter] = useState<"" | ProductType>(initialType);
  const [conditionFilter, setConditionFilter] = useState<"" | ProductCondition>(
    "",
  );
  const [priceMin, setPriceMin] = useState("");
  const [priceMax, setPriceMax] = useState("");

  useEffect(() => {
    let active = true;
    getMarketplaceCategories()
      .then((catList) => {
        if (!active) return;
        setCategories(catList);
      })
      .catch((error: unknown) => {
        if (!active) return;
        setLoadError(
          error instanceof Error
            ? error.message
            : "Không thể tải danh mục sản phẩm.",
        );
      });
    return () => {
      active = false;
    };
  }, []);

  useEffect(() => {
    let active = true;
    const timer = window.setTimeout(() => {
      setLoading(true);
      setLoadError("");
      getMarketplaceProducts({
        keyword,
        categoryId: categoryId || undefined,
        type: typeFilter || undefined,
        condition: conditionFilter || undefined,
        priceMin: priceMin ? Number(priceMin) : undefined,
        priceMax: priceMax ? Number(priceMax) : undefined,
      })
        .then((productList) => {
          if (active) setProducts(productList);
        })
        .catch((error: unknown) => {
          if (!active) return;
          setLoadError(
            error instanceof Error
              ? error.message
              : "Không thể tải danh sách sản phẩm.",
          );
        })
        .finally(() => {
          if (active) setLoading(false);
        });
    }, 200);

    return () => {
      active = false;
      window.clearTimeout(timer);
    };
  }, [
    keyword,
    categoryId,
    typeFilter,
    conditionFilter,
    priceMin,
    priceMax,
  ]);

  const filteredProducts = products;

  const hasFilter =
    Boolean(keyword.trim()) ||
    categoryId > 0 ||
    Boolean(typeFilter) ||
    Boolean(conditionFilter) ||
    Boolean(priceMin) ||
    Boolean(priceMax);

  const handleResetFilters = () => {
    setKeyword("");
    setCategoryId(0);
    setTypeFilter("");
    setConditionFilter("");
    setPriceMin("");
    setPriceMax("");
  };

  return (
    <div className="mx-auto max-w-7xl px-4 py-8">
      <div className="flex flex-col gap-6 lg:flex-row">
        <aside className="shrink-0 lg:w-1/4">
          <div className="sticky top-24 rounded-2xl border-2 border-border bg-surface p-5 shadow-xs">
            <h2 className="mb-4 flex items-center gap-2 text-base font-extrabold text-fg">
              <Filter className="h-4 w-4 text-primary" />
              Lọc sản phẩm
            </h2>

            <div className="space-y-4">
              <div>
                <label
                  htmlFor="filter-q"
                  className="mb-1.5 block text-xs font-bold tracking-wide text-muted uppercase"
                >
                  Từ khóa
                </label>
                <div className="relative">
                  <Search className="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted" />
                  <input
                    id="filter-q"
                    type="text"
                    value={keyword}
                    onChange={(e) => setKeyword(e.target.value)}
                    placeholder="Tìm sách, bàn phím, đồ KTX..."
                    className="w-full rounded-xl border border-border bg-bg py-2 pr-3 pl-9 text-sm text-fg outline-none focus:border-primary"
                  />
                </div>
              </div>

              <div>
                <label
                  htmlFor="filter-cat"
                  className="mb-1.5 block text-xs font-bold tracking-wide text-muted uppercase"
                >
                  Danh mục
                </label>
                <select
                  id="filter-cat"
                  value={categoryId}
                  onChange={(e) => setCategoryId(Number(e.target.value))}
                  className="w-full rounded-xl border border-border bg-bg px-3 py-2 text-sm text-fg outline-none focus:border-primary"
                >
                  <option value={0}>-- Tất cả danh mục --</option>
                  {categories.map((cat) => (
                    <option key={cat.id} value={cat.id}>
                      {cat.name}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label
                  htmlFor="filter-type"
                  className="mb-1.5 block text-xs font-bold tracking-wide text-muted uppercase"
                >
                  Hình thức giao dịch
                </label>
                <select
                  id="filter-type"
                  value={typeFilter}
                  onChange={(e) =>
                    setTypeFilter(e.target.value as "" | ProductType)
                  }
                  className="w-full rounded-xl border border-border bg-bg px-3 py-2 text-sm text-fg outline-none focus:border-primary"
                >
                  <option value="">Tất cả hình thức</option>
                  <option value="sale">💰 Mua bán trực tiếp</option>
                  <option value="auction">⚡ Đấu giá ngược</option>
                  <option value="exchange">🔄 Trao đổi đồ</option>
                </select>
              </div>

              <div>
                <span className="mb-1.5 block text-xs font-bold tracking-wide text-muted uppercase">
                  Tình trạng
                </span>
                <div className="space-y-1.5 text-sm">
                  {(
                    [
                      { value: "", label: "⚪ Tất cả" },
                      { value: "new", label: "🟢 Mới 100%" },
                      { value: "like_new", label: "🔵 Như mới (90%+)" },
                      { value: "used", label: "🟡 Đã qua sử dụng" },
                    ] as const
                  ).map((opt) => (
                    <label
                      key={opt.value || "all"}
                      className="flex cursor-pointer items-center gap-2 text-fg hover:text-primary"
                    >
                      <input
                        type="radio"
                        name="condition"
                        checked={conditionFilter === opt.value}
                        onChange={() => setConditionFilter(opt.value)}
                        className="h-4 w-4 accent-indigo-600"
                      />
                      <span>{opt.label}</span>
                    </label>
                  ))}
                </div>
              </div>

              <div>
                <span className="mb-1.5 block text-xs font-bold tracking-wide text-muted uppercase">
                  Khoảng giá (VNĐ)
                </span>
                <div className="grid grid-cols-2 gap-2">
                  <input
                    type="number"
                    min={0}
                    step={10000}
                    value={priceMin}
                    onChange={(e) => setPriceMin(e.target.value)}
                    placeholder="Từ"
                    className="w-full rounded-xl border border-border bg-bg px-3 py-2 text-sm text-fg outline-none focus:border-primary"
                  />
                  <input
                    type="number"
                    min={0}
                    step={10000}
                    value={priceMax}
                    onChange={(e) => setPriceMax(e.target.value)}
                    placeholder="Đến"
                    className="w-full rounded-xl border border-border bg-bg px-3 py-2 text-sm text-fg outline-none focus:border-primary"
                  />
                </div>
              </div>

              {hasFilter && (
                <button
                  type="button"
                  onClick={handleResetFilters}
                  className="inline-flex w-full cursor-pointer items-center justify-center gap-1.5 rounded-xl border-2 border-border py-2 text-xs font-bold text-muted transition hover:bg-surface-2 hover:text-fg"
                >
                  <RotateCcw className="h-3.5 w-3.5" />
                  Xóa bộ lọc
                </button>
              )}
            </div>
          </div>
        </aside>

        <div className=" lg:w-3/4">
          <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
              <h1 className="text-xl font-extrabold text-fg sm:text-2xl">
                {keyword.trim() ? (
                  <>
                    Kết quả tìm kiếm:{" "}
                    <span className="text-primary">
                      &ldquo;{keyword.trim()}&rdquo;
                    </span>
                  </>
                ) : categoryId > 0 ? (
                  categories.find((c) => c.id === categoryId)?.name ??
                  "Danh sách sản phẩm"
                ) : (
                  "Tất cả sản phẩm đang bán"
                )}
              </h1>
              <p className="mt-0.5 text-xs text-muted">
                Hiển thị {filteredProducts.length} sản phẩm đã được Admin kiểm duyệt
              </p>
            </div>
          </div>

          {loadError && (
            <div
              role="alert"
              className="mb-5 rounded-xl border border-rose-300 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-300"
            >
              {loadError}
            </div>
          )}

          {loading ? (
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
              {[1, 2, 3, 4, 5, 6].map((i) => (
                <div
                  key={i}
                  className="h-72 animate-pulse rounded-2xl border border-border bg-surface"
                />
              ))}
            </div>
          ) : filteredProducts.length === 0 ? (
            <div className="rounded-2xl border border-border bg-surface py-16 text-center">
              <ShoppingBag className="mx-auto mb-3 h-12 w-12 text-muted/40" />
              <h3 className="text-base font-bold text-fg">
                Không tìm thấy sản phẩm nào phù hợp
              </h3>
              <p className="mt-1 text-xs text-muted">
                Hãy thử thay đổi từ khóa hoặc nhấn nút Xóa bộ lọc ở cột bên trái.
              </p>
            </div>
          ) : (
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
              {filteredProducts.map((p) => {
                const cond = CONDITION_MAP[p.condition];
                return (
                  <div
                    key={p.id}
                    className="group flex flex-col overflow-hidden rounded-2xl border border-border bg-surface transition duration-300 hover:-translate-y-1 hover:border-primary/40 hover:shadow-lg"
                  >
                    <div className="relative flex h-44 items-center justify-center bg-surface-2">
                      {p.type === "auction" ? (
                        <Zap className="h-12 w-12 text-rose-500/35 transition group-hover:scale-110" />
                      ) : (
                        <ShoppingBag className="h-12 w-12 text-muted/35 transition group-hover:scale-110" />
                      )}

                      <div className="absolute top-2.5 left-2.5">
                        {p.type === "auction" ? (
                          <span className="inline-flex items-center gap-1 rounded-lg bg-linear-to-r from-rose-500 to-orange-500 px-2.5 py-1 text-[10px] font-black tracking-wider text-white uppercase shadow-md">
                            ⚡ Đấu giá
                          </span>
                        ) : p.type === "exchange" ? (
                          <span className="inline-flex items-center gap-1 rounded-lg bg-cyan-500 px-2.5 py-1 text-[10px] font-black tracking-wider text-white uppercase shadow-md">
                            🔄 Trao đổi
                          </span>
                        ) : (
                          <span className="inline-flex items-center gap-1 rounded-lg bg-indigo-600 px-2.5 py-1 text-[10px] font-black tracking-wider text-white uppercase shadow-md">
                            💰 Mua bán
                          </span>
                        )}
                      </div>

                      {cond && (
                        <div className="absolute top-2.5 right-2.5">
                          <span
                            className={cn(
                              "inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold shadow-xs",
                              cond.badgeClass,
                            )}
                          >
                            {cond.label}
                          </span>
                        </div>
                      )}
                    </div>

                    <div className="flex flex-1 flex-col p-4">
                      <p className="mb-1.5 flex items-center gap-1 text-xs text-muted">
                        <Tag className="h-3 w-3 text-primary" />
                        {p.categoryName}
                      </p>

                      <Link
                        href={`/products/${p.id}`}
                        className="mb-3 line-clamp-2 flex-1 text-sm font-bold text-fg transition group-hover:text-primary"
                      >
                        {p.title}
                      </Link>

                      <div className="mb-3 flex items-end justify-between">
                        {p.type === "auction" ? (
                          <div>
                            <span className="font-mono text-lg font-black text-rose-500">
                              {formatVnd(p.price)}
                            </span>
                            <span className="block text-[10px] text-rose-500">
                              ↓ Giá đang giảm dần
                            </span>
                          </div>
                        ) : p.type === "sale" ? (
                          <span className="font-mono text-lg font-extrabold text-primary">
                            {formatVnd(p.price)}
                          </span>
                        ) : (
                          <span className="rounded-lg bg-cyan-500/15 px-2.5 py-1 text-xs font-bold text-cyan-600 dark:text-cyan-300">
                            Nhận trao đổi đồ
                          </span>
                        )}
                      </div>

                      <div className="mt-auto flex items-center justify-between border-t border-border pt-3 text-xs text-muted">
                        <span className="inline-flex items-center gap-1 truncate pr-2">
                          <User className="h-3.5 w-3.5 shrink-0" />
                          <Link
                            href={`/users/${p.sellerId}`}
                            className="truncate font-medium text-fg transition hover:text-primary"
                          >
                            {p.sellerName}
                          </Link>
                          <ShieldCheck
                            className="h-3.5 w-3.5 shrink-0 text-emerald-500"
                            aria-label="Đã xác thực"
                          />
                        </span>
                        <span className="inline-flex shrink-0 items-center gap-1">
                          <Clock className="h-3 w-3" />
                          {formatDateVi(p.createdAt)}
                        </span>
                      </div>
                    </div>
                  </div>
                );
              })}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

export default function ProductsPage() {
  return (
    <MainShell>
      <Suspense
        fallback={
          <div className="mx-auto max-w-7xl px-4 py-8">
            <div className="h-96 animate-pulse rounded-2xl bg-surface" />
          </div>
        }
      >
        <ProductsCatalogInner />
      </Suspense>
    </MainShell>
  );
}
