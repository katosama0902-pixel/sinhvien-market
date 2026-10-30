export type ProductType = "sale" | "exchange" | "auction";

export type ProductStatus = "pending" | "active" | "sold" | "cancelled";

export type ProductCondition = "new" | "like_new" | "used";

export interface AdminProduct {
  id: number;
  title: string;
  categoryId: number;
  categoryName: string;
  sellerId: number;
  sellerName: string;
  sellerEmail: string;
  type: ProductType;
  price: number | null;
  condition: ProductCondition;
  status: ProductStatus;
  hasTransactions?: boolean;
  rejectionReason?: string | null;
  createdAt: string;
}

export type CategoryIconKey =
  | "bi-book"
  | "bi-laptop"
  | "bi-bicycle"
  | "bi-backpack"
  | "bi-house"
  | "bi-music-note"
  | "bi-brush"
  | "bi-controller"
  | "bi-bag"
  | "bi-box-seam";

export interface AdminCategory {
  id: number;
  name: string;
  slug: string;
  icon: CategoryIconKey;
  createdAt: string;
}

export type LockDuration =
  | "3days"
  | "1week"
  | "2weeks"
  | "1month"
  | "3months"
  | "6months"
  | "forever";

export interface AdminUserRecord {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  role: "student" | "admin";
  isLocked: boolean;
  lockReason: string | null;
  lockedAt: string | null;
  lockedUntil: string | null;
  isVerified: boolean;
  lastVerifiedAt: string | null;
  securityQuestion: string | null;
  strikeCount: number;
  createdAt: string;
}

export interface ViolationRecord {
  id: number;
  userId: number;
  adminId: number;
  adminName: string;
  strikeNumber: number;
  reason: string;
  evidenceUrl: string | null;
  createdAt: string;
}

export type PaymentMethod = "cod" | "banking" | "zalopay";

export type OrderStatus =
  | "pending"
  | "delivered"
  | "received"
  | "completed"
  | "cancelled";

export interface AdminTransaction {
  id: number;
  productId: number;
  productTitle: string;
  buyerId: number;
  buyerName: string;
  sellerId: number;
  sellerName: string;
  type: "sale" | "auction";
  amount: number;
  paymentMethod: PaymentMethod;
  orderStatus: OrderStatus;
  createdAt: string;
}

export type ReportStatus = "pending" | "resolved" | "ignored";

export interface SystemReport {
  id: number;
  reporterId: number;
  reporterName: string;
  targetUserId: number | null;
  targetName: string | null;
  productId: number | null;
  productTitle: string | null;
  reason: string;
  description: string;
  status: ReportStatus;
  adminNote: string | null;
  evidenceUrl: string | null;
  createdAt: string;
}

export interface AdminUserDetail {
  profile: AdminUserRecord;
  products: AdminProduct[];
  transactions: AdminTransaction[];
  reports: SystemReport[];
  ratingStats: {
    avg: number;
    count: number;
  };
}

export interface AdminRating {
  id: number;
  transactionId: number;
  raterId: number;
  raterName: string;
  raterEmail: string;
  rateeId: number;
  rateeName: string;
  rateeEmail: string;
  productId: number;
  productTitle: string;
  stars: number;
  comment: string | null;
  status: "visible" | "hidden";
  createdAt: string;
}

export interface AdminBanner {
  id: number;
  title: string;
  imageUrl: string;
  link: string;
  isActive: boolean;
  displayOrder: number;
  createdAt: string;
}

export interface GiveawayParticipant {
  id: number;
  name: string;
  email: string;
  joinedAt: string;
}

export interface AdminGiveaway {
  id: number;
  title: string;
  description: string;
  imageUrl: string | null;
  endTime: string;
  status: "active" | "ended";
  winnerId: number | null;
  winnerName: string | null;
  participants: GiveawayParticipant[];
  createdAt: string;
}

export type AuditAction =
  | "approve_product"
  | "reject_product"
  | "delete_product"
  | "lock_user"
  | "unlock_user"
  | "strike_user"
  | "create_category"
  | "update_category"
  | "delete_category"
  | "resolve_report"
  | "ignore_report"
  | "toggle_rating"
  | "create_banner"
  | "update_banner"
  | "toggle_banner"
  | "delete_banner"
  | "create_giveaway"
  | "spin_giveaway"
  | "delete_giveaway";

export interface AuditLogEntry {
  id: number;
  adminId: number;
  adminName: string;
  adminEmail: string;
  action: AuditAction;
  targetType: string;
  targetId: number;
  note: string | null;
  createdAt: string;
}

export interface DashboardStats {
  totalUsers: number;
  activeProducts: number;
  pendingCount: number;
  txToday: number;
  recentTx: AdminTransaction[];
  recentProducts: AdminProduct[];
}

export type ExportType = "users" | "products" | "transactions";
