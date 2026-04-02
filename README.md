<div align="center">

# 🏪 SinhVienMarket

**Nền tảng mua bán, trao đổi và đấu giá đồ dùng dành riêng cho sinh viên ký túc xá**

[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)

[🌐 Demo](#) · [📋 Báo cáo tiến độ](#-báo-cáo-tiến-độ) · [🐛 Báo lỗi](../../issues)

</div>

---

## 📖 Giới Thiệu

**SinhVienMarket** là nền tảng thương mại điện tử nội bộ được thiết kế riêng cho cộng đồng sinh viên ký túc xá — nơi việc trao tay đồ trực tiếp là hoàn toàn khả thi, giúp loại bỏ chi phí vận chuyển và các rào cản không cần thiết của các sàn thương mại lớn.

### 🎯 Vấn Đề Cần Giải Quyết

| Vấn đề hiện tại | Giải pháp của SinhVienMarket |
|---|---|
| Rào cản đăng bán phức tạp trên Shopee/Lazada | Đăng bán đơn giản, không cần xác minh cửa hàng |
| Phí ship ngang giá món đồ (sách 20k, ship 25k) | Trao tay trực tiếp trong khuôn viên KTX |
| Không có cộng đồng mua bán nội bộ an toàn | Hệ thống kiểm duyệt bài đăng bởi Admin |
| Giao dịch tự phát qua Facebook/Zalo dễ lừa đảo | Rating, tố cáo vi phạm, nhật ký hành động |

---

## ✨ Tính Năng Chính

### 🛍️ Mua Bán & Giao Dịch
- **3 hình thức giao dịch:** Bán thông thường / Đấu giá ngược (Reverse Auction) / Trao đổi hiện vật
- **Đấu giá ngược (Dutch Auction):** Giá tự động giảm dần theo thời gian, người mua nhấn "Lock & Buy" để chốt đơn
- **Order Tracking:** Theo dõi đơn hàng qua 4 giai đoạn (Chờ xác nhận → Đang giao → Đã giao → Hoàn tất)
- **Wishlist:** Lưu sản phẩm yêu thích để theo dõi sau

### 🔐 Xác Thực & Bảo Mật
- **Đăng ký / Đăng nhập** qua Email & Mật khẩu với OTP 2 lớp
- **Google OAuth 2.0** — Đăng nhập nhanh bằng tài khoản Google
- **Recent Accounts** — Ghi nhớ tài khoản đã đăng nhập để truy cập nhanh
- **Quên mật khẩu** qua câu hỏi bảo mật cá nhân
- CSRF Protection, Password Bcrypt, XSS Escaping toàn bộ output

### 💬 Tương Tác Cộng Đồng
- **Chat realtime** giữa người mua và người bán (Polling 3s)
- **Thông báo trong app** — Bell badge cập nhật tin nhắn/duyệt bài/đấu giá
- **Đánh giá người bán** (1–5 sao + nhận xét sau giao dịch)
- **Tố cáo vi phạm** — Báo cáo sản phẩm hoặc tài khoản đáng ngờ
- **Sự kiện Giveaway** — Bốc thăm may mắn trúng quà

### 🛡️ Admin Panel
- Dashboard thống kê tổng quan (Users, Products, Transactions)
- Kiểm duyệt bài đăng (Duyệt / Từ chối với thông báo tự động)
- Quản lý người dùng (Khóa/Mở khóa tài khoản)
- Nhật ký hành động Admin (Audit Log — không thể xóa/sửa)
- Quản lý danh mục, sự kiện Giveaway, báo cáo vi phạm

### 🎨 Giao Diện
- **Dark Mode / Light Mode** — Toggle toàn trang, lưu `localStorage`
- Responsive Mobile (Bootstrap 5.3)
- Trang lỗi 404 & 500 thân thiện với người dùng
- Font Plus Jakarta Sans + Bootstrap Icons

---

## 🗄️ Cấu Trúc Database

```
users                 — Tài khoản sinh viên & Admin (hỗ trợ Google ID)
products              — Sản phẩm đăng bán (sale / auction / exchange)
categories            — Danh mục sản phẩm
transactions          — Lịch sử giao dịch & trạng thái vận chuyển
messages              — Tin nhắn chat
conversations         — Quản lý hội thoại
notifications         — Thông báo đẩy trong app
ratings               — Đánh giá người bán
reports               — Tố cáo vi phạm
wishlists             — Danh sách yêu thích
giveaways             — Sự kiện giveaway
giveaway_participants — Người tham gia giveaway
audit_logs            — Nhật ký hành động Admin
otp_codes             — Mã OTP xác thực
password_resets       — Đặt lại mật khẩu
```

---

## 🏗️ Kiến Trúc Dự Án

Dự án sử dụng kiến trúc **MVC thuần PHP** — không phụ thuộc framework bên ngoài.

```
sinhvien-market/
├── app/
│   ├── controllers/   # 15+ Controllers (Auth, Product, Chat, Admin...)
│   ├── models/        # 10+ Models (User, Product, Transaction...)
│   ├── views/         # 30+ Views (layouts, pages, errors)
│   └── services/      # NotificationService, ...
├── core/
│   ├── Router.php     # URL Dispatcher
│   ├── Controller.php # Base Controller (CSRF, Auth helpers)
│   ├── Model.php      # Base Model (PDO wrapper)
│   ├── ErrorHandler.php
│   └── Middleware.php
├── config/
│   └── Database.php   # Singleton PDO connection
├── database/
│   └── schema.sql     # Full database schema (15+ bảng)
├── public/
│   ├── css/style.css  # Design System (CSS Variables, Dark Mode)
│   ├── uploads/       # Ảnh sản phẩm & Avatar
│   └── index.html
├── storage/logs/      # Log file tự động theo ngày
├── index.php          # Entry point duy nhất
└── .env               # Cấu hình môi trường
```

### Luồng Xử Lý Yêu Cầu (Request Lifecycle)

```
Browser Request
    └─→ .htaccess (Rewrite → index.php)
           └─→ Router::dispatch() (Phân tích URL + HTTP Method)
                  └─→ Middleware::requireAuth/requireAdmin() (Kiểm tra quyền)
                         └─→ Controller::action() (Logic nghiệp vụ)
                                └─→ Model::query() (PDO → MySQL)
                                       └─→ Controller::render(View, Data)
                                              └─→ Layout + View → HTML Response
```

---

## 🚀 Cài Đặt & Chạy Local

### Yêu Cầu
- [Laragon](https://laragon.org) (hoặc XAMPP)
- PHP >= 8.1
- MySQL >= 8.0

### Các Bước

**1. Clone repository**
```bash
git clone https://github.com/your-username/sinhvien-market.git
cd sinhvien-market
```

**2. Tạo cơ sở dữ liệu**
```sql
CREATE DATABASE sinhvien_market CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
Sau đó import file `database/schema.sql` vào database vừa tạo.

**3. Cấu hình môi trường**
```bash
cp .env.example .env
```
Mở file `.env` và điền thông tin:
```env
APP_URL=http://localhost:8080/sinhvien-market
APP_DEBUG=true

DB_HOST=127.0.0.1
DB_NAME=sinhvien_market
DB_USER=root
DB_PASS=

MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_FROM=your_email@gmail.com

GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8080/sinhvien-market/auth/google/callback
```

**4. Khởi động Laragon và truy cập**
```
http://localhost:8080/sinhvien-market
```

---

## 🔑 Tài Khoản Mặc Định

| Role | Email | Mật khẩu |
|------|-------|----------|
| Admin | admin@sinhvienmarket.com | *(xem trong schema.sql)* |
| Student | *(tự đăng ký)* | *(OTP qua email)* |

---

## 📈 Trạng Thái Dự Án

| Tiêu chí | Điểm | Trạng thái |
|----------|------|------------|
| Project Structure (MVC) | 8/10 | 🟢 Đạt |
| API Conventions | 9/10 | 🟢 Đạt |
| Error Handling | 8.5/10 | 🟢 Đạt |
| Database Design | 7/10 | 🟡 Khá |
| Security | 8/10 | 🟢 Đạt |
| Code Style | 7/10 | 🟡 Khá |
| Testing (Automated) | 2/10 | 🔴 Chưa có |
| Git Workflow | 7/10 | 🟡 Khá |

**Điểm trung bình: 7.1/10**

---

## 🗺️ Roadmap

- [ ] Bộ lọc sản phẩm nâng cao (khoảng giá, tình trạng hàng)
- [ ] Live Search (hiển thị kết quả khi đang gõ)
- [ ] Rate Limiting (chống spam login/OTP)
- [ ] Wishlist Alerts (thông báo khi giá giảm)
- [ ] Unit Tests (PHPUnit)
- [ ] Database Migrations (thay thế schema.sql tĩnh)

---

## 🤝 Đóng Góp

Dự án đồ án cơ sở — mọi góp ý, phát hiện lỗi đều được hoan nghênh qua [Issues](../../issues).

---

## 📄 License

Distributed under the MIT License. See `LICENSE` for more information.

---

## 🐛 Changelog & Bugfixes

> Tổng hợp toàn bộ lỗi đã được phát hiện và sửa trong quá trình phát triển. Dùng để tra cứu và tránh lặp lại lỗi.

### v1.1.0 — 2026-03-27 · Dark Mode Fixes

#### 🌙 CSS Global (`public/css/style.css`) — Hardcoded Colors

| Thành phần | Vấn đề | Fix |
|---|---|---|
| Form inputs / select | `background: #fff` hardcoded | → `var(--card-bg)` |
| Input group, password toggle | `background: #fff` hardcoded | → `var(--card-bg)` |
| Navbar dropdown menu | `background: rgba(255,255,255,.98)` hardcoded | → `var(--bs-dropdown-bg)` |
| Bootstrap dropdown | Thiếu CSS variable override | Thêm `--bs-dropdown-bg`, `--bs-dropdown-color` vào `[data-theme="dark"]` |
| Auth card (login/register) | Nền trắng trong dark mode | Thêm dark mode override cho `.auth-card` |

#### 🏠 Home Page (`app/views/home/index.php`) — Inline Styles

| Thành phần | Vấn đề | Fix |
|---|---|---|
| Section "Đấu giá HOT" | `background: #fff` inline | → `var(--bg)` |
| Section "Danh mục" | `background: linear-gradient(#f8fafc...)` inline | → class `.hp-category-section` với dark override |
| Section "Sản phẩm mới nhất" | `background: #fff` inline | → `var(--card-bg)` |
| `.hp-auction-card` / `.hp-cat-card` / `.hp-product-card` | `background: #fff` hardcoded | → `var(--card-bg)`, `border: var(--border)` |
| Text màu card title | `color: #0f172a` hardcoded | → `var(--text)` |

#### 🗄️ Database Schema — Lỗi 500 crash

| Lỗi | Nguyên nhân | Fix |
|---|---|---|
| `500` toàn app | Thiếu bảng `giveaways` và `giveaway_participants` | Thêm vào `schema.sql` + migrate DB |
| `500` trang Admin Users | Thiếu cột `lock_reason`, `locked_at`, `locked_until` | `ALTER TABLE` + cập nhật schema |
| `500` trang Tố cáo | Thiếu bảng `reports` | Thêm vào `schema.sql` + migrate DB |

---

### v1.2.0 — 2026-03-29 · Auth, Upload & Admin Fixes

| ID | Mức độ | Mô tả | Fix |
|---|---|---|---|
| BF-001 | 🟡 Medium | Avatar không hiển thị trên Navbar sau upload | Bổ sung `avatar`, `avatar_url` vào `$_SESSION['user']` ở tất cả điểm login; sync session trong `ProfileController::show()` |
| BF-002 | 🔴 High | `redirect()` trong GoogleAuthController xung đột với base Controller | Đổi tên thành `redirectToGoogle()` |
| BF-003 | 🔴 High | Admin duyệt bài crash: `Column not found: 'details'` | Sửa tên cột trong `AuditLog::log()` từ `details` → `note` |
| BF-004 | 🔴 High | Bảng `users` thiếu cột `google_id` và `avatar_url` | Chạy `ALTER TABLE` thêm 2 cột; thêm 3 methods vào `User.php` |
| BF-005 | 🟢 Low | CSS lint warning: `-webkit-line-clamp` thiếu property chuẩn | Thêm `line-clamp: N` sau mỗi `-webkit-line-clamp: N` |
| BF-006 | 🟢 Low | Avatar tràn ra ngoài khung tròn trong navbar | Thêm `overflow: hidden` + `object-fit: cover` cho `.nav-avatar img` |
| BF-007 | 🟡 Medium | Giới hạn upload avatar chỉ 2MB — quá thấp | Nâng lên `10 * 1024 * 1024` trong `ProfileController` + cập nhật UI text |
| BF-009 | 🟡 Medium | OAuth `state` token không bị xóa sau callback | Thêm `unset($_SESSION['oauth_state'])` sau xác minh thành công |

---

### 📊 Tổng Kết

| Mức độ | Số lượng | Trạng thái |
|--------|----------|------------|
| 🔴 High (Crash/Blocker) | 4 | ✅ Đã fix |
| 🟡 Medium | 4 | ✅ Đã fix |
| 🟢 Low (Visual/Warning) | 3 | ✅ Đã fix |
| **Tổng** | **11 bugs** | **100% resolved** |

---

<div align="center">
Made with ❤️ by SinhVienMarket Team · Đồ án cơ sở 2026
</div>
