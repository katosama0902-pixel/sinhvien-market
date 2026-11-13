import { USE_MOCK } from "@/lib/config";
import { ApiError, type AuthUser } from "./types";
import { apiRequest } from "./client";
import { getOwnProfile } from "./profile";

export interface StudentDormProfile {
  userId: number;
  name: string;
  email: string;
  phone: string;
  studentNumber: string;
  university: string;
  faculty: string;
  program: string;
  academicYear: string;
  dateOfBirth: string | null;
  emergencyContactName: string;
  emergencyContactPhone: string;
}

export interface ResidenceApplication {
  id: number;
  contractId?: number | null;
  applicationNumber: string;
  studentName: string;
  studentEmail: string;
  studentNumber: string;
  university: string;
  startDate: string;
  endDate: string;
  preferredRoomType: "any" | "standard" | "private" | "accessible";
  note: string | null;
  status: "submitted" | "under_review" | "approved" | "rejected" | "withdrawn";
  decisionNote: string | null;
  createdAt: string;
  contractNumber: string | null;
  contractStatus: "pending_assignment" | "active" | "expired" | "terminated" | null;
}

export interface ResidenceContract {
  id: number;
  contractNumber: string;
  applicationNumber: string;
  studentName: string;
  studentNumber: string;
  startDate: string;
  endDate: string;
  status: "pending_assignment" | "active" | "expired" | "terminated";
}

export interface AssignableResidenceRole {
  key: "dorm_manager" | "service_staff" | "tenant";
  name: string;
}

interface MockResidenceStore {
  profiles: Record<string, StudentDormProfile>;
  applications: ResidenceApplication[];
}

const STORAGE_KEY = "svm_mock_residence_v1";

function readStore(): MockResidenceStore {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    const value = raw ? (JSON.parse(raw) as Partial<MockResidenceStore>) : {};
    return { profiles: value.profiles ?? {}, applications: value.applications ?? [] };
  } catch {
    throw new ApiError("UNKNOWN", "Không đọc được dữ liệu lưu trú xem thử.", {
      status: 500,
    });
  }
}

function writeStore(store: MockResidenceStore): void {
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(store));
  } catch {
    throw new ApiError("UNKNOWN", "Không lưu được dữ liệu lưu trú xem thử.", {
      status: 500,
    });
  }
}

export async function getStudentDormProfile(
  user: AuthUser,
): Promise<StudentDormProfile> {
  if (!USE_MOCK) return apiRequest<StudentDormProfile>("/student/profile");
  const store = readStore();
  if (store.profiles[String(user.id)]) return store.profiles[String(user.id)];
  const profile = await getOwnProfile(user);
  return {
    userId: user.id,
    name: profile.name,
    email: profile.email,
    phone: profile.phone,
    studentNumber: profile.studentId,
    university: profile.university,
    faculty: "",
    program: "",
    academicYear: "",
    dateOfBirth: null,
    emergencyContactName: "",
    emergencyContactPhone: "",
  };
}

export async function updateStudentDormProfile(
  user: AuthUser,
  input: Omit<StudentDormProfile, "userId" | "email">,
): Promise<StudentDormProfile> {
  if (!USE_MOCK) {
    return apiRequest<StudentDormProfile>("/student/profile", {
      method: "PUT",
      body: input,
    });
  }
  const profile: StudentDormProfile = {
    ...input,
    userId: user.id,
    email: user.email,
  };
  const store = readStore();
  store.profiles[String(user.id)] = profile;
  writeStore(store);
  return profile;
}

export async function getResidenceApplications(
  user?: AuthUser,
): Promise<ResidenceApplication[]> {
  if (!USE_MOCK) return apiRequest<ResidenceApplication[]>("/residence/applications");
  if (!user) return [];
  return readStore().applications.filter((application) =>
    application.studentEmail === user.email,
  );
}

