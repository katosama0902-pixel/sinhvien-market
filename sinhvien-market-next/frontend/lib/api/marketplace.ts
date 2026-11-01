import { USE_MOCK } from "@/lib/config";
import {
  getAdminBanners,
  getAdminCategories,
  getAdminGiveaways,
  getAdminProducts,
  getAdminRatings,
  getAdminUsers,
  getDashboardStats,
} from "./admin";
import { getCurrentUser } from "./auth";
import { apiRequest } from "./client";
import type {
  AdminBanner,
  AdminCategory,
  AdminGiveaway,
  AdminProduct,
  DashboardStats,
} from "@/lib/types/admin";

export type MarketplaceProduct = Omit<AdminProduct, "sellerEmail"> & {
  imageUrl: string | null;
  sellerVerified: boolean;
  currentPrice: number | null;
  isAtFloor: boolean | null;
};

export type MarketplaceBanner = Omit<AdminBanner, "imageUrl"> & {
  imageUrl: string | null;
};

export type MarketplaceGiveaway = Omit<AdminGiveaway, "participants"> & {
  participantCount: number;
  joinedByCurrentUser: boolean;
};

export interface MarketplaceHomeData {
  banners: MarketplaceBanner[];
  categories: AdminCategory[];
  featuredProducts: MarketplaceProduct[];
  auctionProducts: MarketplaceProduct[];
  giveaway: MarketplaceGiveaway | null;
  stats: Pick<DashboardStats, "activeProducts" | "totalUsers" | "txToday">;
}

export interface LeaderboardSeller {
  id: number;
  name: string;
  avatarUrl: string | null;
  isStudentVerified: boolean;
  productCount: number;
  soldCount: number;
  avgRating: number;
  ratingCount: number;
  score: number;
}

export interface LeaderboardData {
  topSellers: LeaderboardSeller[];
  myRank: number;
  myStats: LeaderboardSeller | null;
}

export interface MarketplaceProductFilters {
  keyword?: string;
  categoryId?: number;
  type?: AdminProduct["type"];
  condition?: AdminProduct["condition"];
  priceMin?: number;
  priceMax?: number;
}

export interface MarketplaceProductDetail extends MarketplaceProduct {
  description: string;
}

function toMarketplaceProduct(product: AdminProduct): MarketplaceProduct {
  return {
    id: product.id,
    title: product.title,
    categoryId: product.categoryId,
    categoryName: product.categoryName,
    sellerId: product.sellerId,
    sellerName: product.sellerName,
    type: product.type,
    price: product.price,
    condition: product.condition,
    status: product.status,
    hasTransactions: product.hasTransactions,
    rejectionReason: product.rejectionReason,
    createdAt: product.createdAt,
    imageUrl: null,
    sellerVerified: false,
    currentPrice:
      product.type === "auction" && product.status === "active"
        ? product.price
        : null,
    isAtFloor: null,
  };
}

export async function getMarketplaceHome(): Promise<MarketplaceHomeData> {
  if (USE_MOCK) {
    const [banners, categories, allProducts, giveaways, stats, user] =
      await Promise.all([
        getAdminBanners(),
        getAdminCategories(),
        getAdminProducts("all"),
        getAdminGiveaways(),
        getDashboardStats(),
        getCurrentUser(),
      ]);
    const activeProducts = allProducts.filter(
      (product) => product.status === "active",
    );
    const activeGiveaway =
      giveaways.find((giveaway) => giveaway.status === "active") ??
      giveaways[0] ??
      null;

    return {
      banners: banners
        .filter((banner) => banner.isActive)
        .map((banner) => ({ ...banner })),
      categories,
      featuredProducts: activeProducts.slice(0, 6).map(toMarketplaceProduct),
      auctionProducts: activeProducts
        .filter((product) => product.type === "auction")
        .slice(0, 3)
        .map(toMarketplaceProduct),
      giveaway: activeGiveaway
        ? {
            id: activeGiveaway.id,
            title: activeGiveaway.title,
            description: activeGiveaway.description,
            imageUrl: activeGiveaway.imageUrl,
            endTime: activeGiveaway.endTime,
            status: activeGiveaway.status,
            winnerId: activeGiveaway.winnerId,
            winnerName: activeGiveaway.winnerName,
            createdAt: activeGiveaway.createdAt,
            participantCount: activeGiveaway.participants.length,
            joinedByCurrentUser: Boolean(
              user &&
                activeGiveaway.participants.some(
                  (participant) =>
                    participant.email.toLowerCase() ===
                    user.email.toLowerCase(),
                ),
            ),
          }
        : null,
      stats,
    };
  }
  return apiRequest<MarketplaceHomeData>("/marketplace/home");
}

