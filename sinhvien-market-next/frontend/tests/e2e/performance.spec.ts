import { expect, test, type Page } from "@playwright/test";

/**
 * Đo tốc độ phản hồi của đăng nhập / đăng ký (yêu cầu của leader).
 * Lưu ý: mock API cố ý trễ 300–800ms để giả lập mạng, nên ngân sách dưới đây
 * gồm cả độ trễ giả lập đó. Khi nối backend thật cần đo lại.
 */
const BUDGET = {
  validationFeedbackMs: 150, // bấm nút -> lỗi validate hiện (không gọi mạng)
  loginToRedirectMs: 1500, // bấm đăng nhập -> sang trang đích
  registerToOtpMs: 1500, // bấm đăng ký -> sang trang OTP
  pageLoadMs: 2000, // mở trang -> form dùng được
};

const results: string[] = [];

test.afterAll(() => {
  console.log("\n===== KẾT QUẢ ĐO TỐC ĐỘ =====\n" + results.join("\n"));
});

async function measure<T>(label: string, budget: number, fn: () => Promise<T>) {
  const t0 = Date.now();
  await fn();
  const ms = Date.now() - t0;
  results.push(
    `${label}: ${ms}ms (ngân sách ${budget}ms) ${ms <= budget ? "ĐẠT" : "VƯỢT"}`,
  );
  expect(ms, label).toBeLessThanOrEqual(budget);
}

/**
 * Đo trong trình duyệt: từ lúc nhận sự kiện click đến lúc thông báo lỗi xuất hiện trong DOM.
 * Cách này loại bỏ thời gian chờ/độ trễ của chính công cụ test (Playwright).
 */
async function measureValidationInPage(
  page: Page,
  button: string,
  errorText: string,
) {
  await page.waitForLoadState("networkidle");
  return page.evaluate(
    ({ button, errorText }) =>
      new Promise<number>((resolve) => {
        const btn = Array.from(document.querySelectorAll("button")).find((b) =>
          b.textContent?.includes(button),
        )!;
        const t0 = performance.now();
        const obs = new MutationObserver(() => {
          if (document.body.innerText.includes(errorText)) {
            obs.disconnect();
            resolve(performance.now() - t0);
          }
        });
        obs.observe(document.body, {
          childList: true,
          subtree: true,
          characterData: true,
        });
        btn.click();
      }),
    { button, errorText },
  );
}

test("tải /login và /register", async ({ page }) => {
  for (const path of ["/login", "/register"]) {
    await measure(`Mở ${path}`, BUDGET.pageLoadMs, async () => {
      await page.goto(path);
      await page.locator("#email").waitFor();
    });
  }
});

test("phản hồi validate form đăng nhập", async ({ page }) => {
  await page.goto("/login");
  const ms = await measureValidationInPage(
    page,
    "Đăng nhập",
    "Email không hợp lệ.",
  );
  results.push(
    `Đăng nhập: lỗi validate hiện (đo trong trình duyệt): ${Math.round(ms)}ms (ngân sách ${BUDGET.validationFeedbackMs}ms) ${ms <= BUDGET.validationFeedbackMs ? "ĐẠT" : "VƯỢT"}`,
  );
  expect(ms).toBeLessThanOrEqual(BUDGET.validationFeedbackMs);
});

test("phản hồi validate form đăng ký", async ({ page }) => {
  await page.goto("/register");
  const ms = await measureValidationInPage(
    page,
    "Tạo tài khoản",
    "Họ tên từ 2 - 100 ký tự.",
  );
  results.push(
    `Đăng ký: lỗi validate hiện (đo trong trình duyệt): ${Math.round(ms)}ms (ngân sách ${BUDGET.validationFeedbackMs}ms) ${ms <= BUDGET.validationFeedbackMs ? "ĐẠT" : "VƯỢT"}`,
  );
  expect(ms).toBeLessThanOrEqual(BUDGET.validationFeedbackMs);
});

test("đăng nhập thành công -> chuyển trang", async ({ page }) => {
  await page.goto("/login");
  await page.fill("#email", "student@demo.vn");
  await page.fill("#password", "Demo1234");
  await measure("Đăng nhập -> /products", BUDGET.loginToRedirectMs, async () => {
    await page.getByRole("button", { name: /Đăng nhập/ }).click();
    await page.waitForURL(/\/products$/);
  });
});

test("đăng ký -> chuyển sang OTP", async ({ page }) => {
  await page.goto("/register");
  await page.fill("#name", "Nguyễn Văn Test");
  await page.fill("#email", "toc-do@demo.vn");
  await page.selectOption("#security_question", "q2");
  await page.fill("#security_answer", "demo");
  await page.fill("#password", "Demo1234");
  await page.fill("#password_confirm", "Demo1234");
  await measure("Đăng ký -> /verify-otp", BUDGET.registerToOtpMs, async () => {
    await page.getByRole("button", { name: "Tạo tài khoản" }).click();
    await page.waitForURL(/\/verify-otp$/);
  });
});

test("nút bị vô hiệu hoá khi đang gửi (chống bấm đúp)", async ({ page }) => {
  await page.goto("/login");
  await page.fill("#email", "student@demo.vn");
  await page.fill("#password", "Demo1234");
  const btn = page.getByRole("button", { name: /Đăng nhập/ });
  await btn.click();
  await expect(page.locator("form button[type=submit]")).toBeDisabled();
});
