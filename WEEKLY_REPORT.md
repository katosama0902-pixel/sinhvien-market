# 📅 Báo Cáo Tiến Độ Hàng Tuần — SinhVienMarket

> **Dự án:** SinhVienMarket — Nền tảng mua bán đồ dùng sinh viên KTX
> **Nhóm thực hiện:** *(Điền tên nhóm)*
> **GVHD:** *(Điền tên giáo viên hướng dẫn)*
> **Bắt đầu:** 09/03/2026

---

## 📋 Hướng Dẫn Sử Dụng Template

Mỗi tuần cập nhật một section theo đúng format bên dưới. Đánh dấu trạng thái công việc theo ký hiệu:

| Ký hiệu | Ý nghĩa |
|---------|---------|
| ✅ | Hoàn thành |
| 🔄 | Đang thực hiện |
| ⏸️ | Tạm dừng / Chưa bắt đầu |
| ❌ | Không thể hoàn thành / Bị block |
| 🐛 | Bug / Lỗi cần xử lý |

---

## TUẦN 1 — 09/03/2026 đến 14/03/2026

### 🎯 Mục Tiêu Tuần Này
Xây dựng nền tảng ban đầu của dự án: cấu trúc thư mục, kết nối database, và các tính năng xác thực cơ bản.

### 📝 Công Việc Đã Thực Hiện

| STT | Công việc | Người thực hiện | Trạng thái | Ghi chú |
|-----|-----------|-----------------|------------|---------|
| 1 | Khởi tạo dự án, thiết lập môi trường Laragon + cấu trúc MVC | *(Tên)* | ✅ | Single entry point `index.php` |
| 2 | Thiết kế database schema (`users`, `products`, `categories`, `transactions`) | *(Tên)* | ✅ | File `database/schema.sql` |
| 3 | Hệ thống Đăng ký / Đăng nhập cơ bản (Email + Mật khẩu, CSRF) | *(Tên)* | ✅ | Hash bcrypt, CSRF token |
| 4 | Xác thực OTP qua email (mã 6 số, thời hạn 15 phút) | *(Tên)* | ✅ | Sử dụng PHPMailer + Gmail SMTP |
| 5 | Admin Panel — Đăng nhập riêng, Dashboard thống kê tổng quan | *(Tên)* | ✅ | Phân quyền `role = admin` |
| 6 | CRUD Sản phẩm (Đăng bán, chỉnh sửa, xóa, duyệt bài) | *(Tên)* | ✅ | Upload ảnh kèm sản phẩm |

### 🐛 Lỗi Phát Sinh & Cách Xử Lý
*(Không có lỗi lớn trong tuần này)*

### 📊 Tiến Độ Tổng Thể
- **Hoàn thành:** 6/6 công việc (100%)
- **Tổng số dòng code ước tính:** ~1,200 dòng

### 💬 Nhận Xét / Kế Hoạch Tuần Sau
Nền tảng MVC hoạt động ổn định. Tuần sau sẽ bắt đầu xây dựng các tính năng nghiệp vụ cốt lõi (Đấu giá, Chat, Rating).

---

## TUẦN 2 — 15/03/2026 đến 21/03/2026

### 🎯 Mục Tiêu Tuần Này
Xây dựng các tính năng tương tác cốt lõi: Đấu giá ngược, Chat, Thông báo, Đánh giá.

### 📝 Công Việc Đã Thực Hiện

| STT | Công việc | Người thực hiện | Trạng thái | Ghi chú |
|-----|-----------|-----------------|------------|---------|
| 1 | Đấu giá ngược (Dutch Auction) — Giá tự giảm theo thời gian thực | *(Tên)* | ✅ | Polling API `/api/auction/price` mỗi 5s |
| 2 | Hệ thống Chat 2 chiều (polling mỗi 3s, không cần WebSocket) | *(Tên)* | ✅ | Bảng `messages` + `conversations` |
| 3 | Hệ thống Thông báo — Navbar badge, notify khi có tin nhắn/duyệt bài | *(Tên)* | ✅ | Bảng `notifications` |
| 4 | Hệ thống Đánh giá (Rating 1–5 sao sau giao dịch thành công) | *(Tên)* | ✅ | Hiển thị điểm uy tín người bán |
| 5 | Hồ sơ & Trang cá nhân công khai (avatar, bio, uy tín người bán) | *(Tên)* | ✅ | Public Profile `/profile/view?id=` |
| 6 | Admin: Quản lý user, khóa tài khoản, xem lịch sử giao dịch | *(Tên)* | ✅ | Bảng `audit_logs` ghi nhận hành động |
| 7 | Sự kiện Giveaway (quay số trúng thưởng, tích hợp popup) | *(Tên)* | ✅ | Bảng `giveaways` + `giveaway_participants` |

