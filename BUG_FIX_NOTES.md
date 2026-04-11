# 🐛 Bug Fix Notes — SinhVienMarket

> Tổng hợp toàn bộ lỗi đã được phát hiện và sửa trong quá trình phát triển dự án.
> Tài liệu này phục vụ mục đích tra cứu, tránh lặp lại lỗi và giúp onboard thành viên mới.

---

## Mục Lục
1. [Authentication & Session](#1-authentication--session)
2. [Database & Model](#2-database--model)
3. [Giao diện & CSS](#3-giao-diện--css)
4. [Upload & File Handling](#4-upload--file-handling)
5. [Admin Panel](#5-admin-panel)
6. [Google OAuth](#6-google-oauth)

---

## 1. Authentication & Session

### [BF-001] Lỗi Avatar không hiển thị trên Navbar sau khi cập nhật
- **Ngày phát hiện:** 29/03/2026
- **Mức độ:** Medium
- **Mô tả:** Sau khi người dùng tải ảnh đại diện mới lên trang Hồ sơ, ảnh hiển thị đúng trong trang profile nhưng vẫn hiện chữ cái đầu tên ở navbar.
- **Nguyên nhân:** Khi viết `Session` lúc đăng nhập (`processLogin`, `verifyOtp`), các trường `avatar` và `avatar_url` **không được lưu vào** `$_SESSION['user']`. Navbar sử dụng `$user = $_SESSION['user']` nên không có đường dẫn ảnh để render.
- **Cách fix:**
  - Bổ sung `avatar` và `avatar_url` vào mảng session trong tất cả các điểm login: `AuthController::processLogin()`, `AuthController::verifyOtp()`, `GoogleAuthController::loginUser()`.
  - Thêm đồng bộ session trong `ProfileController::show()` — cứ mỗi lần vào trang hồ sơ, hệ thống sẽ refresh lại ảnh từ DB vào session.
  - Thêm `$_SESSION['user']['avatar'] = 'avatars/' . $filename` ngay sau khi upload thành công trong `ProfileController::uploadAvatar()`.
  - Cập nhật `main.php` để ưu tiên hiển thị `$user['avatar']` (ảnh upload) hoặc `$user['avatar_url']` (ảnh Google) thay vì chỉ hiện initials.
- **Files liên quan:**
  - `app/controllers/AuthController.php`
  - `app/controllers/ProfileController.php`
  - `app/controllers/GoogleAuthController.php`
  - `app/views/layouts/main.php`
  - `public/css/style.css`

---

### [BF-002] Lỗi Redirect vòng lặp (Loop) trong GoogleAuthController
- **Ngày phát hiện:** 29/03/2026
- **Mức độ:** High
- **Mô tả:** Method `redirect()` trong `GoogleAuthController` xung đột với method `redirect()` đã có trong class cha `Core\Controller`, gây ra redirect không đúng URL.
- **Nguyên nhân:** `GoogleAuthController` định nghĩa lại method `redirect()` nhưng logic tạo URL khác với base Controller.
- **Cách fix:** Đổi tên method trong `GoogleAuthController` từ `redirect()` thành `redirectToGoogle()`. Tất cả các lần redirect nội bộ (về `/login`, `/`) sử dụng `$this->redirect()` kế thừa từ base `Controller`.
- **Files liên quan:**
  - `app/controllers/GoogleAuthController.php`
  - `core/Controller.php`

---

## 2. Database & Model

### [BF-003] Lỗi `SQLSTATE[42S22]: Column not found: 'details'` khi Admin duyệt bài
- **Ngày phát hiện:** 29/03/2026
- **Mức độ:** High (Crash khi sử dụng tính năng chính của Admin)
- **Mô tả:** Khi Admin nhấn nút **Duyệt** hoặc **Từ chối** bài đăng, trang bắn lỗi `PDOException: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'details' in 'field list'` và hành động không được ghi lại.
- **Nguyên nhân:** Bảng `audit_logs` trong database có cột tên là `note`, nhưng code trong `AuditLog::log()` lại INSERT vào cột tên là `details` — không tồn tại.

```sql
-- Schema thực tế trong DB:
CREATE TABLE audit_logs (
    `note` TEXT DEFAULT NULL  -- ✅ Đúng tên
);
```

```php
// AuditLog.php - CODE SAI (trước khi fix):
'INSERT INTO audit_logs (..., details) VALUES (...)'

// AuditLog.php - CODE ĐÚNG (sau khi fix):
'INSERT INTO audit_logs (..., note) VALUES (...)'
```

- **Cách fix:** Sửa tên cột trong câu INSERT của `AuditLog::log()` từ `details` thành `note`.
- **Files liên quan:**
  - `app/models/AuditLog.php`

---

### [BF-004] Users table thiếu cột `google_id` và `avatar_url`
- **Ngày phát hiện:** 29/03/2026
- **Mức độ:** High (Blocker cho tính năng Google Login)
- **Mô tả:** Luồng Google OAuth cần lưu `google_id` để nhận diện người dùng và `avatar_url` để lấy ảnh từ tài khoản Google, nhưng 2 cột này chưa tồn tại trong bảng `users`.
- **Cách fix:** Chạy migration ALTER TABLE để bổ sung 2 cột:
```sql
ALTER TABLE users
  ADD COLUMN google_id   VARCHAR(100) NULL UNIQUE AFTER password,
  ADD COLUMN avatar_url  VARCHAR(255) NULL AFTER google_id;
```
- **Files liên quan:**
  - `database/schema.sql`
  - `app/models/User.php` (thêm 3 methods: `findByGoogleId`, `createFromGoogle`, `linkGoogle`)

---

## 3. Giao diện & CSS

### [BF-005] CSS Lint Warning `line-clamp` thiếu property tương thích
- **Ngày phát hiện:** 29/03/2026
- **Mức độ:** Low (Chỉ là warning, không ảnh hưởng chức năng)
- **Mô tả:** IDE báo lint warning: *"Also define the standard property 'line-clamp' for compatibility"* tại 2 vị trí trong `style.css`.
- **Nguyên nhân:** Code chỉ sử dụng `-webkit-line-clamp` (prefix cũ) mà không có `line-clamp` (property chuẩn W3C mới).
- **Cách fix:** Thêm `line-clamp: N` sau mỗi `-webkit-line-clamp: N`.
```css
/* Trước */
-webkit-line-clamp: 2;

/* Sau */
-webkit-line-clamp: 2;
line-clamp: 2;  /* + Thêm dòng này */
```
- **Files liên quan:**
  - `public/css/style.css` (dòng ~754, ~789)

---

### [BF-006] Nav Avatar không có `overflow: hidden` khiến ảnh tràn ra ngoài vòng tròn
- **Ngày phát hiện:** 29/03/2026
- **Mức độ:** Low (Visual bug)
- **Mô tả:** Khi avatar được hiển thị trong navbar, nếu ảnh có tỷ lệ khác 1:1 thì ảnh bị tràn ra ngoài khung tròn.
- **Cách fix:** Thêm `overflow: hidden` vào `.nav-avatar` và thêm rule CSS cho `img` bên trong:
```css
.nav-avatar {
  overflow: hidden; /* + Thêm */
}
.nav-avatar img {
  width: 100%; height: 100%; object-fit: cover; /* + Thêm */
}
```
- **Files liên quan:**
  - `public/css/style.css`

---

## 4. Upload & File Handling

### [BF-007] Giới hạn upload ảnh đại diện chỉ 2MB, cần nâng lên 10MB
- **Ngày phát hiện:** 29/03/2026
- **Mức độ:** Medium (Ảnh hưởng UX)
- **Mô tả:** Người dùng không thể tải ảnh đại diện có dung lượng lớn hơn 2MB. Với ảnh chụp từ smartphone hiện đại (thường > 3MB), đây là hạn chế không hợp lý.
- **Cách fix:**
  1. Sửa kiểm tra kích thước trong `ProfileController::uploadAvatar()`: `2 * 1024 * 1024` → `10 * 1024 * 1024`.
  2. Cập nhật text hướng dẫn trong `views/profile/edit.php`: "Tối đa 2MB" → "Tối đa 10MB".
  3. *(Cần làm thêm)* Điều chỉnh `upload_max_filesize = 12M` và `post_max_size = 12M` trong `php.ini` của Laragon.
- **Files liên quan:**
  - `app/controllers/ProfileController.php`
  - `app/views/profile/edit.php`

---

## 5. Admin Panel

### [BF-008] Trang Admin duyệt bài crash do lỗi AuditLog (xem BF-003)

*Đây là lỗi trực tiếp gây ra bởi [BF-003](#bf-003-lỗi-sqlstate42s22-column-not-found-details-khi-admin-duyệt-bài). Đã được fix chung.*

---

## 6. Google OAuth

### [BF-009] State token CSRF không được xóa sau callback thành công
- **Ngày phát hiện:** 29/03/2026
- **Mức độ:** Medium (Bảo mật)
- **Mô tả:** Sau khi OAuth callback hoàn tất, `$_SESSION['oauth_state']` cần được xóa để tránh token bị tái sử dụng (replay attack).
- **Cách fix:** Thêm `unset($_SESSION['oauth_state'])` ngay sau khi xác minh state thành công trong `GoogleAuthController::callback()`.
- **Files liên quan:**
  - `app/controllers/GoogleAuthController.php`

---

## 7. Dark Mode (v1.1.0 — 27/03/2026)

### [BF-010] Nhiều component bị hardcoded màu sáng, không tương thích Dark Mode

- **Ngày phát hiện:** 26–27/03/2026
- **Mức độ:** High (Toàn bộ giao diện vỡ khi bật Dark Mode)
- **Mô tả:** Khi người dùng bật Dark Mode, nhiều thành phần giao diện vẫn hiển thị nền trắng/sáng do các giá trị `background`, `color`, `border` bị viết cứng (hardcoded) thay vì dùng CSS Variables.

#### A. CSS Global (`public/css/style.css`) — Form & Input

| Thành phần | Vấn đề | Fix |
|---|---|---|
| Form inputs / select | `background: #fff` hardcoded | → `var(--card-bg)` |
| Input group, password toggle | `background: #fff` hardcoded | → `var(--card-bg)` |
| Navbar dropdown menu | `background: rgba(255,255,255,.98)` hardcoded | → `var(--bs-dropdown-bg)` |
| Bootstrap dropdown | Thiếu CSS variable override | Thêm `--bs-dropdown-bg`, `--bs-dropdown-color` vào `[data-theme="dark"]` |
| Auth card (login/register) | Nền trắng trong dark mode | Thêm dark mode override cho `.auth-card` |

**Cách fix:**
```css
/* Trước — Hardcoded */
.form-control { background: #fff; }
.navbar-main .dropdown-menu { background: rgba(255,255,255,.98); }

/* Sau — Dùng CSS Variable */
.form-control { background: var(--card-bg); }
[data-theme="dark"] {
  --bs-dropdown-bg: var(--card);
  --bs-dropdown-color: var(--text);
}
```

#### B. Trang chủ (`app/views/home/index.php`) — Inline Styles

| Thành phần | Vấn đề | Fix |
|---|---|---|
| Section "Đấu giá HOT" | `background: #fff` inline style | → `var(--bg)` |
| Section "Danh mục" | `background: linear-gradient(#f8fafc...)` inline | → Class `.hp-category-section` với dark override |
| Section "Sản phẩm mới nhất" | `background: #fff` inline style | → `var(--card-bg)` |
| `.hp-auction-card` | `background: #fff` hardcoded | → `var(--card-bg)`, `border: var(--border)` |
| `.hp-cat-card` | `background: #fff` hardcoded | → `var(--card-bg)`, `border: var(--border)` |
| `.hp-product-card` | `background: #fff` hardcoded | → `var(--card-bg)`, `border: var(--border)` |
| Text màu card title | `color: #0f172a` hardcoded | → `var(--text)` |

#### C. Database Schema — Lỗi 500 crash khi bật Dark Mode lần đầu

| Lỗi | Nguyên nhân | Fix |
|---|---|---|
| `500` toàn app | Thiếu bảng `giveaways` và `giveaway_participants` | Thêm vào `schema.sql` + migrate DB |
| `500` trang Admin Users | Thiếu cột `lock_reason`, `locked_at`, `locked_until` trong bảng `users` | `ALTER TABLE` + cập nhật schema |
| `500` trang Tố cáo | Thiếu bảng `reports` | Thêm vào `schema.sql` + migrate DB |

- **Files liên quan:**
  - `public/css/style.css`
  - `app/views/home/index.php`
  - `app/views/layouts/main.php`
  - `database/schema.sql`

---

### [BF-011] Navbar Dropdown bị trắng khi chuyển qua Dark Mode
- **Ngày phát hiện:** 27/03/2026
- **Mức độ:** Medium (Visual bug rõ ràng)
- **Mô tả:** Dropdown menu dưới tên người dùng (User Menu) và dropdown thông báo hiển thị nền trắng khi Dark Mode được bật, text bị mất do màu sắc tương phản thấp.
- **Nguyên nhân:** Bootstrap 5.3 quản lý màu dropdown qua biến `--bs-dropdown-bg` và `--bs-dropdown-color`. Các biến này chưa được override trong phạm vi `[data-theme="dark"]`.
- **Cách fix:**
```css
[data-theme="dark"] {
  --bs-dropdown-bg: #1e293b;           /* Nền dropdown tối */
  --bs-dropdown-color: #e2e8f0;        /* Chữ sáng */
  --bs-dropdown-border-color: #334155; /* Viền tối */
  --bs-dropdown-link-color: #e2e8f0;
  --bs-dropdown-link-hover-bg: #334155;
}
```
- **Files liên quan:**
  - `public/css/style.css`

---

### [BF-012] Lỗi hiển thị "Tổng: 0 xu" khi Check-in ở User cũ
- **Ngày phát hiện:** 03/04/2026
- **Mức độ:** Medium (Lỗi Logic CSDL)
- **Mô tả:** Khi user check-in, thông báo hiển thị nhận +10 xu nhưng tổng lại là 0 xu, thay vì 10.
- **Nguyên nhân:** Cột `coins` mới được thêm vào DB với dạng có default là 0 qua quá trình migration. Khi có dữ liệu trống hoặc truy vấn UPDATE qua PDO thao tác `coins = coins + 10` với giá trị rỗng/không tương thích, phép tính trả về rỗng `NULL` hoặc được Model parse thành `0` gây hiển thị sai lệch.
- **Cách fix:** Đổi truy vấn cập nhật trong model `User.php` từ `coins = coins + X` thành phương thức bắt an toàn `coins = COALESCE(coins, 0) + X`.
- **Files liên quan:**
  - `app/models/User.php`

---

## 8. Real-time Chat & AI Assistant (v1.2.0 — 10/04/2026)

### [BF-013] Lỗi tin nhắn hiển thị trùng lặp 2 lần khi gửi
- **Ngày phát hiện:** 10/04/2026
- **Mức độ:** High (Visual / UX)
- **Mô tả:** Khi nhắn 1 tin, khung chat hiển thị tin nhắn đó 2 lần liên tiếp.
- **Nguyên nhân:** Xung đột giữa PHP render ban đầu và AJAX Polling chập chờn. Hàm Polling JavaScript lấy nhầm tin nhắn cũ do thiếu cờ định danh ID DOM. Đồng thời có biểu hiện của Race Condition khi người dùng nhấn gửi liên tục.
- **Cách fix:** 
  - Thêm thuộc tính `id="msg-{{id}}"` cho từng thẻ tin nhắn render từ PHP.
  - Sửa hàm script trong `chat/index.php` để JS lấy `id` này.
  - Cập nhật logic cắm cờ `isSending` trong JS để khóa nút submit tạm thời tránh ấn đúp. Thêm logic so sánh `$lastMsgId`.
- **Files liên quan:**
  - `app/views/chat/index.php`

---

### [BF-014] Bot AI Trợ lý tự động trả lời Spam mỗi câu nói của khách
- **Ngày phát hiện:** 10/04/2026
- **Mức độ:** Medium (Logic Nghiệp vụ)
- **Mô tả:** Bot tự trả lời mỗi khi khách có dấu "?" hoặc nhắn chữ "còn". Điều này khiến Bot cướp lời Shop và nói luyên thuyên phá hỏng hội thoại.
- **Cách fix:** Xây dựng lại hệ thống "Lazada-style Auto-Responder".
  - Thêm hàm `getLastMessageTimeBySender` trong Model.
  - Áp dụng 2 mốc thời gian: `12 tiếng Cooldown` (ẩn nếu shop vừa rep) và `5 phút Session` (ngăn Bot bắt lời nhiều tin nhắn liên tiếp).
- **Files liên quan:**
  - `app/controllers/ChatController.php`
  - `app/models/Message.php`

---

## 9. Kiểm Thử Toàn Diện (v1.3.0 — 11/04/2026)

> Phiên test thực hiện web testing toàn bộ tính năng Admin + User, Light Mode + Dark Mode.
> Tài khoản test: `admin@market.com` / `Admin@123` (admin), `katosama0902@gmail.com` / `Admin@123` (sinh viên)

### [BF-015] Lỗi ParseError — Trang Profile không thể truy cập

- **Ngày phát hiện:** 11/04/2026
- **Mức độ:** Critical (Trang hỏng hoàn toàn)
- **Mô tả:** Khi sinh viên truy cập `/profile`, hệ thống bắn lỗi PHP `ParseError: Unterminated comment starting line 214`. Toàn bộ trang hồ sơ cá nhân không thể load — đây là tính năng core của ứng dụng.
- **Nguyên nhân:** Trong file `app/views/profile/edit.php`, dòng 214 có một PHP comment block không được đóng:
```php
// Dòng 214 — SAI:
<?php /* if ($tab === 'general'): ?>

// Phải là:
<?php /* if ($tab === 'general'): */ ?>
```
Comment dạng `/* ... */` bị để hở — không có `*/` đóng. PHP parse fail toàn bộ file.
- **Cách fix:** Thêm `*/` để đóng comment đúng:
```php
<?php /* if ($tab === 'general'): */ ?>
```
Hoặc xóa toàn bộ block dead code từ dòng 214 đến 227 (code Google Maps API đã bị comment ra, vĩnh viễn không dùng).
- **Files liên quan:**
  - `app/views/profile/edit.php` (dòng 214)

---

### [BF-016] Flash Message bị Escape HTML — Tên user không in đậm trong thông báo Admin

- **Ngày phát hiện:** 11/04/2026
- **Mức độ:** Low (Visual/UX)
- **Mô tả:** Khi Admin khóa/mở khóa tài khoản, thông báo hiển thị text thô như `✅ Đã khóa tài khoản <strong>Kagato</strong> trong 3 ngày.` thay vì in đậm tên user.
- **Nguyên nhân:** `Flash::render()` trong `core/Flash.php` (dòng 42) escape toàn bộ nội dung message bằng `htmlspecialchars()`, khiến HTML tags bị hiển thị thay vì render. Trong khi `AdminController::toggleUser()` (dòng 178) truyền HTML có `<strong>` vào Flash message.
- **Nguyên nhân gốc:** Không nhất quán: Flash message được thiết kế cho plain text, nhưng AdminController truyền HTML.
- **Cách fix:** Chọn một trong hai:
  1. *(Đơn giản)* Sửa `AdminController` để không dùng HTML trong Flash message:
  ```php
  // Trước (dòng 178):
  Flash::set('success', "✅ Đã khóa tài khoản <strong>{$user['name']}</strong> trong $durationLabel.");
  // Sau:
  Flash::set('success', "✅ Đã khóa tài khoản {$user['name']} trong $durationLabel.");
  ```
  2. *(Mở rộng)* Thêm tham số `$allowHtml` vào `Flash::set()` và chỉ escape khi cần thiết.
- **Files liên quan:**
  - `core/Flash.php` (dòng 42)
  - `app/controllers/AdminController.php` (dòng 178, 185)

---

### [BF-017] Bộ đếm ngược Đấu giá ngược hiển thị `--:--` thay vì thời gian thực

- **Ngày phát hiện:** 11/04/2026
- **Mức độ:** Medium (UX — tính năng core không hoạt động đúng)
- **Mô tả:** Trên trang chi tiết sản phẩm đấu giá (ví dụ `/products/show?id=3`), đồng hồ đếm ngược "Giảm tiếp theo sau" hiển thị `--:--` thay vì thời gian thực tế đến lần giảm giá kế tiếp.
- **Nguyên nhân:** Countdown timer trong view `products/detail.php` dùng JavaScript để tính toán thời gian, nhưng khi sản phẩm đã đạt giá sàn (`is_at_floor = true`), biến `next_drop_in_seconds` có thể bị tính sai hoặc JavaScript không phân biệt được trường hợp giá đạt sàn. Cần kiểm tra điều kiện render trong view.
- **Nguyên nhân bổ sung:** Có thể liên quan đến timezone server (UTC) vs browser (UTC+7) gây tính sai `started_at`.
- **Cách fix:**
  1. Kiểm tra `app/views/products/detail.php` — điều kiện hiển thị countdown.
  2. Nếu `is_at_floor = true`, hiển thị "Đã đạt giá sàn" thay vì countdown.
  3. Đảm bảo `started_at` được truyền đúng định dạng ISO 8601 sang JavaScript để tránh timezone mismatch.
- **Files liên quan:**
  - `app/views/products/detail.php`
  - `app/models/Auction.php` (hàm `calculateCurrentPrice`)

---

### [BF-018] Giá sản phẩm đấu giá hiển thị sai trong Product Listing

- **Ngày phát hiện:** 11/04/2026
- **Mức độ:** High (Logic nghiệp vụ sai — gây hiểu nhầm)
- **Mô tả:** Trong trang danh sách sản phẩm `/products`, sản phẩm đấu giá (product ID 3) hiển thị giá `255.000đ` thay vì giá khởi điểm `80.000đ` hoặc giá hiện tại tính theo thời gian thực.
  - DB: `start_price = 80,000`, `floor_price = 30,000`, `price = NULL`
  - Trên screen: hiển thị `255.000đ` (không khớp với bất kỳ field nào trong auction logic)
- **Nguyên nhân:** Model `Product::getActive()` JOIN với bảng `auctions` nhưng query có thể lấy thêm cột `price` từ các sản phẩm khác, hoặc `current_price` được tính sai do `started_at` không được SELECT trong query `getActive()`. Cần kiểm tra `Product::getActive()` có SELECT `started_at` và `auction_status` không.
- **Cách fix:**
  1. Đảm bảo query `Product::getActive()` bao gồm `a.started_at`, `a.status as auction_status`, `a.start_price`, `a.floor_price`, `a.decrease_amount`, `a.step_minutes`.
  2. Nếu `product.type = 'auction'` mà `started_at` là NULL → không gọi `calculateCurrentPrice`.
  3. Xác minh không có dữ liệu rác trong DB gây ra giá 255,000đ.
- **Files liên quan:**
  - `app/models/Product.php` (method `getActive`)
  - `app/controllers/ProductController.php` (dòng 59-65)

---

### [BF-019] HomeController — Hàm `index()` và `dashboard()` thiếu line break, dễ gây nhầm lẫn

- **Ngày phát hiện:** 11/04/2026
- **Mức độ:** Low (Code Quality)
- **Mô tả:** Trong `app/controllers/HomeController.php` dòng 74, hàm `dashboard()` bắt đầu ngay sau dấu `}` đóng của hàm `index()` trên cùng một dòng — không có newline. Đây là vấn đề code style nhưng không ảnh hưởng runtime.
```php
// Dòng 74 — hiện tại:
        ]);
    }    public function dashboard(): void
// Phải là:
        ]);
    }

    public function dashboard(): void
```
- **Cách fix:** Thêm dòng trống giữa hai hàm để dễ đọc.
- **Files liên quan:**
  - `app/controllers/HomeController.php` (dòng 74)

---

## 📊 Tổng Kết

| # | Mức độ | Số lượng | Trạng thái |
|---|--------|----------|------------|
| 🔴 | Critical (Crash/Blocker) | 6 | 🛠️ BF-015, BF-018 cần fix |
| 🟡 | Medium | 7 | 🛠️ BF-017 cần fix; còn lại ✅ |
| 🟢 | Low (Visual/Warning/Quality) | 6 | 🛠️ BF-016, BF-019 cần fix |
| | **Tổng** | **19 bugs** | **14 đã resolved / 5 mới phát hiện** |

---

> **Lưu ý cho maintainer:** Nếu phát hiện lỗi mới, hãy thêm vào file này theo đúng format `[BF-XXX]` để đảm bảo truy xuất được lỗi khi cần.