export async function getMarketplaceLeaderboard(): Promise<LeaderboardData> {
  if (!USE_MOCK) {
    return apiRequest<LeaderboardData>("/marketplace/leaderboard");
  }

  const [users, products, ratings, currentUser] = await Promise.all([
    getAdminUsers(),
    getAdminProducts("all"),
    getAdminRatings(),
    getCurrentUser(),
  ]);
  const sellers = users
    .filter((user) => user.role === "student" && !user.isLocked)
    .map((user): LeaderboardSeller => {
      const ownProducts = products.filter(
        (product) => product.sellerId === user.id,
      );
      const ownRatings = ratings.filter((rating) => rating.rateeId === user.id);
      const avgRating =
        ownRatings.length > 0
          ? ownRatings.reduce((sum, rating) => sum + rating.stars, 0) /
            ownRatings.length
          : 0;
      const soldCount = ownProducts.filter(
        (product) => product.status === "sold",
      ).length;
      const score = soldCount * 5 + avgRating * 10 + ownProducts.length;
      return {
        id: user.id,
        name: user.name,
        avatarUrl: null,
        isStudentVerified: user.isVerified,
        productCount: ownProducts.length,
        soldCount,
        avgRating: Math.round(avgRating * 10) / 10,
        ratingCount: ownRatings.length,
        score,
      };
    })
    .sort(
      (a, b) =>
        b.score - a.score ||
        b.soldCount - a.soldCount ||
        b.avgRating - a.avgRating,
    );
  const myStats =
    sellers.find((seller) => seller.id === currentUser?.id) ?? null;
  const myRank =
    myStats === null
      ? 0
      : sellers.filter(
          (seller) =>
            seller.score > myStats.score ||
            (seller.score === myStats.score &&
              seller.soldCount > myStats.soldCount) ||
            (seller.score === myStats.score &&
              seller.soldCount === myStats.soldCount &&
              seller.avgRating > myStats.avgRating),
        ).length + 1;
  return { topSellers: sellers.slice(0, 10), myRank, myStats };
}

export async function getMarketplaceCategories(): Promise<AdminCategory[]> {
  if (USE_MOCK) return getAdminCategories();
  return apiRequest<AdminCategory[]>("/marketplace/categories");
}

export async function getMarketplaceProducts(
  filters: MarketplaceProductFilters = {},
): Promise<MarketplaceProduct[]> {
  if (USE_MOCK) {
    const products = (await getAdminProducts("all")).filter(
      (product) => product.status === "active",
    );
    const keyword = filters.keyword?.trim().toLocaleLowerCase("vi");

    return products
      .filter((product) => {
        if (filters.categoryId && product.categoryId !== filters.categoryId) {
          return false;
        }
        if (filters.type && product.type !== filters.type) return false;
        if (filters.condition && product.condition !== filters.condition) {
          return false;
        }
        if (
          filters.priceMin !== undefined &&
          (product.price ?? 0) < filters.priceMin
        ) {
          return false;
        }
        if (
          filters.priceMax !== undefined &&
          (product.price ?? 0) > filters.priceMax
        ) {
          return false;
        }
        if (!keyword) return true;
        return (
          product.title.toLocaleLowerCase("vi").includes(keyword) ||
          product.categoryName.toLocaleLowerCase("vi").includes(keyword) ||
          product.sellerName.toLocaleLowerCase("vi").includes(keyword)
        );
      })
      .map(toMarketplaceProduct);
  }

  const search = new URLSearchParams();
  search.set("limit", "50");
  if (filters.keyword?.trim()) search.set("q", filters.keyword.trim());
  if (filters.categoryId) search.set("category", String(filters.categoryId));
  if (filters.type) search.set("type", filters.type);
  if (filters.condition) search.set("condition", filters.condition);
  if (filters.priceMin !== undefined) {
    search.set("priceMin", String(filters.priceMin));
  }
  if (filters.priceMax !== undefined) {
    search.set("priceMax", String(filters.priceMax));
  }

  const query = search.size > 0 ? `?${search.toString()}` : "";
  return apiRequest<MarketplaceProduct[]>(`/marketplace/products${query}`);
}

export async function getMarketplaceProduct(
  productId: number,
): Promise<MarketplaceProductDetail> {
  if (USE_MOCK) {
    const products = await getAdminProducts("all");
    const product = products.find((item) => item.id === productId);
    if (!product || product.status !== "active") {
      throw new Error("Sản phẩm không tồn tại hoặc đã ngừng bán.");
    }
    return {
      ...toMarketplaceProduct(product),
      description: "Thông tin chi tiết sản phẩm demo.",
    };
  }

  return apiRequest<MarketplaceProductDetail>(
    `/marketplace/product?id=${encodeURIComponent(productId)}`,
  );
}