### 🐛 Lỗi Phát Sinh & Cách Xử Lý
*(Không có lỗi lớn trong tuần này)*

### 📊 Tiến Độ Tổng Thể
- **Hoàn thành:** 7/7 công việc (100%)
- **Tổng số dòng code ước tính:** ~2,800 dòng

### 💬 Nhận Xét / Kế Hoạch Tuần Sau
Các tính năng tương tác hoạt động tốt. Cần tập trung vào các tính năng nâng cao: Order Tracking, Report, Error Handling.

---

## TUẦN 3 — 22/03/2026 đến 25/03/2026

### 🎯 Mục Tiêu Tuần Này
Hoàn thiện các tính năng nâng cao: Order Tracking, Tố cáo vi phạm, Error Handling và RESTful API.

### 📝 Công Việc Đã Thực Hiện

| STT | Công việc | Người thực hiện | Trạng thái | Ghi chú |
|-----|-----------|-----------------|------------|---------|
| 1 | Order Tracking — Vận chuyển qua 4 trạng thái | *(Tên)* | ✅ | Chờ → Đang giao → Đã giao → Hoàn tất |
| 2 | Hệ thống Tố cáo Vi phạm (Report) — User tố cáo, Admin xét duyệt | *(Tên)* | ✅ | Bảng `reports` |
| 3 | Public Profile — Click tên người bán → mở trang cá nhân | *(Tên)* | ✅ | Hiển thị rating, sản phẩm đang bán |
| 4 | Error Handling toàn cục (`ErrorHandler`, `Logger`, trang 404/500 đẹp) | *(Tên)* | ✅ | Log file tự động theo ngày |
| 5 | RESTful API chuẩn (`ApiController`, envelope response) | *(Tên)* | ✅ | Envelope: `{success, data, error}` |

### 🐛 Lỗi Phát Sinh & Cách Xử Lý
*(Không có lỗi lớn trong tuần này)*

### 📊 Tiến Độ Tổng Thể
- **Hoàn thành:** 5/5 công việc (100%)
- **Tổng số dòng code ước tính:** ~3,900 dòng

### 💬 Nhận Xét / Kế Hoạch Tuần Sau
Hệ thống khá hoàn chỉnh. Tuần sau sẽ tập trung vào UI/UX: Dark Mode, Git Workflow, và các tính năng bổ sung.

---

## TUẦN 4 — 26/03/2026 đến 29/03/2026

### 🎯 Mục Tiêu Tuần Này
Hoàn thiện Dark Mode, tích hợp Google OAuth, fix các lỗi tồn đọng và hoàn thiện DevOps.

### 📝 Công Việc Đã Thực Hiện

