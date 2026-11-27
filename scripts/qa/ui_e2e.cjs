// Kiểm thử giao diện thật bằng trình duyệt (W10–11): thao tác như người dùng, dò lỗi mọi trang
// theo 4 vai trò, kiểm tra hiển thị trên điện thoại 390px, XSS, và chụp màn hình cho báo cáo.
// node ui_e2e.cjs <frontend_dir> <base_url> <test_password_file> <accounts.json> <image.jpg> <out_dir>
const path = require("path");
const fs = require("fs");
const { execFileSync } = require("child_process");
const MYSQL = (process.env.MYSQL_CMD || "mysql -h127.0.0.1 -P3307 -uroot --default-character-set=utf8mb4").split(" ").concat(["sinhvien_market_demo"]);
const { createRequire } = require("module");
const req = createRequire(path.join(process.argv[2], "package.json"));
const { chromium } = req("@playwright/test");
const [, , , BASE, PWFILE, ACCOUNTS, IMAGE, OUT] = process.argv;
const PW = fs.readFileSync(PWFILE, "utf8").trim();
const ACC = Object.fromEntries(JSON.parse(fs.readFileSync(ACCOUNTS, "utf8")).map((a) => [a.key, a]));
const ADMIN = "qa.admin@example.test";
const MANAGER = "quanly.ktx@example.com";
const SHOTS = path.join(OUT, "shots");
fs.mkdirSync(SHOTS, { recursive: true });
const results = [];
let group = "?";
const RUN = Date.now().toString().slice(-6);

function record(ok, name, detail = "") {
  results.push({ group, name, ok: !!ok, detail: String(detail).slice(0, 300) });
  console.log(`  ${ok ? "PASS" : "FAIL"} ${name}${detail ? "  -> " + String(detail).slice(0, 200) : ""}`);
}
async function step(name, fn) {
  try {
    const detail = await fn();
    record(true, name, detail ?? "");
  } catch (e) {
    record(false, name, String(e.message || e).split("\n")[0]);
  }
}
function watch(page, issues) {
  page.on("pageerror", (e) => issues.push("JS: " + String(e.message).slice(0, 160)));
  page.on("console", (m) => {
    if (m.type() === "error" && !/Failed to load resource: the server responded with a status of (401|403|404|409|422)/.test(m.text()))
      issues.push("console: " + m.text().slice(0, 160));
  });
  page.on("response", (r) => {
    if (r.url().includes("/api/") && r.status() >= 500) issues.push(`api ${r.status()}: ${r.url().replace(BASE, "").slice(0, 90)}`);
  });
}
async function apiLogin(ctx, email, role = "student") {
  const c = await (await ctx.request.get(BASE + "/api/auth/csrf")).json();
  const r = await ctx.request.post(BASE + "/api/auth/login", {
    data: { email, password: PW, role },
    headers: { "X-CSRF-Token": c.data.token },
  });
  const body = await r.json();
  return body.data?.csrfToken || (await (await ctx.request.get(BASE + "/api/auth/csrf")).json()).data.token;
}
async function api(ctx, token, method, url, data) {
  const r = await ctx.request.fetch(BASE + url, { method, data, headers: { "X-CSRF-Token": token } });
  const body = await r.json().catch(() => ({}));
  if (r.status() >= 400) throw new Error(`${method} ${url} -> ${r.status()} ${body.error?.message || ""}`);
  return body.data;
}
const shot = (page, name) => page.screenshot({ path: path.join(SHOTS, name + ".png"), fullPage: false });

