import { expect, test } from "@playwright/test";

test("Luồng 1: đăng nhập sinh viên thành công -> /products", async ({ page }) => {
  await page.goto("/login");
  await page.fill("#email", "student@demo.vn");
  await page.fill("#password", "Demo1234");
  await page.getByRole("button", { name: /Đăng nhập/ }).click();
  await expect(page).toHaveURL(/\/products$/);
});

test("Đăng nhập sai mật khẩu hiện thông báo lỗi", async ({ page }) => {
  await page.goto("/login");
  await page.fill("#email", "student@demo.vn");
  await page.fill("#password", "SaiMatKhau1");
  await page.getByRole("button", { name: /Đăng nhập/ }).click();
  await expect(page.getByRole("alert").first()).toBeVisible();
  await expect(page).toHaveURL(/\/login$/);
});

test("Tài khoản bị khóa -> /account-locked", async ({ page }) => {
  await page.goto("/login");
  await page.fill("#email", "locked@demo.vn");
  await page.fill("#password", "Demo1234");
  await page.getByRole("button", { name: /Đăng nhập/ }).click();
  await expect(page).toHaveURL(/\/account-locked$/);
  await expect(page.getByText("Tài Khoản Đã Bị Khóa")).toBeVisible();
});

test("Luồng 2: đăng ký -> OTP -> /products", async ({ page }) => {
  await page.goto("/register");
  await page.fill("#name", "Nguyễn Văn Test");
  await page.fill("#email", "moi@demo.vn");
  await page.fill("#phone", "0912345678");
  await page.selectOption("#security_question", "q1");
  await page.fill("#security_answer", "demo");
  await page.fill("#password", "Demo1234");
  await page.fill("#password_confirm", "Demo1234");
  await page.getByRole("button", { name: "Tạo tài khoản" }).click();
  await expect(page).toHaveURL(/\/verify-otp$/);
  await expect(page.getByText("moi@demo.vn").first()).toBeVisible();

  // OTP sai trước, rồi nhập đúng
  await page.fill("input[inputmode=numeric]", "000000");
  await page.getByRole("button", { name: "Xác Minh Ngay" }).click();
  await expect(page.getByRole("alert").first()).toBeVisible();
  await page.fill("input[inputmode=numeric]", "123456");
  await page.getByRole("button", { name: "Xác Minh Ngay" }).click();
  await expect(page).toHaveURL(/\/products$/);
});

test("Đăng ký: hiện đủ lỗi validate khi để trống", async ({ page }) => {
  await page.goto("/register");
  await page.getByRole("button", { name: "Tạo tài khoản" }).click();
  await expect(page.getByText("Họ tên từ 2 - 100 ký tự.")).toBeVisible();
  await expect(page.getByText("Email không hợp lệ.")).toBeVisible();
});

test("Quên mật khẩu -> đặt lại -> đăng nhập bằng mật khẩu mới", async ({ page }) => {
  await page.goto("/forgot-password");
  await page.fill("#email", "student@demo.vn");
  await page.getByRole("button", { name: "Xác Nhận Email" }).click();
  await page.fill("#security_answer", "demo");
  await page.getByRole("button", { name: "Khôi Phục Mật Khẩu" }).click();
  await expect(page).toHaveURL(/\/login$/);
  await page.getByRole("link", { name: /Mở liên kết đặt lại mật khẩu/ }).click();
  await expect(page).toHaveURL(/\/reset-password\?/);
  await page.fill("#password", "NewPass123");
  await page.fill("#passwordConfirm", "NewPass123");
  await page.getByRole("button", { name: "Lưu Mật Khẩu Mới" }).click();
  await expect(page).toHaveURL(/\/login$/);
});

test("Truy cập /reset-password không token -> về /login", async ({ page }) => {
  await page.goto("/reset-password");
  await expect(page).toHaveURL(/\/login$/);
});

test("Admin: tài khoản sinh viên bị từ chối ở /admin-login, admin vào /admin", async ({ page }) => {
  await page.goto("/admin-login");
  await page.fill("#email", "student@demo.vn");
  await page.fill("#password", "Demo1234");
  await page.locator("form button[type=submit]").click();
  await expect(page).toHaveURL(/\/admin-login$/);
  await page.fill("#email", "admin@demo.vn");
  await page.fill("#password", "Admin1234");
  await page.locator("form button[type=submit]").click();
  await expect(page).toHaveURL(/\/admin$/);
});
