import { expect, test, type Page } from "@playwright/test";

const ADMIN_SESSION = {
  id: 2,
  name: "Quản trị viên",
  email: "admin@demo.vn",
  role: "admin",
};

async function seedAdminSession(page: Page) {
  await page.addInitScript((session) => {
    window.sessionStorage.setItem("svm_mock_session", JSON.stringify(session));
  }, ADMIN_SESSION);
}

test("Admin Luồng 1: bảo vệ route /admin và đăng nhập vào Dashboard", async ({
  page,
}) => {
  await page.goto("/admin");
  await expect(page).toHaveURL(/\/admin-login$/);

  await page.fill("#email", "admin@sinhvienmarket.edu.vn");
  await page.fill("#password", "Admin123");
  await page.locator("form button[type=submit]").click();

  await expect(page).toHaveURL(/\/admin$/);
  await expect(page.getByText("Tổng người dùng")).toBeVisible();
  await expect(page.getByText("Kiểm duyệt").first()).toBeVisible();
});

test("Admin Luồng 2: kiểm duyệt bài đăng (/admin/products) & quản lý danh mục (/admin/categories)", async ({
  page,
}) => {
  await seedAdminSession(page);

  await page.goto("/admin/products");
  await expect(
    page.getByText("Giáo trình Giải tích 1 & Đại số tuyến tính").first(),
  ).toBeVisible();

  await page.goto("/admin/categories");
  await expect(page.getByText("Sách & Giáo trình").first()).toBeVisible();
  await page.getByRole("button", { name: /Thêm danh mục/i }).click();
  await page.fill("#cat-name", "Đồ dùng thể thao ĐH");
  await expect(page.getByText("do-dung-the-thao-dh")).toBeVisible();
});

test("Admin Luồng 3: quản lý người dùng, hồ sơ 360° & lịch sử gậy (/admin/users/*)", async ({
  page,
}) => {
  await seedAdminSession(page);

  await page.goto("/admin/users");
  await expect(page.getByText("Nguyễn Minh Anh").first()).toBeVisible();

  await page.goto("/admin/users/detail?id=1");
  await expect(page.getByText("Nguyễn Minh Anh").first()).toBeVisible();
  await expect(page.getByText("student@demo.vn").first()).toBeVisible();

  await page.goto("/admin/users/violations?id=5");
  await expect(page.getByText("2/3").first()).toBeVisible();
  await expect(
    page.getByText("Spam bài đăng trùng lặp liên tục trong 1 giờ."),
  ).toBeVisible();
});

test("Admin Luồng 4: báo cáo giao dịch, tố cáo vi phạm & kiểm duyệt đánh giá", async ({
  page,
}) => {
  await seedAdminSession(page);

  await page.goto("/admin/reports");
  await expect(page.getByText("Tổng giá trị giao dịch")).toBeVisible();
  await expect(page.getByText("Biểu đồ doanh số theo ngày")).toBeVisible();

  await page.goto("/admin/system-reports");
  await expect(
    page.getByText("Nghi vấn hàng giả / hàng nhái").first(),
  ).toBeVisible();

  await page.goto("/admin/ratings");
  await expect(page.getByText("Tổng lượt đánh giá")).toBeVisible();
  await expect(page.getByText("Điểm trung bình sàn")).toBeVisible();
});

test("Admin Luồng 5: Giveaways, Vòng quay may mắn & Nhật ký Admin tự động", async ({
  page,
}) => {
  await seedAdminSession(page);

  await page.goto("/admin/giveaways");
  await expect(page.getByText("Tạo Giveaway mới")).toBeVisible();

  await page.goto("/admin/giveaway-spin?id=1");
  await expect(
    page.getByText("Bàn quay ngẫu nhiên công bằng (6 giây)"),
  ).toBeVisible();

  const spinBtn = page.getByRole("button", { name: /Bắt đầu Quay số!/i });
  await expect(spinBtn).toBeVisible();
  await spinBtn.click();

  const fastBtn = page.getByRole("button", { name: /Hiện kết quả ngay/i });
  await expect(fastBtn).toBeVisible();
  await fastBtn.click();

  await expect(
    page.getByText("🎉 Chúc mừng người chiến thắng!"),
  ).toBeVisible();

  await page.goto("/admin/audit-log");
  await expect(
    page.getByText("Nhật ký bảo mật tự động (Chỉ đọc — Read-only Audit Trail)"),
  ).toBeVisible();
  await expect(page.getByText("🎰 Quay số Giveaway").first()).toBeVisible();
});

test("Admin Đo tốc độ: thời gian mở và hiển thị dữ liệu các màn hình quản trị <= 2000ms", async ({
  page,
}) => {
  await seedAdminSession(page);

  const routesToMeasure: { path: string; marker: string }[] = [
    { path: "/admin", marker: "Tổng người dùng" },
    { path: "/admin/products", marker: "Giáo trình Giải tích 1" },
    { path: "/admin/users", marker: "Nguyễn Minh Anh" },
    { path: "/admin/reports", marker: "Biểu đồ doanh số theo ngày" },
    { path: "/admin/audit-log", marker: "Nhật ký bảo mật tự động" },
  ];

  for (const item of routesToMeasure) {
    const t0 = Date.now();
    await page.goto(item.path);
    await page.getByText(item.marker).first().waitFor({ state: "visible" });
    const elapsed = Date.now() - t0;
    expect(elapsed, `Tốc độ mở ${item.path}`).toBeLessThanOrEqual(2000);
  }
});