(async () => {
  const browser = await chromium.launch();

  // ------------------------------------------------------------ 1. Luồng thật trên giao diện
  group = "UI-1. Đăng nhập & đăng tin qua form";
  console.log("\n=== " + group + " ===");
  const sellerCtx = await browser.newContext({ viewport: { width: 1366, height: 860 } });
  const sp = await sellerCtx.newPage();
  const spIssues = [];
  watch(sp, spIssues);
  await step("Trang chủ tải được", async () => {
    await sp.goto(BASE + "/", { waitUntil: "networkidle" });
    await shot(sp, "01-trang-chu");
  });
  await step("Trang Sản phẩm hiển thị ảnh thật (ảnh tải thành công)", async () => {
    await sp.goto(BASE + "/products", { waitUntil: "networkidle" });
    for (let y = 0; y < 8; y++) { await sp.mouse.wheel(0, 900); await sp.waitForTimeout(250); }
    await sp.waitForLoadState("networkidle");
    await sp.waitForTimeout(800);
    const stat = await sp.$$eval("img[src*='/products/image']", (a) => [a.length, a.filter((i) => i.complete && i.naturalWidth > 0).length]);
    await shot(sp, "01b-san-pham");
    if (stat[1] < 20) throw new Error(`chỉ ${stat[1]}/${stat[0]} ảnh tải được`);
    return `${stat[1]}/${stat[0]} ảnh tải thành công`;
  });
  await step("Đăng nhập sai mật khẩu báo lỗi", async () => {
    await sp.goto(BASE + "/login", { waitUntil: "networkidle" });
    await sp.locator("#email").fill(ACC.u04.email);
    await sp.locator("#password").fill("sai-mat-khau");
    await sp.locator("button[type=submit]").first().click();
    await sp.getByText(/không chính xác|sai/i).first().waitFor({ timeout: 10000 });
  });
  await step("Đăng nhập qua form", async () => {
    await sp.locator("#email").fill(ACC.u04.email);
    await sp.locator("#password").fill(PW);
    await sp.locator("button[type=submit]").first().click();
    await sp.waitForURL((u) => !/\/login/.test(u.pathname), { timeout: 20000 });
    return sp.url().replace(BASE, "");
  });
  const title = `Máy tính Casio fx-580 QA${RUN}`;
  await step("Đăng tin bán có ảnh qua form", async () => {
    await sp.goto(BASE + "/products/create", { waitUntil: "networkidle" });
    await sp.getByPlaceholder(/Giáo trình Giải tích/).fill(title);
    await sp.getByPlaceholder(/Mô tả tình trạng/).fill("Máy tính Casio fx-580VN X còn bảo hành, đủ chức năng thi đại học. Giao tại KTX khu B.");
    const cat = sp.locator("select").first();
    await cat.selectOption({ index: 1 });
    await sp.locator("input[type=number]").first().fill("350000");
    await sp.locator('input[type=file][accept*="gif"]').setInputFiles(IMAGE);
    await shot(sp, "02-dang-tin");
    await sp.getByRole("button", { name: /Đăng tin/ }).click();
    await sp.waitForURL(/my-listings/, { timeout: 20000 });
    await sp.getByText(title).first().waitFor({ timeout: 10000 });
    await shot(sp, "03-tin-cua-toi");
    return "chuyển tới Tin của tôi, thấy tin mới";
  });
  await step("Form chặn tiêu đề quá ngắn (kiểm tra phía trình duyệt)", async () => {
    await sp.goto(BASE + "/products/create", { waitUntil: "networkidle" });
    await sp.getByPlaceholder(/Giáo trình Giải tích/).fill("ab");
    await sp.getByRole("button", { name: /Đăng tin/ }).click();
    await sp.waitForTimeout(800);
    if (!/products\/create/.test(sp.url())) throw new Error("form vẫn gửi đi");
    const valid = await sp.getByPlaceholder(/Giáo trình Giải tích/).evaluate((el) => el.validity.valid);
    if (valid) throw new Error("ô tiêu đề vẫn hợp lệ");
  });

  // ------------------------------------------------------------ 2. Tìm kiếm AI trên giao diện
  group = "UI-2. Tìm kiếm AI";
  console.log("\n=== " + group + " ===");
  await step("Tìm kiếm AI 'nồi cơm điện cho phòng ký túc xá'", async () => {
    await sp.goto(BASE + "/products", { waitUntil: "networkidle" });
    const box = sp.getByRole("searchbox").or(sp.getByPlaceholder(/Tìm|tìm/)).first();
    await box.fill("nồi cơm điện cho phòng ký túc xá");
    await box.press("Enter");
    await sp.getByText(/Nồi cơm điện/).first().waitFor({ timeout: 25000 });
    await sp.waitForTimeout(1500);
    await shot(sp, "04-tim-kiem-ai");
    return "kết quả có 'Nồi cơm điện'";
  });

  // ------------------------------------------------------------ 3. Báo cáo tin đăng
  group = "UI-3. Báo cáo tin vi phạm";
  console.log("\n=== " + group + " ===");
  const list = await (await sellerCtx.request.get(BASE + "/api/marketplace/products?limit=50")).json();
  const meId = (await (await sellerCtx.request.get(BASE + "/api/auth/me")).json()).data.id;
  const foreign = list.data.find((p) => p.sellerId !== meId);
  await step("Mở form báo cáo, gửi và nhận xác nhận", async () => {
    await sp.goto(`${BASE}/products/${foreign.id}`, { waitUntil: "networkidle" });
    await sp.getByRole("button", { name: /Báo cáo tin đăng/ }).click();
    await sp.getByRole("form", { name: /Báo cáo tin đăng/ }).locator("select").selectOption({ label: "Lừa đảo / giả mạo" });
    await sp.getByPlaceholder(/Nêu rõ điểm vi phạm/).fill("Ảnh lấy trên mạng, người bán yêu cầu chuyển khoản trước rồi chặn liên lạc.");
    await shot(sp, "05-bao-cao-tin");
    await sp.getByRole("button", { name: /Gửi báo cáo/ }).click();
    await sp.getByText(/Đã gửi báo cáo/).waitFor({ timeout: 10000 });
  });
  await step("Gửi báo cáo lần 2 báo trùng", async () => {
    await sp.reload({ waitUntil: "networkidle" });
    await sp.getByRole("button", { name: /Báo cáo tin đăng/ }).click();
    await sp.getByPlaceholder(/Nêu rõ điểm vi phạm/).fill("Gửi lại báo cáo cho cùng một tin đăng.");
    await sp.getByRole("button", { name: /Gửi báo cáo/ }).click();
    await sp.getByText(/đã báo cáo tin này/i).waitFor({ timeout: 10000 });
  });
  await step("Không hiện nút báo cáo trên tin của chính mình", async () => {
    const mine = list.data.find((p) => p.sellerId === meId);
    if (!mine) return "bỏ qua (không có tin)";
    await sp.goto(`${BASE}/products/${mine.id}`, { waitUntil: "networkidle" });
    if (await sp.getByRole("button", { name: /Báo cáo tin đăng/ }).count()) throw new Error("vẫn hiện nút");
  });

  // ------------------------------------------------------------ 4. Đánh giá người bán
  group = "UI-4. Mua hàng & đánh giá người bán";
  console.log("\n=== " + group + " ===");
  const buyerCtx = await browser.newContext({ viewport: { width: 1366, height: 860 } });
  const btok = await apiLogin(buyerCtx, ACC.u05.email);
  const stok = await apiLogin(sellerCtx, ACC.u04.email);
  const target = list.data.find((p) => p.sellerId === meId && p.type === "sale");
  let txId;
  await step("Chuẩn bị giao dịch hoàn tất (API)", async () => {
    const tx = await api(buyerCtx, btok, "POST", "/api/user/transactions/checkout", { productId: target.id, type: "sale", paymentMethod: "cod", shippingAddress: "Phòng A203 KTX khu A" });
    txId = tx.id;
    await api(sellerCtx, stok, "POST", "/api/user/transactions/status", { id: txId, status: "shipping" });
    await api(sellerCtx, stok, "POST", "/api/user/transactions/status", { id: txId, status: "delivered" });
    return `giao dịch #${txId}`;
  });
  const bp = await buyerCtx.newPage();
  const bpIssues = [];
  watch(bp, bpIssues);
  await step("Người mua bấm 'Xác nhận đã nhận' trên giao diện", async () => {
    await bp.goto(BASE + "/transactions", { waitUntil: "networkidle" });
    await bp.getByRole("button", { name: /Xác nhận đã nhận/ }).first().click();
    await bp.getByText(/Đã cập nhật trạng thái/).waitFor({ timeout: 10000 });
  });
  await step("Gửi đánh giá khi chưa chọn sao bị nhắc", async () => {
    const form = bp.getByRole("form", { name: "Đánh giá người bán" }).first();
    await form.getByRole("button", { name: /Gửi đánh giá/ }).click();
    await form.getByText("Hãy chọn số sao.").waitFor({ timeout: 5000 });
  });
  await step("Chọn 4 sao + nhận xét, gửi đánh giá", async () => {
    const form = bp.getByRole("form", { name: "Đánh giá người bán" }).first();
    await form.getByRole("radio", { name: "4 sao" }).click();
    await form.getByLabel("Nhận xét").fill("Người bán thân thiện, giao đúng hẹn.");
    await shot(bp, "06-danh-gia-nguoi-ban");
    await form.getByRole("button", { name: /Gửi đánh giá/ }).click();
    await bp.getByText(/Bạn đã đánh giá người bán 4/).first().waitFor({ timeout: 10000 });
  });
  await step("Hồ sơ người bán hiển thị đánh giá mới", async () => {
    await bp.goto(`${BASE}/users/${meId}`, { waitUntil: "networkidle" });
    await bp.getByText(/giao đúng hẹn/).first().waitFor({ timeout: 10000 });
    await shot(bp, "07-ho-so-nguoi-ban");
  });

  // ------------------------------------------------------------ 5. XSS
  group = "UI-5. An toàn hiển thị (XSS)";
  console.log("\n=== " + group + " ===");
  const adminCtx = await browser.newContext({ viewport: { width: 1366, height: 860 } });
  const atok = await apiLogin(adminCtx, ADMIN, "admin");
  let dialog = false;
  sp.on("dialog", (d) => { dialog = true; d.dismiss(); });
  await step("Kiểm duyệt AI tự chặn tin chứa mã độc", async () => {
    const created = await sellerCtx.request.post(BASE + "/api/user/products", {
      headers: { "X-CSRF-Token": stok },
      multipart: { title: `<img src=x onerror="window.__xss=1"> XSS QA${RUN}`, description: "<script>window.__xss=2</script> mô tả thử nghiệm XSS đủ dài", category_id: String(list.data[0].categoryId), type: "sale", condition: "used", price: "10000" },
    });
    const body = await created.json();
    if (body.data?.status === "active") throw new Error("tin chứa mã độc được đăng thẳng");
    return `trạng thái tin: ${body.data?.status}`;
  });
  await step("Tiêu đề sản phẩm chứa HTML hiển thị nguyên văn, không thực thi", async () => {
    const victim = list.data.find((p) => p.sellerId !== meId && p.id !== foreign.id);
    execFileSync(MYSQL[0], [...MYSQL.slice(1), "-e", `UPDATE products SET title = CONCAT('<img src=x onerror="window.__xss=1"> ', title) WHERE id = ${victim.id}`]);
    await sp.goto(`${BASE}/products/${victim.id}`, { waitUntil: "networkidle" });
    const flag = await sp.evaluate(() => window.__xss);
    const literal = await sp.getByText('<img src=x onerror="window.__xss=1">').first().isVisible();
    await shot(sp, "22-xss-hien-thi-an-toan");
    if (flag || dialog) throw new Error("script đã chạy!");
    if (!literal) throw new Error("không thấy chuỗi HTML hiển thị dạng chữ");
    return "hiển thị dạng chữ, không chạy";
  });
  await step("Tin nhắn chat chứa HTML không thực thi", async () => {
    const buyerId = (await (await buyerCtx.request.get(BASE + "/api/auth/me")).json()).data.id;
    const other = list.data.find((p) => p.sellerId !== buyerId && p.sellerId !== meId);
    const conv = await api(buyerCtx, btok, "POST", "/api/user/conversations", { productId: other.id });
    await api(buyerCtx, btok, "POST", "/api/chat/send", { conversation_id: conv.id, body: '<img src=x onerror="window.__xss=3"> chào bạn' });
    await bp.goto(`${BASE}/chat/${conv.id}`, { waitUntil: "networkidle" });
    await bp.waitForTimeout(800);
    const flag = await bp.evaluate(() => window.__xss);
    if (flag) throw new Error("script trong tin nhắn đã chạy!");
    await bp.getByText(/chào bạn/).first().waitFor({ timeout: 8000 });
    return "tin nhắn hiển thị an toàn";
  });

  // ------------------------------------------------------------ 6. Dò lỗi mọi trang theo vai trò
  group = "UI-6. Dò lỗi toàn bộ trang (4 vai trò)";
  console.log("\n=== " + group + " ===");
  const PUBLIC = ["/", "/login-role", "/login", "/admin-login", "/register", "/forgot-password", "/products", "/leaderboard", `/products/${foreign.id}`];
  const STUDENT = ["/dashboard", "/profile", "/products", "/products/create", "/my-listings", "/wishlist", "/notifications", "/chat", "/transactions", "/rewards", `/users/${meId}`,
    "/requests", "/residence", "/residence/announcements", "/residence/assistant", "/residence/finance", "/residence/services", "/rentals", "/marketplace/matching"];
  const MANAGER_R = ["/admin/residence", "/admin/residence/dashboard", "/admin/residence/finance", "/admin/residence/operations", "/residence/services"];
  const ADMIN_R = ["/admin", "/admin/products", "/admin/categories", "/admin/banners", "/admin/users", "/admin/reports", "/admin/system-reports", "/admin/ratings",
    "/admin/giveaways", "/admin/audit-log", "/admin/residence", "/admin/marketplace/matching"];
  const mgrCtx = await browser.newContext({ viewport: { width: 1366, height: 860 } });
  await apiLogin(mgrCtx, MANAGER);
  const guestCtx = await browser.newContext({ viewport: { width: 1366, height: 860 } });
  const studentCtx = await browser.newContext({ viewport: { width: 1366, height: 860 } });
  await apiLogin(studentCtx, ACC.u02.email);
  const shotsFor = { "/residence/finance": "08-ktx-hoa-don-sv", "/residence/services": "09-ktx-sua-chua", "/residence/assistant": "10-tro-ly-noi-quy",
    "/admin/residence/finance": "11-ql-tai-chinh", "/admin/residence/dashboard": "12-ql-dashboard", "/admin/residence/operations": "13-ql-van-hanh",
    "/admin": "14-admin-dashboard", "/admin/system-reports": "15-admin-bao-cao", "/admin/ratings": "16-admin-danh-gia", "/admin/marketplace/matching": "17-admin-so-sanh-mo-hinh",
    "/rentals": "18-mat-bang", "/chat": "19-tin-nhan", "/requests": "20-nhu-cau" };
  const crawl = [];
  for (const [label, ctx, routes] of [["khách", guestCtx, PUBLIC], ["sinh viên", studentCtx, STUDENT], ["quản lý KTX", mgrCtx, MANAGER_R], ["admin", adminCtx, ADMIN_R]]) {
    let bad = 0;
    for (const route of routes) {
      const page = await ctx.newPage();
      const issues = [];
      watch(page, issues);
      try {
        await page.goto(BASE + route, { waitUntil: "networkidle", timeout: 90000 });
        await page.waitForTimeout(600);
        const body = await page.locator("body").innerText();
        if (/Unhandled Runtime Error|Application error|This page could not be found/i.test(body)) issues.push("UI hiển thị lỗi");
        if (shotsFor[route] && label !== "khách") await shot(page, shotsFor[route]);
      } catch (e) {
        issues.push("NAV: " + String(e.message).split("\n")[0]);
      }
      const u = [...new Set(issues)];
      crawl.push({ label, route, final: page.url().replace(BASE, ""), issues: u });
      if (u.length) { bad++; console.log(`     ! [${label}] ${route}: ${u.join(" | ")}`); }
      await page.close();
    }
    record(bad === 0, `Vai trò ${label}: ${routes.length} trang không lỗi JS/API 5xx`, bad ? `${bad} trang có vấn đề` : "");
  }
  await step("Sinh viên bị chặn khỏi trang quản trị", async () => {
    const page = await studentCtx.newPage();
    await page.goto(BASE + "/admin/users", { waitUntil: "networkidle" });
    const text = await page.locator("body").innerText();
    const blocked = !/\/admin\/users/.test(page.url()) || /không có quyền|Không thể truy cập|chỉ dành cho|403/i.test(text);
    await page.close();
    if (!blocked) throw new Error("vẫn xem được trang admin");
    return "hiện thông báo: Không thể truy cập cổng quản trị";
  });

  // ------------------------------------------------------------ 7. Điện thoại 390px
  group = "UI-7. Giao diện điện thoại 390px";
  console.log("\n=== " + group + " ===");
  const mobile = await browser.newContext({ viewport: { width: 390, height: 844 }, isMobile: true, hasTouch: true, deviceScaleFactor: 2 });
  await apiLogin(mobile, ACC.u02.email);
  const MOBILE = ["/", "/products", `/products/${foreign.id}`, "/products/create", "/transactions", "/chat", "/residence", "/residence/finance", "/residence/services", "/profile", "/requests"];
  const overflow = [];
  for (const route of MOBILE) {
    const page = await mobile.newPage();
    await page.goto(BASE + route, { waitUntil: "networkidle" });
    await page.waitForTimeout(500);
    const w = await page.evaluate(() => document.documentElement.scrollWidth);
    if (w > 392) overflow.push(`${route} (${w}px)`);
    if (route === "/products" || route === "/residence/finance") await shot(page, "21-mobile" + route.replace(/\//g, "-"));
    await page.close();
  }
  record(overflow.length === 0, `${MOBILE.length} trang không tràn ngang ở 390px`, overflow.join(", "));

  record(spIssues.length + bpIssues.length === 0, "Không có lỗi JS/API 5xx trong các luồng thao tác", [...spIssues, ...bpIssues].slice(0, 4).join(" | "));
  fs.writeFileSync(path.join(OUT, "ui_results.json"), JSON.stringify({ results, crawl }, null, 1));
  const passed = results.filter((r) => r.ok).length;
  console.log(`\nTỔNG UI: ${passed}/${results.length} PASS`);
  await browser.close();
})().catch((e) => { console.error(e); process.exit(1); });
