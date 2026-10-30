import { describe, expect, it } from "vitest";
import {
  buildCsvContent,
  calculateStrikeOutcome,
  computeLockedUntil,
  formatDateTimeVi,
  formatDateVi,
  formatShortDateTimeVi,
  formatVnd,
  isWithinDateRange,
  makeCategorySlug,
  validateBannerLink,
  validateCategoryName,
  validateDateRange,
} from "@/lib/validation/admin";

describe("makeCategorySlug", () => {
  it("chuyển tiếng Việt có dấu (kể cả chữ đ/Đ) sang slug chuẩn không dấu", () => {
    expect(makeCategorySlug("Đồ điện tử & Công nghệ")).toBe(
      "do-dien-tu-cong-nghe",
    );
    expect(makeCategorySlug("Sách / Giáo trình Đại Học")).toBe(
      "sach-giao-trinh-dai-hoc",
    );
    expect(makeCategorySlug("  Đồ gia dụng   KTX  ")).toBe("do-gia-dung-ktx");
  });

  it("trả về chuỗi rỗng khi chỉ có ký tự đặc biệt hoặc khoảng trắng", () => {
    expect(makeCategorySlug("   !!! @@@   ")).toBe("");
  });
});

describe("validateCategoryName", () => {
  it("báo lỗi khi tên danh mục ngắn hơn 2 ký tự", () => {
    expect(validateCategoryName("")).toBe(
      "Tên danh mục phải có ít nhất 2 ký tự.",
    );
    expect(validateCategoryName(" A ")).toBe(
      "Tên danh mục phải có ít nhất 2 ký tự.",
    );
  });

  it("báo lỗi khi tên danh mục vượt quá 80 ký tự", () => {
    expect(validateCategoryName("a".repeat(81))).toBe(
      "Tên danh mục tối đa 80 ký tự.",
    );
  });

  it("hợp lệ khi tên từ 2 đến 80 ký tự", () => {
    expect(validateCategoryName("Đồ điện tử")).toBeNull();
  });
});

describe("validateBannerLink", () => {
  it("cho phép để trống hoặc đường dẫn nội bộ / hoặc http(s)://", () => {
    expect(validateBannerLink("")).toBeNull();
    expect(validateBannerLink("/products?cat=2")).toBeNull();
    expect(validateBannerLink("https://sinhvienmarket.vn/promo")).toBeNull();
    expect(validateBannerLink("http://localhost:3000/giveaways")).toBeNull();
  });

  it("từ chối liên kết thiếu giao thức hoặc không hợp lệ", () => {
    expect(validateBannerLink("sinhvienmarket.vn")).toBe(
      "Link đích phải bắt đầu bằng / hoặc http(s)://",
    );
    expect(validateBannerLink("javascript:alert(1)")).toBe(
      "Link đích phải bắt đầu bằng / hoặc http(s)://",
    );
  });
});

describe("validateDateRange", () => {
  it("hợp lệ khi từ ngày nhỏ hơn hoặc bằng đến ngày, hoặc để trống", () => {
    expect(validateDateRange("", "")).toBeNull();
    expect(validateDateRange("2026-10-01", "2026-10-31")).toBeNull();
    expect(validateDateRange("2026-10-15", "2026-10-15")).toBeNull();
  });

  it("báo lỗi khi từ ngày lớn hơn đến ngày", () => {
    expect(validateDateRange("2026-10-20", "2026-10-10")).toBe(
      'Lỗi: "Từ ngày" không được lớn hơn "Đến ngày"!',
    );
  });
});

describe("computeLockedUntil", () => {
  const baseDate = new Date("2026-10-01T00:00:00.000Z");

  it("tính đúng ngày mở khóa cho các kỳ hạn có thời hạn", () => {
    expect(computeLockedUntil("3days", baseDate)).toBe(
      "2026-10-04T00:00:00.000Z",
    );
    expect(computeLockedUntil("1week", baseDate)).toBe(
      "2026-10-08T00:00:00.000Z",
    );
    expect(computeLockedUntil("1month", baseDate)).toBe(
      "2026-10-31T00:00:00.000Z",
    );
  });

  it("trả về null khi khóa vĩnh viễn (forever)", () => {
    expect(computeLockedUntil("forever", baseDate)).toBeNull();
  });
});

