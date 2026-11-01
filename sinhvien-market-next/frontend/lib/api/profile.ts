import { USE_MOCK } from "@/lib/config";
import { SECURITY_QUESTIONS, type SecurityQuestionId } from "@/lib/constants/auth";
import { ApiError } from "./types";
import { apiRequest } from "./client";
import { getAdminProducts, getAdminRatings } from "./admin";
import type { AdminProduct } from "@/lib/types/admin";
import type { AuthUser } from "./types";

export interface BankAccountProfile {
  bankName: string;
  bankCode: string;
  accountNo: string;
  accountName: string;
}

export interface LoginHistoryEntry {
  id: number;
  loggedAt: string;
  ipAddress: string;
  deviceInfo: string;
  isCurrent: boolean;
  isNewDevice: boolean;
}

export interface OwnProfile {
  id: number;
  name: string;
  email: string;
  phone: string;
  university: string;
  studentId: string;
  dormitoryAddress: string;
  socialContact: string;
  bio: string;
  availableTime: string;
  avatarDataUrl: string | null;
  createdAt: string;
  emailVerified: boolean;
  studentVerified: boolean;
  coins: number;
  rankName: string;
  rankIcon: string;
  rankColor: string;
  securityQuestion: SecurityQuestionId | null;
  bankAccount: BankAccountProfile | null;
  loginHistory: LoginHistoryEntry[];
}

export interface PublicProfileReview {
  id: number;
  raterName: string;
  productTitle: string;
  stars: number;
  comment: string | null;
  createdAt: string;
}

export interface PublicProfile {
  id: number;
  name: string;
  avatarDataUrl: string | null;
  studentVerified: boolean;
  createdAt: string;
  bio: string;
  university: string;
  dormitoryAddress: string;
  socialContact: string;
  availableTime: string;
  averageRating: number;
  ratingCount: number;
  reviews: PublicProfileReview[];
  activeProducts: AdminProduct[];
}

export type OwnProfileUpdate = Pick<
  OwnProfile,
  | "name"
  | "phone"
  | "university"
  | "studentId"
  | "dormitoryAddress"
  | "socialContact"
  | "bio"
  | "availableTime"
>;

const PROFILE_STORE_KEY = "svm_mock_profiles_v1";
const BANK_STORE_KEY = "svm_mock_profile_banks_v1";
const AVATAR_STORE_KEY = "svm_mock_profile_avatars_v1";
const SECURITY_STORE_KEY = "svm_mock_profile_security_v1";

const sampleProfiles: Record<number, Partial<OwnProfile>> = {
  1: {
    phone: "0901234567",
    university: "Đại học Khoa học Tự nhiên",
    studentId: "23120001",
    dormitoryAddress: "KTX Khu B · Tòa BA1",
    socialContact: "zalo.me/0901234567",
    bio: "Mình là sinh viên năm 3, thường trao đổi giáo trình và đồ dùng học tập còn tốt.",
    availableTime: "Buổi tối và cuối tuần",
    createdAt: "2026-09-15T08:30:00+07:00",
    emailVerified: true,
    studentVerified: true,
    coins: 150,
    rankName: "Sinh viên năng động",
    rankIcon: "sparkles",
    rankColor: "indigo",
    securityQuestion: "q1",
    bankAccount: {
      bankName: "Vietcombank",
      bankCode: "VCB",
      accountNo: "0123456789",
      accountName: "NGUYEN MINH ANH",
    },
    loginHistory: [
      {
        id: 1,
        loggedAt: "2026-11-01T20:45:00+07:00",
        ipAddress: "127.0.0.1",
        deviceInfo: "Chrome · Windows",
        isCurrent: true,
        isNewDevice: false,
      },
      {
        id: 2,
        loggedAt: "2026-10-31T21:12:00+07:00",
        ipAddress: "127.0.0.1",
        deviceInfo: "Chrome · Windows",
        isCurrent: false,
        isNewDevice: false,
      },
    ],
  },
  6: {
    phone: "0977889900",
    university: "Đại học Công nghệ Thông tin",
    studentId: "22520718",
    dormitoryAddress: "KTX Khu A · Tòa A3",
    bio: "Mình thích công nghệ và thường thanh lý đồ điện tử sau khi nâng cấp.",
    availableTime: "Sau 18:00",
    createdAt: "2026-09-20T14:10:00+07:00",
    emailVerified: true,
    studentVerified: true,
    rankName: "Thành viên tích cực",
    rankIcon: "star",
    rankColor: "emerald",
  },
};

function readStore<T>(key: string): T {
  if (typeof localStorage === "undefined") return {} as T;
  try {
    const raw = localStorage.getItem(key);
    return raw ? (JSON.parse(raw) as T) : ({} as T);
  } catch {
    throw new ApiError(
      "UNKNOWN",
      "Không thể đọc dữ liệu hồ sơ trên trình duyệt.",
      { status: 500 },
    );
  }
}