| STT | Công việc | Người thực hiện | Trạng thái | Ghi chú |
|-----|-----------|-----------------|------------|---------|
| 1 | Dark Mode toàn trang — Toggle, lưu localStorage | *(Tên)* | ✅ | CSS Variables system |
| 2 | Fix Dark Mode Dropdown — Override Bootstrap CSS variables | *(Tên)* | ✅ | `--bs-dropdown-bg`, `--bs-dropdown-color` |
| 3 | Fix hardcoded màu trên Home Page & Form inputs | *(Tên)* | ✅ | Thay thế bằng `var(--card-bg)`, `var(--text)` |
| 4 | Git Workflow — Khởi tạo GitHub repo, Feature Branch + Pull Request | *(Tên)* | ✅ | Hướng dẫn quy trình cho cả nhóm |
| 5 | Schema Audit — Bổ sung `giveaways`, `giveaway_participants` | *(Tên)* | ✅ | Đồng bộ `schema.sql` với live DB |
| 6 | Wishlist Button — Nút "Thêm vào Yêu thích" trên chi tiết sản phẩm | *(Tên)* | ✅ | Bảng `wishlists` |
| 7 | Google OAuth 2.0 — Đăng nhập qua Google, liên kết tài khoản | *(Tên)* | ✅ | Không dùng thư viện ngoài, thuần cURL |
| 8 | Recent Accounts — Ghi nhớ tài khoản cũ trên trang đăng nhập | *(Tên)* | ✅ | Lưu `localStorage` |
| 9 | Avatar Navbar — Hiển thị ảnh đại diện trên thanh điều hướng | *(Tên)* | ✅ | Hỗ trợ cả ảnh upload & ảnh Google |
| 10 | Nâng giới hạn upload ảnh avatar từ 2MB lên 10MB | *(Tên)* | ✅ | Cập nhật `php.ini` + Controller |

### 🐛 Lỗi Phát Sinh & Cách Xử Lý

| Bug | Mô tả | Trạng thái |
|-----|--------|------------|
| 🐛 AuditLog crash khi Admin duyệt bài | Sai tên cột: `details` → `note` | ✅ Đã fix |
| 🐛 Avatar không sync lên Navbar sau upload | Thiếu `avatar` trong `$_SESSION['user']` | ✅ Đã fix |
| 🐛 GoogleAuth redirect() xung đột base class | Đổi tên method thành `redirectToGoogle()` | ✅ Đã fix |

### 📊 Tiến Độ Tổng Thể
- **Hoàn thành:** 10/10 công việc (100%)
- **Bugs phát sinh:** 3 — Đã fix hết
- **Tổng số dòng code ước tính:** ~5,500 dòng

### 💬 Nhận Xét / Kế Hoạch Tuần Sau
Dự án đã hoàn thiện ở mức rất tốt cho đồ án cơ sở. Tuần 5 sẽ tập trung vào tài liệu hóa, kiểm tra cuối và chuẩn bị nộp.

---

## TUẦN 5 — 30/03/2026 đến ...

### 🎯 Mục Tiêu Tuần Này
Hoàn thiện tài liệu, kiểm tra toàn bộ hệ thống và chuẩn bị nộp đồ án.

### 📝 Công Việc Đã Thực Hiện

| STT | Công việc | Người thực hiện | Trạng thái | Ghi chú |
| 1 | Soạn `README.md` chuẩn GitHub | *(Tên)* | ✅ | Đã cập nhật đầy đủ |
| 2 | Tổng hợp `BUG_FIX_NOTES.md` | *(Tên)* | ✅ | Đã bổ sung lỗi mới nhất |
| 3 | Cập nhật Bộ lọc sản phẩm nâng cao (theo tình trạng) | *(Tên)* | ✅ | Auto-submit bằng JS |
| 4 | Bổ sung Tính năng Huy hiệu Xác thực Sinh Viên | *(Tên)* | ✅ | Xác thực qua mail .edu.vn + UI Badges |
| 5 | Bổ sung Tính năng Điểm Xu & Check-in & Đẩy tin | *(Tên)* | ✅ | Check-in +10, Đẩy tin -50 xu |
| 6 | Live Search (gợi ý khi gõ) | *(Tên)* | ⏸️ | Chưa bắt đầu |
| 7 | Rate Limiting (chống spam OTP) | *(Tên)* | ⏸️ | Chưa bắt đầu |
| 8 | Smoke Testing toàn bộ luồng | *(Tên)* | ✅ | Đã test thủ công UI |
| 9 | Hoàn thiện báo cáo + tài liệu đồ án | *(Tên)* | 🔄 | Đang soạn thảo |

### 🐛 Lỗi Phát Sinh & Cách Xử Lý

| Bug | Mô tả | Trạng thái |
|-----|--------|------------|
| 🐛 Lỗi báo "Tổng: 0 xu" khi mới checkin lần đầu | Biến `coins` bị gán NULL trong phép tính do dữ liệu DB cũ. | ✅ Đã fix (dùng `COALESCE`) |