export async function submitResidenceApplication(
  input: Pick<
    ResidenceApplication,
    "startDate" | "endDate" | "preferredRoomType" | "note"
  >,
  user: AuthUser,
): Promise<ResidenceApplication> {
  if (!USE_MOCK) {
    return apiRequest<ResidenceApplication>("/residence/applications", {
      method: "POST",
      body: input,
    });
  }
  const profile = await getStudentDormProfile(user);
  if (!profile.studentNumber.trim() || !profile.university.trim()) {
    throw new ApiError(
      "PROFILE_INCOMPLETE",
      "Vui lòng cập nhật mã số sinh viên và trường học trước khi đăng ký lưu trú.",
      { status: 409 },
    );
  }
  const store = readStore();
  const overlaps = store.applications.some(
    (application) =>
      application.studentEmail === user.email &&
      ["submitted", "under_review", "approved"].includes(application.status) &&
      application.startDate <= input.endDate &&
      application.endDate >= input.startDate,
  );
  if (overlaps) {
    throw new ApiError("APPLICATION_OVERLAP", "Thời gian đăng ký bị trùng.", {
      status: 409,
    });
  }
  const application: ResidenceApplication = {
    ...input,
    id: Date.now(),
    applicationNumber: `KTX-DEMO-${Date.now()}`,
    studentName: profile.name,
    studentEmail: profile.email,
    studentNumber: profile.studentNumber,
    university: profile.university,
    status: "submitted",
    decisionNote: null,
    createdAt: new Date().toISOString(),
    contractNumber: null,
    contractStatus: null,
  };
  store.applications = [application, ...store.applications];
  writeStore(store);
  return application;
}

export async function getResidenceContracts(
  user?: AuthUser,
): Promise<ResidenceContract[]> {
  if (!USE_MOCK) return apiRequest<ResidenceContract[]>("/residence/contracts");
  if (!user) return [];
  return readStore().applications
    .filter(
      (application) =>
        application.studentEmail === user.email && application.contractNumber,
    )
    .map((application) => ({
      id: application.id,
      contractNumber: application.contractNumber as string,
      applicationNumber: application.applicationNumber,
      studentName: application.studentName,
      studentNumber: application.studentNumber,
      startDate: application.startDate,
      endDate: application.endDate,
      status: application.contractStatus ?? "pending_assignment",
    }));
}

export async function getAdminResidenceApplications(): Promise<
  ResidenceApplication[]
> {
  if (!USE_MOCK) {
    return apiRequest<ResidenceApplication[]>("/admin/residence/applications");
  }
  return readStore().applications;
}

export async function reviewResidenceApplication(
  id: number,
  decision: "under_review" | "approve" | "reject",
  note: string,
): Promise<ResidenceApplication> {
  if (!USE_MOCK) {
    return apiRequest<ResidenceApplication>(
      `/admin/residence/applications/${id}/review`,
      { method: "POST", body: { decision, note } },
    );
  }
  const store = readStore();
  const current = store.applications.find((application) => application.id === id);
  if (!current) {
    throw new ApiError("NOT_FOUND", "Không tìm thấy hồ sơ lưu trú.", {
      status: 404,
    });
  }
  if (!["submitted", "under_review"].includes(current.status)) {
    throw new ApiError("INVALID_STATE_TRANSITION", "Hồ sơ đã được xử lý.", {
      status: 409,
    });
  }
  const updated: ResidenceApplication = {
    ...current,
    status:
      decision === "approve"
        ? "approved"
        : decision === "reject"
          ? "rejected"
          : "under_review",
    decisionNote: note || null,
    contractNumber:
      decision === "approve"
        ? `HD-DEMO-${current.id}`
        : current.contractNumber,
    contractStatus:
      decision === "approve"
        ? "pending_assignment"
        : current.contractStatus,
  };
  store.applications = store.applications.map((application) =>
    application.id === id ? updated : application,
  );
  writeStore(store);
  return updated;
}

export async function getAssignableResidenceRoles(): Promise<
  AssignableResidenceRole[]
> {
  if (!USE_MOCK) {
    return apiRequest<AssignableResidenceRole[]>("/admin/roles");
  }
  return [
    { key: "dorm_manager", name: "Ban quản lý KTX" },
    { key: "service_staff", name: "Nhân viên dịch vụ" },
    { key: "tenant", name: "Đối tác / người thuê mặt bằng" },
  ];
}

export async function assignResidenceRole(
  userId: number,
  roleKey: AssignableResidenceRole["key"],
): Promise<void> {
  if (!USE_MOCK) {
    await apiRequest<{ userId: number; roleKey: string }>(
      `/admin/users/${userId}/roles`,
      { method: "POST", body: { roleKey } },
    );
  }
}