function writeStore<T>(key: string, value: T): void {
  if (typeof localStorage === "undefined") {
    throw new ApiError(
      "UNKNOWN",
      "Không thể lưu hồ sơ khi trình duyệt chưa sẵn sàng.",
      { status: 500 },
    );
  }
  try {
    localStorage.setItem(key, JSON.stringify(value));
  } catch {
    throw new ApiError(
      "UNKNOWN",
      "Không đủ dung lượng trình duyệt để lưu dữ liệu hồ sơ.",
      { status: 507 },
    );
  }
}

function profileLabel(question: SecurityQuestionId | null): string {
  return (
    SECURITY_QUESTIONS.find((item) => item.value === question)?.label ??
    "Chưa thiết lập"
  );
}

function getStoredObject<T>(key: string): Record<string, T> {
  return readStore<Record<string, T>>(key);
}

function defaultProfile(user: AuthUser): OwnProfile {
  const sample = sampleProfiles[user.id] ?? {};
  const profiles = getStoredObject<Partial<OwnProfile>>(PROFILE_STORE_KEY);
  const banks = getStoredObject<BankAccountProfile>(BANK_STORE_KEY);
  const avatars = getStoredObject<string>(AVATAR_STORE_KEY);
  const questions = getStoredObject<SecurityQuestionId>(SECURITY_STORE_KEY);

  return {
    id: user.id,
    name: user.name,
    email: user.email,
    phone: sample.phone ?? "",
    university: sample.university ?? "",
    studentId: sample.studentId ?? "",
    dormitoryAddress: sample.dormitoryAddress ?? "",
    socialContact: sample.socialContact ?? "",
    bio: sample.bio ?? "",
    availableTime: sample.availableTime ?? "",
    avatarDataUrl: avatars[String(user.id)] ?? null,
    createdAt: sample.createdAt ?? "2026-10-01T08:00:00+07:00",
    emailVerified: sample.emailVerified ?? true,
    studentVerified: sample.studentVerified ?? user.role === "student",
    coins: sample.coins ?? 0,
    rankName: sample.rankName ?? "Tân binh",
    rankIcon: sample.rankIcon ?? "user",
    rankColor: sample.rankColor ?? "slate",
    securityQuestion:
      questions[String(user.id)] ??
      sample.securityQuestion ??
      (null as SecurityQuestionId | null),
    bankAccount: banks[String(user.id)] ?? sample.bankAccount ?? null,
    loginHistory: sample.loginHistory ?? [],
    ...profiles[String(user.id)],
  };
}

export async function getOwnProfile(user: AuthUser): Promise<OwnProfile> {
  if (!USE_MOCK) {
    return apiRequest<OwnProfile>("/profile/me");
  }
  await Promise.resolve();
  return defaultProfile(user);
}

export async function updateOwnProfile(
  user: AuthUser,
  input: OwnProfileUpdate,
): Promise<OwnProfile> {
  if (!USE_MOCK) {
    return apiRequest<OwnProfile>("/profile/me", {
      method: "PUT",
      body: input,
    });
  }
  const name = input.name.trim();
  if (name.length < 2 || name.length > 100) {
    throw new ApiError("VALIDATION_ERROR", "Họ tên cần từ 2 đến 100 ký tự.", {
      status: 422,
      fields: { name: "Họ tên cần từ 2 đến 100 ký tự." },
    });
  }
  const current = defaultProfile(user);
  const next = { ...current, ...input, name };
  const profiles = getStoredObject<Partial<OwnProfile>>(PROFILE_STORE_KEY);
  profiles[String(user.id)] = {
    name: next.name,
    phone: next.phone,
    university: next.university,
    studentId: next.studentId,
    dormitoryAddress: next.dormitoryAddress,
    socialContact: next.socialContact,
    bio: next.bio,
    availableTime: next.availableTime,
  };
  writeStore(PROFILE_STORE_KEY, profiles);
  return next;
}

export async function updateProfileAvatar(
  userId: number,
  avatarDataUrl: string,
): Promise<void> {
  if (!USE_MOCK) {
    const blob = await fetch(avatarDataUrl).then((response) => response.blob());
    const formData = new FormData();
    formData.append("avatar", blob, "avatar.jpg");
    await apiRequest<{ message: string }>("/profile/avatar", {
      method: "POST",
      body: formData,
    });
    return;
  }
  const avatars = getStoredObject<string>(AVATAR_STORE_KEY);
  avatars[String(userId)] = avatarDataUrl;
  writeStore(AVATAR_STORE_KEY, avatars);
}