### 📊 Tiến Độ Tổng Thể
- **Hoàn thành:** 6/9 công việc (~67%)
- **Dự kiến hoàn thành cuối tuần:** 100%

### 💬 Nhận Xét / Kế Hoạch Tuần Sau
*(Cập nhật sau khi kết thúc tuần)*

---

## TUẦN 6 — 05/04/2026 đến 11/04/2026

### 🎯 Mục Tiêu Tuần Này
Tích hợp các tiện ích tương tác chuyên sâu nâng cao: Trợ lý bán hàng tự động bằng AI, khắc phục các lỗi chập chờn của Polling Chat và nghiên cứu thư viện Bản đồ.

### 📝 Công Việc Đã Thực Hiện

| STT | Công việc | Người thực hiện | Trạng thái | Ghi chú |
|-----|-----------|-----------------|------------|---------|
| 1 | Tích hợp Trợ lý bán hàng AI (Gemini Flash) | *(Tên)* | ✅ | Dùng Google AI Studio API Key |
| 2 | Áp dụng Shopee/Lazada Auto-Reply Cooldown | *(Tên)* | ✅ | 12h Cooldown & 5m Session protect |
| 3 | Sửa lỗi Duplicate Chat (Race Condition) | *(Tên)* | ✅ | Cắm cờ ID `msg-{{id}}` vào DOM |
| 4 | Thử nghiệm Google Maps & Places Autocomplete | *(Tên)* | 🔄 | Script đã viết sẵn nhưng tạm ẩn chờ Config Cloud billing |
| 5 | Tối ưu hóa UI Chatbox (isSending Lock) | *(Tên)* | ✅ | Ngăn double-submit |

### 🐛 Lỗi Phát Sinh & Cách Xử Lý

| Bug | Mô tả | Trạng thái |
|-----|--------|------------|
| 🐛 Tin nhắn chat bị đúp 2 lần | Fetch AJAX bắt nhầm tin cũ | ✅ Đã fix (ID mapping) |
| 🐛 Bot AI Spam liên tục | AI Reply bị trigger sai chuẩn | ✅ Đã fix (Áp dụng bộ đếm thời gian thực) |
| 🐛 API Key Google Maps không chạy | Key AI Studio không hỗ trợ quyền Maps | 🔄 Tạm Restore Leaflet, chờ Setup GCP gốc |

### 📊 Tiến Độ Tổng Thể
- **Hoàn thành:** 4/5 công việc (80%)
- **Bugs phát sinh:** 3 — Đã fix: 2, Chờ config: 1

### 💬 Nhận Xét / Kế Hoạch Tuần Sau
Tính năng AI đã hoạt động đúng thiết kế thông minh (nhường lời cho người bán nếu người bán đang online). Tuần sau sẽ tập trung vào live test toàn diện toàn bộ quy trình mua bán lần cuối và chỉnh sửa nốt config Google Cloud cho map.

---

---

## TUẦN 7 — 12/04/2026 đến 18/04/2026

### 🎯 Mục Tiêu Tuần Này
Triển khai các tính năng thực chiến mà thầy giáo đề xuất, tập trung vào trải nghiệm người dùng (C2C) và khả năng chia sẻ sản phẩm bên ngoài nền tảng.

### 📝 Công Việc Đã Thực Hiện

| STT | Công việc | Người thực hiện | Trạng thái | Ghi chú |
|-----|-----------|-----------------|------------|---------|
| 1 | Cải thiện giao diện Điểm Danh (Rewards Center) | *(Tên)* | ✅ | Nhãn "Đã nhận" xanh lá cho ngày cũ, gạch chân số xu đã lĩnh |
| 2 | Triển khai QR Code Sharing tại trang chi tiết sản phẩm | *(Tên)* | ✅ | Dùng api.qrserver.com, nút tải ảnh về máy |
| 3 | Tích hợp Open Graph Meta Tags (SEO) cho link Zalo/Facebook | *(Tên)* | ✅ | `og:title`, `og:description`, `og:image`, `og:url` |
| 4 | Xây dựng hệ thống Make an Offer (Trả giá / Mặc cả) | *(Tên)* | ✅ | Nút Trả giá trên trang sản phẩm + Offer Card trong Chat |
| 5 | Nâng cấp CSDL: thêm `msg_type`, `offer_status`, `offer_price` vào bảng `messages` | *(Tên)* | ✅ | Migration trực tiếp qua PDO |
| 6 | Sửa lỗi `Class 'App\Controllers\User' not found` (BF-020) | *(Tên)* | ✅ | Missing `use App\Models\User` trong ProductController |
| 7 | Sửa lỗi 404 khi xem hồ sơ người bán (BF-021) | *(Tên)* | ✅ | Bỏ kiểm tra `role !== 'student'` không cần thiết |
| 8 | Nâng cấp Bản đồ lên Google Maps API | *(Tên)* | ✅ | Thay thế Leaflet/OSM, sử dụng Google Maps SDK & Geocoding |