describe("calculateStrikeOutcome (Quy tắc 3 gậy)", () => {
  const now = new Date("2026-10-10T00:00:00.000Z");

  it("Gậy 1: chỉ cảnh cáo, chưa khóa tài khoản", () => {
    const res = calculateStrikeOutcome(0, "Đăng sai danh mục", "Minh Anh", now);
    expect(res.nextStrike).toBe(1);
    expect(res.action).toBe("warning");
    expect(res.isLocked).toBe(false);
    expect(res.lockedUntil).toBeNull();
  });

  it("Gậy 2: tự động khóa tài khoản 7 ngày", () => {
    const res = calculateStrikeOutcome(1, "Spam tin đăng", "Minh Anh", now);
    expect(res.nextStrike).toBe(2);
    expect(res.action).toBe("suspend_7_days");
    expect(res.isLocked).toBe(true);
    expect(res.lockedUntil).toBe("2026-10-17T00:00:00.000Z");
  });

  it("Gậy 3 trở lên: tự động khóa vĩnh viễn", () => {
    const res = calculateStrikeOutcome(2, "Lừa đảo đặt cọc", "Minh Anh", now);
    expect(res.nextStrike).toBe(3);
    expect(res.action).toBe("ban_permanent");
    expect(res.isLocked).toBe(true);
    expect(res.lockedUntil).toBeNull();
  });
});

describe("Hàm định dạng tiền tệ, ngày giờ & lọc khoảng ngày", () => {
  it("formatVnd định dạng số tiền chuẩn tiếng Việt", () => {
    expect(formatVnd(null)).toBe("—");
    expect(formatVnd(0)).toBe("0đ");
    expect(formatVnd(850000)).toMatch(/850[.,]000đ/);
  });

  it("formatDateVi, formatDateTimeVi, formatShortDateTimeVi xử lý đúng chuỗi ISO và giá trị rỗng", () => {
    expect(formatDateVi(null)).toBe("—");
    expect(formatDateVi("invalid")).toBe("—");
    expect(formatDateVi("2026-10-28T07:30:00+07:00")).toBe("28/10/2026");
    expect(formatDateTimeVi("2026-10-28T07:30:00+07:00")).toMatch(
      /^28\/10\/2026 \d{2}:\d{2}$/,
    );
    expect(formatShortDateTimeVi("2026-10-28T07:30:00+07:00")).toMatch(
      /^28\/10 \d{2}:\d{2}$/,
    );
  });

  it("isWithinDateRange kiểm tra đúng ngày nằm trong khoảng", () => {
    const sample = "2026-10-25T12:00:00+07:00";
    expect(isWithinDateRange(sample, "2026-10-01", "2026-10-31")).toBe(true);
    expect(isWithinDateRange(sample, "2026-10-26", "2026-10-31")).toBe(false);
    expect(isWithinDateRange(sample, "2026-10-01", "2026-10-20")).toBe(false);
  });
});

describe("buildCsvContent", () => {
  it("tạo chuỗi CSV có UTF-8 BOM, escape dấu phẩy/ngoặc kép và đính kèm thống kê", () => {
    const csv = buildCsvContent({
      headers: ["ID", "Tên sản phẩm", "Giá"],
      rows: [
        [101, 'Sách "Giải tích 1", bản mới', 65000],
        [102, "Bàn phím cơ", 850000],
      ],
      summaryRows: [["Tổng cộng", 2, 915000]],
      fromDate: "2026-10-01",
      toDate: "2026-10-31",
    });

    expect(csv.startsWith("\uFEFF")).toBe(true);
    expect(csv).toContain("Báo cáo từ ngày:,2026-10-01,Đến ngày:,2026-10-31");
    expect(csv).toContain('101,"Sách ""Giải tích 1"", bản mới",65000');
    expect(csv).toContain("--- THỐNG KÊ TỔNG HỢP ---");
    expect(csv).toContain("Tổng cộng,2,915000");
  });
});
