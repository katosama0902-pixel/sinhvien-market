import { USE_MOCK } from "@/lib/config";
import { apiRequest } from "./client";
import type {
  AdminBanner,
  AdminCategory,
  AdminGiveaway,
  AdminProduct,
  AdminRating,
  AdminTransaction,
  AdminUserDetail,
  AdminUserRecord,
  AuditLogEntry,
  CategoryIconKey,
  DashboardStats,
  ExportType,
  LockDuration,
  ReportStatus,
  SystemReport,
  ViolationRecord,
} from "@/lib/types/admin";

const loadMock = () => import("./mock/admin");

export async function getDashboardStats(): Promise<DashboardStats> {
  if (USE_MOCK) return (await loadMock()).mockGetDashboardStats();
  return apiRequest<DashboardStats>("/admin/dashboard");
}

export async function getAdminProducts(
  tab: "pending" | "all",
): Promise<AdminProduct[]> {
  if (USE_MOCK) return (await loadMock()).mockGetProducts(tab);
  return apiRequest<AdminProduct[]>(`/admin/products?tab=${tab}`);
}

export async function getAdminTransactions(): Promise<AdminTransaction[]> {
  if (USE_MOCK) return (await loadMock()).mockGetTransactions();
  return apiRequest<AdminTransaction[]>("/admin/transactions");
}

export async function approveProduct(
  productId: number,
): Promise<{ message: string }> {
  if (USE_MOCK) return (await loadMock()).mockApproveProduct(productId);
  return apiRequest<{ message: string }>("/admin/products/approve", {
    method: "POST",
    body: { productId },
  });
}

export async function rejectProduct(
  productId: number,
  reason: string,
): Promise<{ message: string }> {
  if (USE_MOCK) return (await loadMock()).mockRejectProduct(productId, reason);
  return apiRequest<{ message: string }>("/admin/products/reject", {
    method: "POST",
    body: { productId, reason },
  });
}

export async function deleteProduct(
  productId: number,
): Promise<{ message: string }> {
  if (USE_MOCK) return (await loadMock()).mockDeleteProduct(productId);
  return apiRequest<{ message: string }>("/admin/products/delete", {
    method: "POST",
    body: { productId },
  });
}

export async function getAdminCategories(): Promise<AdminCategory[]> {
  if (USE_MOCK) return (await loadMock()).mockGetCategories();
  return apiRequest<AdminCategory[]>("/admin/categories");
}

export async function createCategory(input: {
  name: string;
  icon: CategoryIconKey;
}): Promise<{ category: AdminCategory; message: string }> {
  if (USE_MOCK) return (await loadMock()).mockCreateCategory(input);
  return apiRequest<{ category: AdminCategory; message: string }>(
    "/admin/categories",
    { method: "POST", body: input },
  );
}

export async function updateCategory(input: {
  id: number;
  name: string;
  icon: CategoryIconKey;
}): Promise<{ category: AdminCategory; message: string }> {
  if (USE_MOCK) return (await loadMock()).mockUpdateCategory(input);
  return apiRequest<{ category: AdminCategory; message: string }>(
    `/admin/categories/${input.id}`,
    { method: "PUT", body: input },
  );
}

export async function deleteCategory(
  categoryId: number,
): Promise<{ message: string }> {
  if (USE_MOCK) return (await loadMock()).mockDeleteCategory(categoryId);
  return apiRequest<{ message: string }>(`/admin/categories/${categoryId}`, {
    method: "DELETE",
  });
}

export async function getAdminUsers(): Promise<AdminUserRecord[]> {
  if (USE_MOCK) return (await loadMock()).mockGetUsers();
  return apiRequest<AdminUserRecord[]>("/admin/users");
}

export async function getAdminUserDetail(
  userId: number,
): Promise<AdminUserDetail> {
  if (USE_MOCK) return (await loadMock()).mockGetUserDetail(userId);
  return apiRequest<AdminUserDetail>(`/admin/users/${userId}`);
}

export async function getAdminUserViolations(userId: number): Promise<{
  user: AdminUserRecord;
  violations: ViolationRecord[];
}> {
  if (USE_MOCK) return (await loadMock()).mockGetUserViolations(userId);
  return apiRequest<{ user: AdminUserRecord; violations: ViolationRecord[] }>(
    `/admin/users/${userId}/violations`,
  );
}

export async function toggleUserLock(input: {
  userId: number;
  reason?: string;
  duration?: LockDuration;
}): Promise<{ user: AdminUserRecord; message: string }> {
  if (USE_MOCK) return (await loadMock()).mockToggleUserLock(input);
  return apiRequest<{ user: AdminUserRecord; message: string }>(
    `/admin/users/${input.userId}/toggle-lock`,
    { method: "POST", body: input },
  );
}

export async function strikeUser(input: {
  userId: number;
  reason: string;
  evidenceUrl?: string;
}): Promise<{
  user: AdminUserRecord;
  action: "warning" | "suspend_7_days" | "ban_permanent";
  message: string;
}> {
  if (USE_MOCK) return (await loadMock()).mockStrikeUser(input);
  return apiRequest<{
    user: AdminUserRecord;
    action: "warning" | "suspend_7_days" | "ban_permanent";
    message: string;
  }>(`/admin/users/${input.userId}/strike`, { method: "POST", body: input });
}

export async function getTransactionReports(
  fromDate: string,
  toDate: string,
): Promise<{
  transactions: AdminTransaction[];
  totalAmount: number;
  totalCount: number;
  avgAmount: number;
  byDay: { day: string; amount: number }[];
}> {
  if (USE_MOCK)
    return (await loadMock()).mockGetTransactionReports(fromDate, toDate);
  const qs = new URLSearchParams({ from: fromDate, to: toDate });
  return apiRequest(`/admin/reports?${qs.toString()}`);
}