### 🐛 Lỗi Phát Sinh & Cách Xử Lý

| Bug | Mô tả | Trạng thái |
|-----|--------|------------|
| 🐛 [BF-020] Missing `use` statement | `ProductController::show()` gọi `User` mà không import namespace | ✅ Đã fix |
| 🐛 [BF-021] Hồ sơ người bán 404 | Logic check `role !== 'student'` quá khắt, chặn cả user hợp lệ | ✅ Đã fix |

### 📊 Tiến Độ Tổng Thể
- **Hoàn thành:** 7/7 công việc (100%)
- **Bugs phát sinh:** 2 — Đã fix: 2

### 💬 Nhận Xét / Kế Hoạch Tuần Sau
V1.5.0 đã hoàn chỉnh với 2 tính năng thực chiến cao: QR Sharing và Make an Offer. Tuần sau nếu có thời gian có thể phát triển tiếp Leaderboard Người Bán và hệ thống Minigame nhận Xu tại /rewards.

---

## 📈 Tổng Hợp Tiến Độ Toàn Dự Án

| Tuần | Giai đoạn | Số task | Hoàn thành | Tỷ lệ |
|------|-----------|---------|------------|-------|
| Tuần 1 (09–14/03) | Xây nền móng | 6 | 6 | 100% ✅ |
| Tuần 2 (15–21/03) | Tính năng cốt lõi | 7 | 7 | 100% ✅ |
| Tuần 3 (22–25/03) | Nâng cao & An toàn | 5 | 5 | 100% ✅ |
| Tuần 4 (26–29/03) | UI & DevOps | 10 | 10 | 100% ✅ |
| Tuần 5 (30/03–04/04) | Tài liệu & Kiểm thử | 9 | 8 | ~88% ✅ |
| Tuần 6 (05/04–11/04) | AI Chat & Map Dev | 5 | 4 | 80% 🔄 |
| Tuần 7 (12/04–18/04) | Thực Chiến C2C (v1.5.0) | 7 | 7 | 100% ✅ |
| **Tổng** | | **49** | **47** | **~96%** |

---

## 📌 Template Tuần Mới (Copy & Paste khi bắt đầu tuần mới)

```markdown
## TUẦN X — DD/MM/YYYY đến DD/MM/YYYY

### 🎯 Mục Tiêu Tuần Này
*(Mô tả mục tiêu tổng quát của tuần)*

### 📝 Công Việc Đã Thực Hiện

| STT | Công việc | Người thực hiện | Trạng thái | Ghi chú |
|-----|-----------|-----------------|------------|---------|
| 1 | ... | *(Tên)* | ⏸️ | ... |

### 🐛 Lỗi Phát Sinh & Cách Xử Lý

| Bug | Mô tả | Trạng thái |
|-----|--------|------------|
| 🐛 ... | ... | ⏸️ Chưa fix |

### 📊 Tiến Độ Tổng Thể
- **Hoàn thành:** X/Y công việc (Z%)
- **Bugs phát sinh:** N — Đang xử lý: M

### 💬 Nhận Xét / Kế Hoạch Tuần Sau
*(Nhận xét về tuần này và định hướng cho tuần tiếp theo)*
```

---

<div align="center">

📬 Báo cáo được duy trì bởi **SinhVienMarket Team** · Cập nhật hàng tuần vào **Thứ Hai**

</div>
