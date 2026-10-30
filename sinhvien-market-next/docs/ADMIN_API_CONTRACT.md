# Đặc tả API Phân hệ Quản trị (Admin API Contract) — SinhVienMarket

Tài liệu này định nghĩa hợp đồng giao tiếp HTTP JSON giữa ứng dụng **Next.js Frontend** (`lib/api/admin.ts`) và **Backend API** cho toàn bộ 13 màn hình thuộc phân hệ Quản trị viên (`Admin`).

- **Chế độ giả lập (Mock Mode)**: Khi biến môi trường `NEXT_PUBLIC_USE_MOCK=true` (mặc định trong giai đoạn phát triển), toàn bộ các hàm trong `lib/api/admin.ts` gọi trực tiếp vào kho dữ liệu trạng thái `lib/api/mock/admin.ts` (lưu trong `localStorage` với khóa `svm_mock_admin_db_v1`).
- **Chế độ kết nối thật (Real Backend Mode)**: Khi đặt `NEXT_PUBLIC_USE_MOCK=false` và cấu hình `NEXT_PUBLIC_API_BASE_URL`, các hàm sẽ gửi request HTTP chuẩn kèm cookie phiên làm việc (`credentials: "include"`).

---

## 1. Quy ước chung

### 1.1. Xác thực & Phân quyền
- Tất cả endpoint dưới `/admin/*` yêu cầu người dùng đã đăng nhập và có `role === "admin"`.
- Nếu chưa đăng nhập: trả về HTTP `401 Unauthorized` với mã lỗi `"NOT_AUTHENTICATED"`.
- Nếu đã đăng nhập nhưng không phải Admin: trả về HTTP `403 Forbidden` với mã lỗi `"FORBIDDEN"`.

### 1.2. Định dạng lỗi thống nhất
Khi xảy ra lỗi (HTTP `4xx` / `5xx`), Backend trả về JSON theo cấu trúc:

```json
{
  "code": "VALIDATION_ERROR",
  "message": "Mô tả lỗi bằng tiếng Việt hiển thị cho Admin.",
  "fields": {
    "name": "Tên danh mục phải có ít nhất 2 ký tự."
  }
}
```

---

## 2. Danh sách Endpoint theo Màn hình

### 2.1. Tổng quan hệ thống (`/admin`)

#### `GET /admin/dashboard`
Trả về số liệu thống kê tổng quan, 5 giao dịch gần nhất và 5 sản phẩm mới đăng.

**Response `200 OK` (`DashboardStats`)**:
```json
{
  "totalUsers": 7,
  "activeProducts": 6,
  "pendingCount": 4,
  "txToday": 2,
  "recentTx": [
    {
      "id": 501,
      "productId": 108,
      "productTitle": "Áo khoác đồng phục HUTECH size L còn mới",
      "buyerId": 3,
      "buyerName": "Trần Quốc Bảo",
      "sellerId": 1,
      "sellerName": "Nguyễn Minh Anh",
      "type": "sale",
      "amount": 110000,
      "paymentMethod": "banking",
      "orderStatus": "completed",
      "createdAt": "2026-10-28T07:30:00+07:00"
    }
  ],
  "recentProducts": []
}
```

---

### 2.2. Kiểm duyệt bài đăng (`/admin/products`)

#### `GET /admin/products?tab=pending|all`
Lấy danh sách sản phẩm chờ duyệt (`tab=pending`) hoặc toàn bộ sản phẩm (`tab=all`).

#### `POST /admin/products/approve`
Duyệt bài đăng chuyển sang trạng thái `active` và tự động ghi Audit Log `approve_product`.
- **Body**: `{ "productId": 101 }`
- **Response `200 OK`**: `{ "message": "Đã duyệt bài đăng #101." }`