export async function getSystemReports(
  status: "" | ReportStatus = "",
): Promise<{ reports: SystemReport[]; pendingCount: number }> {
  if (USE_MOCK) return (await loadMock()).mockGetSystemReports(status);
  const qs = status ? `?status=${encodeURIComponent(status)}` : "";
  return apiRequest(`/admin/system-reports${qs}`);
}

export async function resolveSystemReport(input: {
  id: number;
  status: "resolved" | "ignored";
  adminNote?: string;
  evidenceUrl?: string;
}): Promise<{ message: string }> {
  if (USE_MOCK) return (await loadMock()).mockResolveSystemReport(input);
  return apiRequest<{ message: string }>(
    `/admin/system-reports/${input.id}/resolve`,
    { method: "POST", body: input },
  );
}

export async function getAdminRatings(): Promise<AdminRating[]> {
  if (USE_MOCK) return (await loadMock()).mockGetRatings();
  return apiRequest<AdminRating[]>("/admin/ratings");
}

export async function toggleRatingVisibility(
  ratingId: number,
): Promise<{ rating: AdminRating; message: string }> {
  if (USE_MOCK) return (await loadMock()).mockToggleRating(ratingId);
  return apiRequest<{ rating: AdminRating; message: string }>(
    `/admin/ratings/${ratingId}/toggle`,
    { method: "POST" },
  );
}

export async function getAdminBanners(): Promise<AdminBanner[]> {
  if (USE_MOCK) return (await loadMock()).mockGetBanners();
  return apiRequest<AdminBanner[]>("/admin/banners");
}

export async function createBanner(input: {
  title: string;
  link: string;
  displayOrder: number;
  isActive: boolean;
  imageUrl?: string;
}): Promise<{ banner: AdminBanner; message: string }> {
  if (USE_MOCK) return (await loadMock()).mockCreateBanner(input);
  return apiRequest<{ banner: AdminBanner; message: string }>(
    "/admin/banners",
    { method: "POST", body: input },
  );
}

export async function updateBanner(input: {
  id: number;
  title: string;
  link: string;
  displayOrder: number;
}): Promise<{ banner: AdminBanner; message: string }> {
  if (USE_MOCK) return (await loadMock()).mockUpdateBanner(input);
  return apiRequest<{ banner: AdminBanner; message: string }>(
    `/admin/banners/${input.id}`,
    { method: "PUT", body: input },
  );
}

export async function toggleBanner(
  bannerId: number,
): Promise<{ banner: AdminBanner; message: string }> {
  if (USE_MOCK) return (await loadMock()).mockToggleBanner(bannerId);
  return apiRequest<{ banner: AdminBanner; message: string }>(
    `/admin/banners/${bannerId}/toggle`,
    { method: "POST" },
  );
}

export async function deleteBanner(
  bannerId: number,
): Promise<{ message: string }> {
  if (USE_MOCK) return (await loadMock()).mockDeleteBanner(bannerId);
  return apiRequest<{ message: string }>(`/admin/banners/${bannerId}`, {
    method: "DELETE",
  });
}

export async function getAdminGiveaways(): Promise<AdminGiveaway[]> {
  if (USE_MOCK) return (await loadMock()).mockGetGiveaways();
  return apiRequest<AdminGiveaway[]>("/admin/giveaways");
}

export async function getAdminGiveawayById(id: number): Promise<AdminGiveaway> {
  if (USE_MOCK) return (await loadMock()).mockGetGiveawayById(id);
  return apiRequest<AdminGiveaway>(`/admin/giveaways/${id}`);
}

export async function createGiveaway(input: {
  title: string;
  description: string;
  endTime: string;
  imageUrl?: string;
}): Promise<{ giveaway: AdminGiveaway; message: string }> {
  if (USE_MOCK) return (await loadMock()).mockCreateGiveaway(input);
  return apiRequest<{ giveaway: AdminGiveaway; message: string }>(
    "/admin/giveaways",
    { method: "POST", body: input },
  );
}

export async function deleteGiveaway(
  giveawayId: number,
): Promise<{ message: string }> {
  if (USE_MOCK) return (await loadMock()).mockDeleteGiveaway(giveawayId);
  return apiRequest<{ message: string }>(`/admin/giveaways/${giveawayId}`, {
    method: "DELETE",
  });
}

export async function saveGiveawayWinner(input: {
  id: number;
  winnerId: number;
}): Promise<{ message: string }> {
  if (USE_MOCK) return (await loadMock()).mockSaveGiveawayWinner(input);
  return apiRequest<{ message: string }>(`/admin/giveaways/${input.id}/spin`, {
    method: "POST",
    body: { winnerId: input.winnerId },
  });
}

export async function getAuditLogs(): Promise<AuditLogEntry[]> {
  if (USE_MOCK) return (await loadMock()).mockGetAuditLogs();
  return apiRequest<AuditLogEntry[]>("/admin/audit-log");
}

export async function exportAdminCsv(input: {
  type: ExportType;
  fromDate?: string;
  toDate?: string;
}): Promise<{ filename: string; content: string }> {
  if (USE_MOCK) return (await loadMock()).mockExportCsv(input);
  const qs = new URLSearchParams({
    type: input.type,
    ...(input.fromDate ? { from: input.fromDate } : {}),
    ...(input.toDate ? { to: input.toDate } : {}),
  });
  return apiRequest<{ filename: string; content: string }>(
    `/admin/export?${qs.toString()}`,
  );
}
