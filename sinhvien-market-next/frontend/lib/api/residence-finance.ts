import { apiRequest } from "./client";

export interface FeeCategory {
  id: number;
  name: string;
  description: string | null;
  isActive: boolean;
}
export interface InvoiceLine {
  id: number;
  feeCategoryId: number | null;
  categoryName: string;
  description: string;
  quantity: number;
  unitPrice: number;
  lineTotal: number;
}
export interface ResidenceInvoice {
  id: number;
  invoiceNumber: string;
  userId: number;
  studentName: string;
  contractId: number | null;
  description: string;
  issuedAt: string;
  dueDate: string;
  totalAmount: number;
  paidAmount: number;
  outstandingAmount: number;
  status: "open" | "partial" | "paid" | "void";
  lines: InvoiceLine[];
}
export interface ResidencePayment {
  id: number;
  invoiceId: number;
  invoiceNumber: string;
  studentName: string;
  amount: number;
  method: "cash" | "bank_transfer" | "other";
  reference: string | null;
  note: string | null;
  status: "pending" | "confirmed" | "rejected";
  createdAt: string;
}
export interface ResidenceNotice {
  id: number;
  type: "announcement" | "event" | "rule";
  title: string;
  body: string;
  audience: "all" | "residents";
  startsAt: string | null;
  endsAt: string | null;
  status: "published" | "archived";
  authorName: string;
  createdAt: string;
}

export const getFeeCategories = () => apiRequest<FeeCategory[]>("/residence/fee-categories");
export const getResidenceInvoices = () => apiRequest<ResidenceInvoice[]>("/residence/invoices");
export const getResidencePayments = () => apiRequest<ResidencePayment[]>("/residence/payments");
export const recordResidencePayment = (input: {
  invoiceId: number; amount: number; method: ResidencePayment["method"];
  reference?: string; note?: string;
}) => apiRequest<{ id: number }>("/residence/payments", { method: "POST", body: input });
export const getResidenceNotices = () => apiRequest<ResidenceNotice[]>("/residence/notices");
export const getAdminFeeCategories = () =>
  apiRequest<FeeCategory[]>("/residence/fee-categories");
export const getAdminInvoices = () =>
  apiRequest<ResidenceInvoice[]>("/residence/invoices");
export const getAdminResidencePayments = () =>
  apiRequest<ResidencePayment[]>("/residence/payments");
export const saveFeeCategory = (input: {
  name: string; description?: string; isActive?: boolean;
}, id?: number) => apiRequest<{ id: number }>(
  `/admin/residence/fee-categories${id ? `/${id}` : ""}`,
  { method: id ? "PUT" : "POST", body: input },
);
export const createResidenceInvoice = (input: {
  userId: number; contractId?: number; description: string; issuedAt: string;
  dueDate: string; lines: Array<{
    feeCategoryId: number; description: string; quantity: number; unitPrice: number;
  }>;
}) => apiRequest<{ id: number }>("/admin/residence/invoices", { method: "POST", body: input });
export const reviewResidencePayment = (id: number, decision: "confirmed" | "rejected") =>
  apiRequest<null>(`/admin/residence/payments/${id}/review`, { method: "POST", body: { decision } });
export const createResidenceNotice = (input: {
  type: ResidenceNotice["type"]; title: string; body: string;
  audience: ResidenceNotice["audience"]; startsAt?: string; endsAt?: string;
}) => apiRequest<{ id: number }>("/admin/residence/notices", { method: "POST", body: input });
export const archiveResidenceNotice = (id: number) =>
  apiRequest<{ id: number }>(`/admin/residence/notices/${id}/archive`, { method: "POST" });
