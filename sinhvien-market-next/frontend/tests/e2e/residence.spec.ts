import { expect, test } from "@playwright/test";

test("sinh viên gửi đăng ký lưu trú, admin duyệt và tạo hợp đồng", async ({
  page,
}) => {
  await page.goto("/login");
  await page.fill("#email", "student@demo.vn");
  await page.fill("#password", "Demo1234");
  await page.getByRole("button", { name: /Đăng nhập/ }).click();
  await expect(page).toHaveURL(/\/products$/);

  await page.goto("/residence");
  await expect(
    page.getByRole("heading", { name: "Thông tin sinh viên" }),
  ).toBeVisible();
  await page.getByLabel("Ngày bắt đầu").fill("2030-08-01");
  await page.getByLabel("Ngày kết thúc").fill("2031-07-31");
  await page.getByLabel("Loại phòng mong muốn").selectOption("standard");
  await page.getByRole("button", { name: "Gửi hồ sơ đăng ký" }).click();
  await expect(page.getByText(/KTX-DEMO-/)).toBeVisible();
  await expect(page.getByText("submitted")).toBeVisible();

  await page.locator("button").filter({ hasText: "Nguyễn Minh Anh" }).first().click();
  await page.getByRole("button", { name: "Đăng xuất" }).click();
  await expect(page).toHaveURL(/\/login-role$/);
  await page.goto("/admin-login");
  await page.fill("#email", "admin@demo.vn");
  await page.fill("#password", "Admin1234");
  await page.locator("form button[type=submit]").click();
  await expect(page).toHaveURL(/\/admin$/);

  await page.goto("/admin/residence");
  await expect(page.getByText(/KTX-DEMO-/)).toBeVisible();
  await page.getByRole("button", { name: "Duyệt và tạo hợp đồng" }).click();
  await expect(page.getByText("approved")).toBeVisible();
  await expect(page.getByText(/HD-DEMO-/)).toBeVisible();
});