#### `POST /admin/products/reject`
Từ chối bài đăng, chuyển trạng thái sang `cancelled`, lưu lý do từ chối và ghi Audit Log `reject_product`.
- **Body**: `{ "productId": 101, "reason": "Hình ảnh sản phẩm bị mờ, không rõ tình trạng thật." }`
- **Response `200 OK`**: `{ "message": "Đã từ chối bài đăng #101." }`

#### `POST /admin/products/delete`
Xóa bài đăng khỏi hệ thống. Nếu sản phẩm đã phát sinh giao dịch (`hasTransactions === true`), trả về lỗi `409 Conflict` để bảo toàn lịch sử đối soát.
- **Body**: `{ "productId": 101 }`
- **Response `200 OK`**: `{ "message": "Đã xóa bài đăng #101." }`

---

### 2.3. Quản lý Danh mục (`/admin/categories`)

#### `GET /admin/categories`
Trả về danh sách danh mục (`AdminCategory[]`: `id`, `name`, `slug`, `icon`, `createdAt`).

#### `POST /admin/categories`
Thêm danh mục mới (tự động chuẩn hóa `slug` tiếng Việt không dấu, kể cả chữ `đ/Đ`).
- **Body**: `{ "name": "Đồ điện tử", "icon": "bi-laptop" }`
- **Response `201 Created`**: `{ "category": { ... }, "message": "Đã tạo danh mục mới." }`

#### `PUT /admin/categories/:id`
Cập nhật tên, icon và slug của danh mục.
- **Body**: `{ "id": 2, "name": "Thiết bị điện tử", "icon": "bi-laptop" }`
- **Response `200 OK`**: `{ "category": { ... }, "message": "Đã cập nhật danh mục." }`

#### `DELETE /admin/categories/:id`
Xóa danh mục. Trả về `409 Conflict` nếu danh mục đang chứa sản phẩm.

---

### 2.4. Quản lý Banner (`/admin/banners`)

#### `GET /admin/banners`
Lấy danh sách banner quảng cáo sắp xếp theo `displayOrder` tăng dần.

#### `POST /admin/banners`
Tạo banner mới.
- **Body**: `{ "title": "Chợ đồ cũ mùa thi", "link": "/products", "displayOrder": 1, "isActive": true, "imageUrl": "..." }`

#### `PUT /admin/banners/:id`
Cập nhật thông tin tiêu đề, link đích và thứ tự hiển thị.

#### `POST /admin/banners/:id/toggle`
Bật/tắt trạng thái hiển thị (`isActive`) của banner.

#### `DELETE /admin/banners/:id`
Xóa banner và ghi Audit Log `delete_banner`.

---

### 2.5. Quản lý Người dùng, Hồ sơ 360° & Hệ thống Gậy (`/admin/users/*`)

#### `GET /admin/users`
Trả về danh sách tài khoản sinh viên & quản trị viên, **bắt buộc bao gồm trường `strikeCount`** (khắc phục lỗi thiếu cột trong `User::all()` ở bản PHP cũ).

#### `GET /admin/users/:id`
Trả về hồ sơ 360° của người dùng (`AdminUserDetail`):
- `profile`: thông tin tài khoản, trạng thái khóa, câu hỏi bảo mật, số gậy.
- `ratingStats`: `{ "avg": 4.8, "count": 5 }`.
- `products`: danh sách sản phẩm đã đăng.
- `transactions`: danh sách giao dịch mà người dùng là người mua hoặc người bán.
- `reports`: danh sách các phiếu tố cáo nhắm vào người dùng này.

#### `GET /admin/users/:id/violations`
Trả về thông tin người dùng và danh sách lịch sử nhận gậy (`ViolationRecord[]`).

#### `POST /admin/users/:id/strike`
Thực thi quy tắc phạt gậy tự động:
- **Gậy 1**: Cảnh cáo (`action: "warning"`), giữ nguyên trạng thái hoạt động.
- **Gậy 2**: Tự động khóa tài khoản 7 ngày (`action: "suspend_7_days"`).
- **Gậy 3+**: Tự động khóa tài khoản vĩnh viễn (`action: "ban_permanent"`, `lockedUntil: null`).
- **Body**:
```json
{
  "userId": 3,
  "reason": "Đăng bài sai danh mục nhiều lần",
  "evidenceUrl": "https://sinhvienmarket.vn/evidence/post-102"
}
```

