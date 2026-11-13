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

const MOCK_PRODUCT_IMAGES: Record<number, string> = {
  102: "/demo-matching/images/mech_keyboard_ref.jpg",
  103: "/demo-matching/images/desk_lamp_white.jpg",
  104: "/demo-matching/images/calc_casio.jpg",
  107: "/demo-matching/images/guitar_acoustic_ref.jpg",
  108: "/demo-matching/images/kettle_stainless.jpg",
};

const MOCK_PRODUCT_DESCRIPTIONS: Record<number, string> = {
  101: "Giáo trình Giải tích 1 & Đại số tuyến tính trọn bộ 2 cuốn, bìa cứng giữ kỹ, không rách trang, tặng kèm tập đề cương ôn thi cuối kỳ.",
  102: "Bàn phím cơ không dây Keychron K6 Red Switch gõ êm, kết nối Bluetooth 3 thiết bị, pin dùng 2 tuần, phù hợp gõ code ban đêm trong phòng KTX.",
  103: "Đèn bàn học chống cận Xiaomi Mi LED Desk Lamp ánh sáng dịu mắt, chỉnh được nhiệt độ màu, muốn giao lưu đổi lấy giá đỡ laptop hoặc sách chuyên ngành.",
  104: "Máy tính bỏ túi Casio fx-580VN X chính hãng Bitex còn nguyên tem chống giả, màn hình rõ nét, phím bấm nhạy.",
  105: "Tai nghe chụp tai Sony WH-CH520 Bluetooth 5.2 pin lên tới 50 giờ, đệm tai êm ái học tiếng Anh online, kèm cáp sạc USB-C.",
  106: "Xe đạp thể thao Giant Escape 3 khung nhôm siêu nhẹ, bộ đề Shimano 21 tốc độ chạy êm quanh Làng Đại học, tặng kèm khóa số và gọng bình nước.",
  107: "Đàn Guitar Acoustic Rosen G11 mặt gỗ thông nguyên tấm âm vang ấm, đã hạ action bấm êm tay cho người mới tập, tặng kèm bao da 3 lớp và capo.",
  108: "Ấm đun siêu tốc Sunhouse 1.8L ruột inox 304 tự ngắt khi sôi, công suất chuẩn quy định an toàn KTX.",
  109: "Bộ giáo trình Lập trình Hướng đối tượng C++ & Java in màu rõ đẹp, có ghi chú các dạng bài tập thực hành trọng tâm.",
};

function makeProductThumbSvg(product: AdminProduct): string {
  const palettes: Record<number, [string, string, string]> = {
    1: ["#312e81", "#4f46e5", "📚 Sách & Giáo trình"],
    2: ["#1e1b4b", "#7c3aed", "🎧 Đồ điện tử"],
    3: ["#134e4a", "#0d9488", "🏠 Đồ gia dụng KTX"],
    4: ["#0c4a6e", "#0284c7", "🚲 Phương tiện KTX"],
    5: ["#3b0764", "#9333ea", "📐 Đồ dùng học tập"],
    6: ["#701a75", "#db2777", "🎸 Nhạc cụ & Giải trí"],
  };
  const [fromColor, toColor, badge] = palettes[product.categoryId] ?? [
    "#1e1b4b",
    "#6366f1",
    "🛍️ SinhVienMarket",
  ];
  const shortTitle =
    product.title.length > 36
      ? `${product.title.slice(0, 36)}…`
      : product.title;
  const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="600" height="400" viewBox="0 0 600 400">
    <defs>
      <linearGradient id="pg" x1="0%" y1="0%" x2="100%" y2="100%">
        <stop offset="0%" stop-color="${fromColor}"/>
        <stop offset="100%" stop-color="${toColor}"/>
      </linearGradient>
    </defs>
    <rect width="600" height="400" fill="url(#pg)"/>
    <circle cx="510" cy="80" r="110" fill="rgba(255,255,255,0.1)"/>
    <circle cx="90" cy="340" r="95" fill="rgba(255,255,255,0.08)"/>
    <rect x="36" y="36" width="220" height="38" rx="19" fill="rgba(255,255,255,0.16)"/>
    <text x="54" y="61" fill="#ffffff" font-family="sans-serif" font-size="16" font-weight="700">${badge}</text>
    <text x="36" y="215" fill="#ffffff" font-family="sans-serif" font-size="25" font-weight="800">${shortTitle}</text>
    <text x="36" y="255" fill="rgba(255,255,255,0.8)" font-family="sans-serif" font-size="16" font-weight="600">Người bán: ${product.sellerName} • KTX ĐHQG</text>
  </svg>`;
  return `data:image/svg+xml;utf8,${encodeURIComponent(svg)}`;
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
    imageUrl: MOCK_PRODUCT_IMAGES[product.id] ?? makeProductThumbSvg(product),
    sellerVerified: product.sellerId !== 3,
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
      description:
        MOCK_PRODUCT_DESCRIPTIONS[product.id] ??
        "Sản phẩm sinh viên KTX còn sử dụng tốt, xem và nhận hàng trực tiếp tại sảnh tòa nhà.",
    };
  }

  return apiRequest<MarketplaceProductDetail>(
    `/marketplace/product?id=${encodeURIComponent(productId)}`,
  );
}