export async function updateProfileBank(
  userId: number,
  bank: BankAccountProfile,
): Promise<void> {
  if (!USE_MOCK) {
    await apiRequest<{ message: string }>("/profile/bank", {
      method: "POST",
      body: bank,
    });
    return;
  }
  const banks = getStoredObject<BankAccountProfile>(BANK_STORE_KEY);
  banks[String(userId)] = bank;
  writeStore(BANK_STORE_KEY, banks);
}

export async function updateProfileSecurityQuestion(
  userId: number,
  question: SecurityQuestionId,
  answer: string,
): Promise<void> {
  if (!answer.trim()) {
    throw new ApiError(
      "VALIDATION_ERROR",
      "Vui lòng nhập câu trả lời bảo mật.",
      { status: 422, fields: { answer: "Không được để trống." } },
    );
  }
  if (!USE_MOCK) {
    await apiRequest<{ message: string }>("/profile/security-question", {
      method: "POST",
      body: { question, answer },
    });
    return;
  }
  const questions = getStoredObject<SecurityQuestionId>(SECURITY_STORE_KEY);
  const auth = await import("./mock/auth");
  await auth.mockUpdateSecurityQuestion(userId, question, answer);
  questions[String(userId)] = question;
  writeStore(SECURITY_STORE_KEY, questions);
}

export async function changeProfilePassword(input: {
  currentPassword: string;
  newPassword: string;
  confirmPassword: string;
}): Promise<void> {
  if (!USE_MOCK) {
    await apiRequest<{ message: string }>("/profile/password", {
      method: "POST",
      body: input,
    });
    return;
  }
  if (
    input.newPassword.length < 8 ||
    !/[A-Z]/.test(input.newPassword) ||
    !/[0-9]/.test(input.newPassword)
  ) {
    throw new ApiError(
      "VALIDATION_ERROR",
      "Mật khẩu mới cần ít nhất 8 ký tự, có chữ hoa và chữ số.",
      { status: 422 },
    );
  }
  if (input.newPassword !== input.confirmPassword) {
    throw new ApiError("VALIDATION_ERROR", "Mật khẩu xác nhận không khớp.", {
      status: 422,
    });
  }
  const auth = await import("./mock/auth");
  await auth.mockChangePassword(input.currentPassword, input.newPassword);
}

export async function getPublicProfile(
  userId: number,
): Promise<PublicProfile> {
  if (!USE_MOCK) {
    return apiRequest<PublicProfile>(
      `/users/profile?id=${encodeURIComponent(userId)}`,
    );
  }
  const [users, products, ratings] = await Promise.all([
    (await import("./admin")).getAdminUsers(),
    getAdminProducts("all"),
    getAdminRatings(),
  ]);
  const user = users.find((item) => item.id === userId);
  if (!user || user.role !== "student") {
    throw new ApiError("UNKNOWN", "Không tìm thấy hồ sơ sinh viên này.", {
      status: 404,
    });
  }

  const sample = sampleProfiles[userId] ?? {};
  const profiles = getStoredObject<Partial<OwnProfile>>(PROFILE_STORE_KEY);
  const avatars = getStoredObject<string>(AVATAR_STORE_KEY);
  const ownProfile = profiles[String(userId)];
  const reviews = ratings
    .filter((rating) => rating.rateeId === userId && rating.status === "visible")
    .map((rating) => ({
      id: rating.id,
      raterName: rating.raterName,
      productTitle: rating.productTitle,
      stars: rating.stars,
      comment: rating.comment,
      createdAt: rating.createdAt,
    }));
  const averageRating =
    reviews.length > 0
      ? Number(
          (
            reviews.reduce((sum, review) => sum + review.stars, 0) /
            reviews.length
          ).toFixed(1),
        )
      : 0;

  const profile: PublicProfile = {
    id: user.id,
    name: ownProfile?.name ?? user.name,
    avatarDataUrl: avatars[String(userId)] ?? null,
    studentVerified: user.isVerified,
    createdAt: user.createdAt,
    bio: ownProfile?.bio ?? sample.bio ?? "",
    university: ownProfile?.university ?? sample.university ?? "",
    dormitoryAddress:
      ownProfile?.dormitoryAddress ?? sample.dormitoryAddress ?? "",
    socialContact: ownProfile?.socialContact ?? sample.socialContact ?? "",
    availableTime: ownProfile?.availableTime ?? sample.availableTime ?? "",
    averageRating,
    ratingCount: reviews.length,
    reviews,
    activeProducts: products.filter(
      (product) => product.sellerId === userId && product.status === "active",
    ),
  };

  return profile;
}

export function getSecurityQuestionLabel(
  question: SecurityQuestionId | null,
): string {
  return profileLabel(question);
}