#### `POST /admin/users/:id/toggle-lock`
Khóa thủ công theo kỳ hạn (`3days`, `1week`, `2weeks`, `1month`, `3months`, `6months`, `forever`) hoặc mở khóa tài khoản (khi mở khóa tự động đặt lại `strikeCount = 0`).

---

### 2.6. Báo cáo Giao dịch (`/admin/reports`) & Xuất CSV (`/admin/export`)

#### `GET /admin/reports?from=YYYY-MM-DD&to=YYYY-MM-DD`
Trả về thống kê doanh thu trong kỳ:
- `transactions`: danh sách giao dịch trong khoảng ngày.
- `totalAmount`: tổng doanh số (VNĐ).
- `totalCount`: tổng số đơn.
- `avgAmount`: giá trị trung bình / đơn.
- `byDay`: mảng `{ "day": "YYYY-MM-DD", "amount": number }[]` phục vụ biểu đồ cột.

#### `GET /admin/export?type=users|products|transactions&from=YYYY-MM-DD&to=YYYY-MM-DD`
Trả về `{ "filename": "...", "content": "\uFEFF..." }` với ký tự BOM UTF-8 ở đầu chuỗi để mở trực tiếp trên Microsoft Excel không bị lỗi font tiếng Việt.

---

### 2.7. Tố cáo vi phạm (`/admin/system-reports`)

#### `GET /admin/system-reports?status=pending|resolved|ignored`
Lấy danh sách phiếu tố cáo kèm tổng số phiếu đang chờ xử lý (`pendingCount`).

#### `POST /admin/system-reports/:id/resolve`
Xác nhận xử lý (`status: "resolved"`) hoặc bỏ qua (`status: "ignored"`) phiếu tố cáo, lưu `evidenceUrl` và `adminNote`, đồng thời ghi Audit Log `resolve_report` / `ignore_report`.

---

### 2.8. Kiểm duyệt Đánh giá (`/admin/ratings`)

#### `GET /admin/ratings`
Trả về danh sách đánh giá 1–5 sao sau giao dịch (`AdminRating[]`). Sử dụng thống nhất trường `status: "visible" | "hidden"`.

#### `POST /admin/ratings/:id/toggle`
Ẩn hoặc hiện lại nhận xét đánh giá, ghi Audit Log `toggle_rating`.

---

### 2.9. Giveaways & Vòng quay may mắn (`/admin/giveaways`, `/admin/giveaway-spin`)

#### `GET /admin/giveaways` & `GET /admin/giveaways/:id`
Lấy danh sách sự kiện hoặc chi tiết một sự kiện kèm danh sách người tham gia (`participants`).

#### `POST /admin/giveaways`
Tạo sự kiện Giveaway mới (`title`, `description`, `endTime`, `imageUrl`).

#### `POST /admin/giveaways/:id/spin`
Lưu người chiến thắng sau khi vòng quay kết thúc, chuyển trạng thái sự kiện sang `ended` và ghi Audit Log `spin_giveaway`.
- **Body**: `{ "winnerId": 6 }`

#### `DELETE /admin/giveaways/:id`
Xóa sự kiện Giveaway.

---

### 2.10. Nhật ký hành động Admin (`/admin/audit-log`)

#### `GET /admin/audit-log`
Trả về danh sách nhật ký bảo mật chỉ đọc (`AuditLogEntry[]`) sắp xếp mới nhất lên đầu. Mỗi bản ghi gồm `id`, `adminId`, `adminName`, `adminEmail`, `action` (1 trong 19 mã hành động chuẩn), `targetType`, `targetId`, `note` và `createdAt`.
