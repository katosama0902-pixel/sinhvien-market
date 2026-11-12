import { apiRequest } from "./client";

export interface ResidenceBuilding {
  id: number; code: string; name: string; address: string | null; status: string;
}
export interface ResidenceFloor {
  id: number; buildingId: number; buildingCode: string; buildingName: string;
  floorNumber: number; name: string | null;
}
export interface ResidenceRoom {
  id: number; floorId: number; floorNumber: number; buildingCode: string;
  buildingName: string; roomNumber: string; roomType: string; capacity: number;
  status: string; bedCount: number;
}
export interface ResidenceBed {
  id: number; roomId: number; roomNumber: string; floorNumber: number;
  buildingCode: string; buildingName: string; bedNumber: string; status: string;
}
export interface BedAllocation {
  contractId: number; contractNumber: string; userId: number;
  allocationId: number | null;
  studentName: string; startDate: string; endDate: string; bedId: number | null;
  bedNumber: string | null; roomNumber: string | null; buildingCode: string | null;
  floorNumber: number | null;
}
export interface ServiceRequest {
  id: number; userId: number; requestType: "service" | "repair" | "feedback";
  title: string; description: string; roomId: number | null; roomNumber: string | null;
  status: "submitted" | "assigned" | "in_progress" | "resolved" | "closed";
  priority: string; assignedTo: number | null; assignedName: string | null;
  staffNote: string | null; requesterName: string; requesterEmail: string;
  aiCategory: string | null; aiConfidence: number | null;
  aiRecommendedPriority: string | null; aiReason: string | null; aiModelKey: string | null;
}
export interface DormRulesAnswer {
  answer: string;
  sources: Array<{ id: number; title: string }>;
  modelKey: string;
}
export interface ResidenceOperationsDashboard {
  activeBuildings: number; activeRooms: number; totalBeds: number; occupiedBeds: number;
  openServiceRequests: number; pendingResidenceApplications: number;
  outstandingInvoices: number; pendingPaymentReviews: number;
}
export interface RentalSpace {
  id: number; spaceCode: string; name: string; location: string; areaSqm: number;
  monthlyPrice: number; description: string | null; status: string;
}
export interface RentalApplication {
  id: number; applicationNumber: string; spaceId: number; spaceCode: string;
  spaceName: string; applicantName: string; applicantEmail: string;
  startDate: string; endDate: string; businessName: string; note: string | null;
  status: string; decisionNote: string | null; contractNumber: string | null;
}
export interface RentalContract {
  id: number; contractNumber: string; spaceCode: string; spaceName: string;
  tenantName: string; startDate: string; endDate: string; monthlyPrice: number;
  status: string;
}

export async function getResidenceInventory() {
  return apiRequest<{
    buildings: ResidenceBuilding[]; floors: ResidenceFloor[];
    rooms: ResidenceRoom[]; beds: ResidenceBed[];
  }>("/admin/residence/inventory");
}

type InventoryResource = "buildings" | "floors" | "rooms" | "beds";
export async function saveInventoryResource(
  resource: InventoryResource,
  input: Record<string, unknown>,
  id?: number,
) {
  const path = `/admin/residence/${resource}${id ? `/${id}` : ""}`;
  return apiRequest<{ id: number }>(path, {
    method: id ? "PUT" : "POST",
    body: input,
  });
}
export async function deleteInventoryResource(resource: InventoryResource, id: number) {
  return apiRequest<{ id: number }>(`/admin/residence/${resource}/${id}`, { method: "DELETE" });
}
export async function getBedAllocations() {
  return apiRequest<BedAllocation[]>("/admin/residence/allocations");
}
export async function getAvailableBeds() {
  return apiRequest<Array<{ id: number; bedNumber: string; roomNumber: string; buildingCode: string; floorNumber: number }>>("/admin/residence/available-beds");
}
export async function assignResidenceBed(contractId: number, bedId: number, note = "") {
  return apiRequest<{ id: number }>("/admin/residence/allocations", {
    method: "POST", body: { contractId, bedId, note },
  });
}
export async function transferResidenceBed(
  allocationId: number, bedId: number, effectiveDate: string, note = "",
) {
  return apiRequest<{ id: number }>(`/admin/residence/allocations/${allocationId}/transfer`, {
    method: "POST", body: { bedId, effectiveDate, note },
  });
}
export async function getAccommodationHistory() {
  return apiRequest<Array<{
    allocationId: number; startDate: string; endDate: string;
    allocationStatus: string; buildingCode: string; floorNumber: number;
    roomNumber: string; bedNumber: string; eventType: string | null;
    eventDate: string | null; eventNote: string | null;
  }>>("/residence/accommodation/history");
}
export async function getServiceRequests() {
  return apiRequest<ServiceRequest[]>("/residence/service-requests");
}
export async function createServiceRequest(input: {
  requestType: ServiceRequest["requestType"]; title: string; description: string;
  roomId?: number; priority?: string;
}) {
  return apiRequest<{ id: number; classification: {
    category: string; confidence: number; recommendedPriority: string; reason: string; modelKey: string;
  } | null }>("/residence/service-requests", { method: "POST", body: input });
}
export async function askDormRulesAssistant(question: string) {
  return apiRequest<DormRulesAnswer>("/residence/assistant/rules", {
    method: "POST", body: { question },
  });
}
export async function getResidenceOperationsDashboard() {
  return apiRequest<ResidenceOperationsDashboard>("/admin/residence/dashboard");
}
export async function updateServiceRequest(
  id: number, input: { assignedTo?: number; status?: ServiceRequest["status"]; staffNote?: string },
) {
  return apiRequest<{ id: number }>(`/admin/residence/service-requests/${id}`, {
    method: "PUT", body: input,
  });
}
export async function getRentalSpaces() {
  return apiRequest<RentalSpace[]>("/rental/spaces");
}
export async function saveRentalSpace(input: Omit<RentalSpace, "id">, id?: number) {
  return apiRequest<{ id: number }>(`/admin/rental/spaces${id ? `/${id}` : ""}`, {
    method: id ? "PUT" : "POST", body: input,
  });
}
export async function deleteRentalSpace(id: number) {
  return apiRequest<{ id: number }>(`/admin/rental/spaces/${id}`, { method: "DELETE" });
}
export async function getRentalApplications() {
  return apiRequest<RentalApplication[]>("/rental/applications");
}
export async function submitRentalApplication(input: {
  spaceId: number; startDate: string; endDate: string; businessName: string; note?: string;
}) {
  return apiRequest<{ id: number }>("/rental/applications", { method: "POST", body: input });
}
export async function getRentalApplicationsForReview() {
  return apiRequest<RentalApplication[]>("/admin/rental/applications");
}
export async function reviewRentalApplication(
  id: number, decision: "under_review" | "approve" | "reject", note: string,
) {
  return apiRequest<RentalApplication>(`/admin/rental/applications/${id}/review`, {
    method: "POST", body: { decision, note },
  });
}
export async function getRentalContracts() {
  return apiRequest<RentalContract[]>("/rental/contracts");
}
