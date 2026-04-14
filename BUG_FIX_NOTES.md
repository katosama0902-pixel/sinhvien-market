# 🐛 Bug Fix Notes — SinhVienMarket

> Tổng hợp toàn bộ lỗi đã được phát hiện và sửa trong quá trình phát triển dự án.
> Tài liệu này phục vụ mục đích tra cứu, tránh lặp lại lỗi và giúp onboard thành viên mới.
> **Cấu trúc:** Sắp xếp theo tuần phát hiện, từ cũ đến mới.

---

## Mục Lục

| Tuần | Giai đoạn | Số lỗi |
|------|-----------|--------|
| [Tuần 4 (26–29/03)](#-tuần-4-2629032026--ui--devops) | UI & DevOps, Tích hợp Google OAuth | 11 |
| [Tuần 5 (30/03–04/04)](#-tuần-5-3003-04042026--tài-liệu--kiểm-thử) | Tài liệu & Kiểm thử | 1 |
| [Tuần 6 (05–11/04)](#-tuần-6-0511042026--ai-chat--map-dev) | AI Chat & Bot, Smoke Testing | 7 |
| [Tuần 7 (12–18/04)](#-tuần-7-1218042026--thực-chiến-c2c--google-maps) | C2C features, Google Maps Migration | 6 |

---

## 🗓 Tuần 4 (26–29/03/2026) — UI & DevOps

### [BF-001] Lỗi Avatar không hiển thị trên Navbar sau khi cập nhật
- **Ngày phát hiện:** 29/03/2026
- **Mức độ:** 🟡 Medium
- **Mô tả:** Sau khi người dùng tải ảnh đại diện mới lên trang Hồ sơ, ảnh hiển thị đúng trong trang profile nhưng vẫn hiện chữ cái đầu tên ở navbar.
- **Nguyên nhân:** Khi viết Session lúc đăng nhập (`processLogin`, `verifyOtp`), các trường `avatar` và `avatar_url` **không được lưu vào** `$_SESSION['user']`. Navbar sử dụng `$user = $_SESSION['user']` nên không có đường dẫn ảnh để render.
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
- **Mức độ:** 🔴 High
- **Mô tả:** Method `redirect()` trong `GoogleAuthController` xung đột với method `redirect()` đã có trong class cha `Core\Controller`, gây ra redirect không đúng URL.
- **Nguyên nhân:** `GoogleAuthController` định nghĩa lại method `redirect()` nhưng logic tạo URL khác với base Controller.
- **Cách fix:** Đổi tên method trong `GoogleAuthController` từ `redirect()` thành `redirectToGoogle()`. Tất cả các lần redirect nội bộ sử dụng `$this->redirect()` kế thừa từ base `Controller`.
- **Files liên quan:**
  - `app/controllers/GoogleAuthController.php`
  - `core/Controller.php`

---

### [BF-003] Lỗi `SQLSTATE[42S22]: Column not found: 'details'` khi Admin duyệt bài
- **Ngày phát hiện:** 29/03/2026
- **Mức độ:** 🔴 High (Crash khi sử dụng tính năng chính của Admin)
- **Mô tả:** Khi Admin nhấn nút **Duyệt** hoặc **Từ chối** bài đăng, trang bắn lỗi `PDOException` và hành động không được ghi lại.
- **Nguyên nhân:** Bảng `audit_logs` có cột tên là `note`, nhưng code trong `AuditLog::log()` lại INSERT vào cột `details` — không tồn tại.

```sql
-- Schema thực tế trong DB:
CREATE TABLE audit_logs (
    `note` TEXT DEFAULT NULL  -- ✅ Đúng tên
);
```

```php
// AuditLog.php - CODE SAI → ĐÚNG:
'INSERT INTO audit_logs (..., details) ...'  →  '... note ...'
```

- **Cách fix:** Sửa tên cột trong câu INSERT của `AuditLog::log()` từ `details` thành `note`.
- **Files liên quan:**
  - `app/models/AuditLog.php`

---

### [BF-004] Users table thiếu cột `google_id` và `avatar_url`
- **Ngày phát hiện:** 29/03/2026
- **Mức độ:** 🔴 High (Blocker cho tính năng Google Login)
- **Mô tả:** Luồng Google OAuth cần lưu `google_id` và `avatar_url`, nhưng 2 cột này chưa tồn tại trong bảng `users`.
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

### [BF-005] CSS Lint Warning `line-clamp` thiếu property tương thích
- **Ngày phát hiện:** 29/03/2026
- **Mức độ:** 🟢 Low (Chỉ là warning, không ảnh hưởng chức năng)
- **Mô tả:** IDE báo lint warning khi chỉ dùng `-webkit-line-clamp` mà thiếu `line-clamp` chuẩn W3C.
- **Cách fix:** Thêm `line-clamp: N` sau mỗi `-webkit-line-clamp: N`.
- **Files liên quan:**
  - `public/css/style.css`

---

### [BF-006] Nav Avatar không có `overflow: hidden` khiến ảnh tràn ra ngoài vòng tròn
- **Ngày phát hiện:** 29/03/2026
- **Mức độ:** 🟢 Low (Visual bug)
- **Mô tả:** Avatar trong navbar bị tràn ra ngoài khung tròn nếu ảnh có tỷ lệ khác 1:1.
- **Cách fix:** Thêm `overflow: hidden` vào `.nav-avatar` và rule CSS cho `img` bên trong.
- **Files liên quan:**
  - `public/css/style.css`

---

### [BF-007] Giới hạn upload ảnh đại diện chỉ 2MB, cần nâng lên 10MB
- **Ngày phát hiện:** 29/03/2026
- **Mức độ:** 🟡 Medium (Ảnh hưởng UX)
- **Mô tả:** Người dùng không thể tải ảnh > 2MB — quá thấp với ảnh smartphone hiện đại.
- **Cách fix:**
  1. Sửa `ProfileController::uploadAvatar()`: `2 * 1024 * 1024` → `10 * 1024 * 1024`.
  2. Cập nhật text hướng dẫn trong `views/profile/edit.php`.
  3. Điều chỉnh `upload_max_filesize = 12M` trong `php.ini` của Laragon.
- **Files liên quan:**
  - `app/controllers/ProfileController.php`
  - `app/views/profile/edit.php`

---

### [BF-008] Trang Admin duyệt bài crash do lỗi AuditLog
*Đây là lỗi trực tiếp gây ra bởi [BF-003](#bf-003-lỗi-sqlstate42s22-column-not-found-details-khi-admin-duyệt-bài). Đã được fix chung.*

---

### [BF-009] State token CSRF không được xóa sau callback OAuth thành công
- **Ngày phát hiện:** 29/03/2026
- **Mức độ:** 🟡 Medium (Bảo mật)
- **Mô tả:** Sau khi OAuth callback hoàn tất, `$_SESSION['oauth_state']` không được xóa → nguy cơ replay attack.
- **Cách fix:** Thêm `unset($_SESSION['oauth_state'])` ngay sau khi xác minh state thành công trong `GoogleAuthController::callback()`.
- **Files liên quan:**
  - `app/controllers/GoogleAuthController.php`

---

### [BF-010] Nhiều component bị hardcoded màu sáng, không tương thích Dark Mode
- **Ngày phát hiện:** 26–27/03/2026
- **Mức độ:** 🔴 High (Toàn bộ giao diện vỡ khi bật Dark Mode)
- **Mô tả:** Khi bật Dark Mode, nhiều thành phần vẫn hiển thị nền trắng do giá trị `background`, `color`, `border` bị hardcoded thay vì dùng CSS Variables.

| Thành phần | Vấn đề | Fix |
|---|---|---|
| Form inputs / select | `background: #fff` hardcoded | → `var(--card-bg)` |
| Input group, password toggle | `background: #fff` hardcoded | → `var(--card-bg)` |
| Navbar dropdown menu | `background: rgba(255,255,255,.98)` hardcoded | → `var(--bs-dropdown-bg)` |
| `.hp-auction-card`, `.hp-cat-card`, `.hp-product-card` | `background: #fff` hardcoded | → `var(--card-bg)` |
| Database — Crash lần đầu | Thiếu bảng `giveaways`, `reports` và cột lock trong `users` | ALTER TABLE + migrate |

- **Files liên quan:**
  - `public/css/style.css`
  - `app/views/home/index.php`
  - `app/views/layouts/main.php`
  - `database/schema.sql`

---

### [BF-011] Navbar Dropdown bị trắng khi chuyển qua Dark Mode
- **Ngày phát hiện:** 27/03/2026
- **Mức độ:** 🟡 Medium (Visual bug rõ ràng)
- **Mô tả:** Dropdown menu dưới tên người dùng hiển thị nền trắng trong Dark Mode.
- **Nguyên nhân:** Bootstrap 5.3 quản lý màu dropdown qua `--bs-dropdown-bg`. Biến này chưa được override.
- **Cách fix:**
```css
[data-theme="dark"] {
  --bs-dropdown-bg: #1e293b;
  --bs-dropdown-color: #e2e8f0;
  --bs-dropdown-border-color: #334155;
  --bs-dropdown-link-color: #e2e8f0;
  --bs-dropdown-link-hover-bg: #334155;
}
```
- **Files liên quan:**
  - `public/css/style.css`

---

## 🗓 Tuần 5 (30/03–04/04/2026) — Tài liệu & Kiểm thử

### [BF-012] Lỗi hiển thị "Tổng: 0 xu" khi Check-in ở User cũ
- **Ngày phát hiện:** 03/04/2026
- **Mức độ:** 🟡 Medium (Lỗi Logic CSDL)
- **Mô tả:** Khi user check-in, thông báo hiển thị nhận +10 xu nhưng tổng lại là 0 xu.
- **Nguyên nhân:** Cột `coins` mới thêm vào DB. Khi UPDATE qua PDO với `coins = coins + 10` mà coins còn là `NULL`, phép tính trả về `NULL` → parse ra `0`.
- **Cách fix:** Đổi truy vấn từ `coins = coins + X` thành `coins = COALESCE(coins, 0) + X`.
- **Files liên quan:**
  - `app/models/User.php`

---

## 🗓 Tuần 6 (05–11/04/2026) — AI Chat & Map Dev

### [BF-013] Lỗi tin nhắn hiển thị trùng lặp 2 lần khi gửi
- **Ngày phát hiện:** 10/04/2026
- **Mức độ:** 🔴 High (Visual / UX)
- **Mô tả:** Khi nhắn 1 tin, khung chat hiển thị tin nhắn đó 2 lần liên tiếp.
- **Nguyên nhân:** Xung đột giữa PHP render ban đầu và AJAX Polling. Hàm Polling JS lấy nhầm tin nhắn cũ do thiếu cờ định danh ID DOM. Race Condition khi nhấn gửi liên tục.
- **Cách fix:**
  - Thêm thuộc tính `id="msg-{{id}}"` cho từng thẻ tin nhắn.
  - Sửa script để JS dùng `id` này để tránh duplicate.
  - Thêm cờ `isSending` trong JS để khóa nút submit tạm thời. Thêm logic so sánh `$lastMsgId`.
- **Files liên quan:**
  - `app/views/chat/index.php`

---

### [BF-014] Bot AI Trợ lý tự động trả lời Spam mỗi câu nói của khách
- **Ngày phát hiện:** 10/04/2026
- **Mức độ:** 🟡 Medium (Logic Nghiệp vụ)
- **Mô tả:** Bot cướp lời Shop và trả lời liên tục phá hỏng hội thoại.
- **Cách fix:** Xây dựng lại hệ thống theo kiểu "Lazada-style Auto-Responder" với 2 mốc thời gian: `12 tiếng Cooldown` và `5 phút Session`.
- **Files liên quan:**
  - `app/controllers/ChatController.php`
  - `app/models/Message.php`

---

### [BF-015] Lỗi ParseError — Trang Profile không thể truy cập
- **Ngày phát hiện:** 11/04/2026
- **Mức độ:** 🔴 High (Crash trang)
- **Mô tả:** PHP ParseError do block comment thiếu dấu đóng `*/` ở dòng 214 khiến không load được trang Edit Profile.
- **Cách fix:** Xóa block dead code dư thừa (Google Maps/Places API script không hoạt động).
- **Files liên quan:**
  - `app/views/profile/edit.php`

---

### [BF-016] Flash Message bị Escape HTML — Tên user không in đậm trong thông báo Admin
- **Ngày phát hiện:** 11/04/2026
- **Mức độ:** 🟢 Low (Visual/UX)
- **Mô tả:** Thẻ `<strong>` trong câu Flash Message bị chuyển thành text thô.
- **Cách fix:** Bỏ thẻ `<strong>` khỏi text gán cho Flash, chỉ giữ plain text.
- **Files liên quan:**
  - `app/controllers/AdminController.php`

---

### [BF-017] Bộ đếm ngược Đấu giá ngược hiển thị `--:--`
- **Ngày phát hiện:** 11/04/2026
- **Mức độ:** 🟡 Medium (Chức năng cốt lõi)
- **Mô tả:** JS Polling Timer bị chững ở `--:--` và không cập nhật trạng thái 🔒 khi sản phẩm về giá sàn.
- **Cách fix:** Tách code thành hàm `renderTime()` để kích hoạt in thời gian ngay lập tức. Bắt cờ `data.is_at_floor` từ response.
- **Files liên quan:**
  - `app/views/products/detail.php`

---

### [BF-018] Giá sản phẩm đấu giá hiển thị sai (đội lên cực cao) trong Product Listing
- **Ngày phát hiện:** 11/04/2026
- **Mức độ:** 🔴 High (Lỗi tính toán logic hệ thống)
- **Mô tả:** Product List hiển thị giá đấu giá đội lên thay vì giảm.
- **Nguyên nhân:** Lệch múi giờ MySQL/PHP khiến `started_at` trễ hơn thực tế → `elapsedSeconds` âm → `Math.floor(-0.5) * decrease` cho giảm âm → giá bị nâng lên.
- **Cách fix:** `elapsedSeconds = max(0, $now - $startedAt)` để cấm chiều âm.
- **Files liên quan:**
  - `app/models/Auction.php`

---

### [BF-019] Thiếu line break giữa 2 tính năng ở HomeController
- **Ngày phát hiện:** 11/04/2026
- **Mức độ:** 🟢 Low (Format code)
- **Mô tả:** Hàm `dashboard()` viết trực tiếp nối với đuôi ngoặc nhọn của hàm `index()`.
- **Cách fix:** Viết lại đúng format và căn lề theo PSR-12.
- **Files liên quan:**
  - `app/controllers/HomeController.php`

---

## 🗓 Tuần 7 (12–18/04/2026) — Thực Chiến C2C + Google Maps

### [BF-020] Lỗi `Class 'App\Controllers\User' not found` tại trang Chi tiết Sản Phẩm
- **Ngày phát hiện:** 12/04/2026
- **Mức độ:** 🔴 High (Crash khi vào trang)
- **Mô tả:** Trang `/products/show?id=X` bị lỗi fatal khi hiển thị Rank Badge của người bán.
- **Nguyên nhân:** `ProductController::show()` gọi `new User()` nhưng thiếu `use App\Models\User;` ở đầu file. PHP tìm `User` trong namespace `App\Controllers` → báo lỗi.
- **Cách fix:** Bổ sung `use App\Models\User;` vào danh sách import ở đầu `ProductController.php`.
- **Files liên quan:**
  - `app/controllers/ProductController.php`

---

### [BF-021] Trang Hồ sơ người bán luôn báo 404
- **Ngày phát hiện:** 12/04/2026
- **Mức độ:** 🟡 Medium (Tính năng không dùng được)
- **Mô tả:** Bấm vào tên người bán trên trang chi tiết sản phẩm luôn trả về 404.
- **Nguyên nhân:** `RatingController::profile()` có điều kiện `$profile['role'] !== 'student'` — logic không cần thiết, chặn nhầm user hợp lệ.
- **Cách fix:** Xóa điều kiện `$profile['role'] !== 'student'`. Chỉ giữ `!$profile` để xử lý user không tồn tại.
- **Files liên quan:**
  - `app/controllers/RatingController.php`

---

### [BF-022] Google Maps `InvalidKeyMapError` — API Key tạo trong trạng thái form lỗi
- **Ngày phát hiện:** 14/04/2026
- **Mức độ:** 🔴 High (Bản đồ không hiển thị)
- **Mô tả:** Bản đồ trả về lỗi `InvalidKeyMapError` dù key đã được render đúng vào HTML.
- **Nguyên nhân:** API Key được tạo khi form Google Cloud Console đang ở trạng thái lỗi ("API selection required"), khiến key bị lưu sai cấu hình / bị lock ở trạng thái invalid.
- **Cách fix:** Tạo API Key mới hoàn toàn từ đầu khi form ở trạng thái bình thường (không có cảnh báo lỗi).
- **Files liên quan:**
  - `.env` (cập nhật `GOOGLE_MAPS_API_KEY`)

---

### [BF-023] Google Maps `Geocoding Service: This API is not activated`
- **Ngày phát hiện:** 14/04/2026
- **Mức độ:** 🔴 High (Marker không hiển thị đúng vị trí)
- **Mô tả:** Maps JS API load được nhưng `google.maps.Geocoder` trả lỗi `Permission Denied` khi geocode địa chỉ người bán.
- **Nguyên nhân:** Billing đã được link nhưng dự án cần **kích hoạt Geocoding API riêng biệt** trong Google Cloud Console — không phải tự động bật khi có billing.
- **Cách fix:** Chuyển sang kiến trúc **Hybrid** — dùng **Nominatim (OpenStreetMap)** để geocode miễn phí thay vì Google Geocoding API:
```javascript
fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(address))
  .then(r => r.json())
  .then(data => { /* dùng data[0].lat, data[0].lon */ })
```
- **Files liên quan:**
  - `app/views/products/detail.php`

---

### [BF-024] Google Maps `Permission Denied` với Map API Key Ver2
- **Ngày phát hiện:** 14/04/2026
- **Mức độ:** 🔴 High (Bản đồ không load)
- **Mô tả:** API Key mới tạo (`Map API Key Ver2`) bị từ chối với lỗi `Permission Denied` ngay cả khi đặt Application Restrictions về None.
- **Nguyên nhân:** Khi tạo key mà không chọn API restrictions, Google Cloud Console **tự động gán "2 APIs" restriction** với bộ APIs không bao gồm Maps JavaScript API. Key không được phép gọi bất kỳ Maps endpoint nào.
- **Cách fix:** Quay lại dùng **Map API Key gốc** (tạo ngày 12/04) — key này được tạo đúng với chọn Maps JS API + Geocoding API. Đặt Application Restrictions về **None** để cho phép `localhost`.
- **Files liên quan:**
  - `.env` (đổi lại key)
  - Google Cloud Console — Credentials

---

## 📊 Tổng Kết

| Tuần | Giai đoạn | 🔴 High | 🟡 Medium | 🟢 Low | Tổng |
|------|-----------|---------|-----------|--------|------|
| Tuần 4 (26–29/03) | UI & DevOps | 3 | 4 | 3 | 11 |
| Tuần 5 (30/03–04/04) | Tài liệu & Kiểm thử | 0 | 1 | 0 | 1 |
| Tuần 6 (05–11/04) | AI Chat & Map Dev | 3 | 2 | 2 | 7 |
| Tuần 7 (12–18/04) | C2C + Google Maps | 5 | 1 | 0 | 6 |
| **Tổng** | | **11** | **8** | **5** | **24** |

| Mức độ | Số lượng | Trạng thái |
|--------|----------|------------|
| 🔴 High (Crash/Blocker) | 11 | ✅ Đã fix tất cả |
| 🟡 Medium | 8 | ✅ Đã fix tất cả |
| 🟢 Low (Visual/Warning) | 5 | ✅ Đã fix tất cả |
| **Tổng** | **24 bugs** | **100% resolved** |

---

> **Lưu ý cho maintainer:** Nếu phát hiện lỗi mới, hãy thêm vào file này theo đúng format `[BF-XXX]` và đặt vào đúng mục Tuần để đảm bảo truy xuất được lỗi khi cần.
