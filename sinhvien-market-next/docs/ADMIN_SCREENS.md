# ADMIN_SCREENS — Rà soát nhóm màn hình Quản trị (Tuần 3 - Phase 0)

Nguồn đã đọc: `app/views/layouts/admin.php`, `app/views/admin/*` (13 view), `AdminController.php`, `AdminProductController.php`, `AdminUserController.php`, `AdminReportController.php`, `AdminGiveawayController.php`, cùng các model `User.php`, `Product.php`, `Category.php`, `Transaction.php`, `Report.php`, `Rating.php`, `Banner.php`, `Giveaway.php`, `AuditLog.php` và `index.php`. Không chỉnh sửa bất kỳ file nào trong `sinhvien-market-php/`.

## 0. Bảng tổng hợp các màn hình Admin

| # | View PHP | Route PHP | Route Next.js | Nhóm chức năng | Phase dự kiến |
|---|---|---|---|---|---|
| 0 | `layouts/admin.php` | Khung dùng chung | `components/admin/AdminShell.tsx` | Sidebar + Topbar + Bảo vệ quyền Admin | Phase 1 |
| 1 | `dashboard.php` | `GET /admin` | `/admin` | Tổng quan thống kê & lối tắt | Phase 1 |
| 2 | `products.php` | `GET /admin/products` | `/admin/products` | Kiểm duyệt & quản lý bài đăng | Phase 2 |
| 3 | `categories.php` | `GET /admin/categories` | `/admin/categories` | Quản lý danh mục (CRUD) | Phase 2 |
| 4 | `banners.php` | `GET /admin/banners` | `/admin/banners` | Quản lý banner trang chủ | Phase 2 |
| 5 | `users.php` | `GET /admin/users` | `/admin/users` | Quản lý người dùng, khóa & phạt gậy | Phase 3 |
| 6 | `user_detail.php` | `GET /admin/users/detail?id=` | `/admin/users/detail?id=` | Hồ sơ chi tiết & hoạt động người dùng | Phase 3 |
| 7 | `user_violations.php` | `GET /admin/users/violations?id=` | `/admin/users/violations?id=` | Lịch sử nhận gậy vi phạm của người dùng | Phase 3 |
| 8 | `reports.php` | `GET /admin/reports` | `/admin/reports` | Báo cáo doanh thu & giao dịch theo ngày | Phase 4 |
| 9 | `system_reports.php` | `GET /admin/system-reports` | `/admin/system-reports` | Quản lý tố cáo vi phạm từ người dùng | Phase 4 |
| 10 | `ratings.php` | `GET /admin/ratings` | `/admin/ratings` | Kiểm duyệt đánh giá & bình luận (1-5 sao) | Phase 4 |
| 11 | `giveaways.php` | `GET /admin/giveaways` | `/admin/giveaways` | Quản lý sự kiện Giveaway | Phase 4 |
| 12 | `giveaway_spin.php` | `GET /admin/giveaway_spin?id=` | `/admin/giveaway-spin?id=` | Vòng quay may mắn chọn người trúng giải | Phase 4 |
| 13 | `audit_log.php` | `GET /admin/audit-log` | `/admin/audit-log` | Nhật ký hành động của Admin | Phase 4 |

Các action phụ trợ trong PHP:
- Xuất dữ liệu CSV: `GET /admin/export?type={users|products|transactions}&from=&to=&_token=`
- Kiểm duyệt sản phẩm: `POST /admin/products/approve`, `POST /admin/products/reject`, `POST /admin/products/delete`
- Danh mục: `POST /admin/categories/store`, `POST /admin/categories/update`, `POST /admin/categories/delete`
- Người dùng: `POST /admin/users/toggle` (khóa/mở khóa), `POST /admin/users/strike` (phạt gậy)
- Tố cáo & đánh giá: `POST /admin/system-reports/resolve`, `POST /admin/ratings/toggle`
- Banner: `POST /admin/banners/store`, `POST /admin/banners/update`, `POST /admin/banners/toggle`, `POST /admin/banners/delete`
- Giveaway: `POST /admin/giveaways/store`, `POST /admin/giveaways/delete`, `POST /admin/giveaway_spin_api`

---

## 1. Khung giao diện Admin (`layouts/admin.php`)

- **Phân quyền (`Middleware::requireAdmin`)**:
  - Chưa đăng nhập: đẩy về `/login-role`.
  - Tài khoản bị khóa (`is_locked = 1`): đẩy về `/account-locked`.
  - Đã đăng nhập nhưng vai trò không phải `admin`: đẩy về trang dành cho sinh viên (`/products`).
- **Sidebar trái (`w-64`, cố định trên màn hình lớn `lg`, mở dạng ngăn kéo trên mobile)**:
  - Nền gradient tối (`#1e1b4b` đến `#0f172a`), logo `AdminPanel - SinhVienMarket`.
  - 3 nhóm điều hướng với 10 mục:
    1. *Tổng quan*: Dashboard (`/admin`)
    2. *Quản lý*: Người dùng (`/admin/users`), Kiểm duyệt bài (`/admin/products`), Danh mục (`/admin/categories`), Sự kiện Giveaway (`/admin/giveaways`), Quản lý Banner (`/admin/banners`)
    3. *Báo cáo & Vi phạm*: Báo cáo giao dịch (`/admin/reports`), Tố cáo vi phạm (`/admin/system-reports`), Kiểm duyệt Đánh giá (`/admin/ratings`), Nhật ký Admin (`/admin/audit-log`)
  - Chân sidebar: liên kết "Xem trang web" mở `/products`.
- **Topbar (`sticky top-0`)**:
  - Trái: nút mở sidebar trên mobile + tiêu đề màn hình hiện tại kèm vạch màu nhấn.
  - Phải: nút chuyển chế độ Sáng/Tối, avatar chữ cái đầu + tên Admin + nhãn `Administrator`, nút `Thoát` (đăng xuất).
- **Hệ thống thông báo**: Flash banner đầu trang + Toast nổi góc dưới bên phải + nút cuộn lên đầu trang.

---

## 2. Chi tiết từng màn hình

### 2.1. Dashboard — `/admin`
- **Banner chào mừng**: Hiển thị avatar chữ cái đầu, lời chào tên Admin, thứ/ngày/tháng/năm hiện tại, huy hiệu số bài chờ duyệt (nếu `> 0`) và nút "Duyệt ngay" dẫn sang `/admin/products`.
- **4 thẻ thống kê (Stats Cards)**:
  1. Tổng người dùng (đếm tài khoản `student`, nhãn `Tài khoản`, xu hướng `+12%`)
  2. Sản phẩm đang bán (trạng thái `active`, nhãn `Đang live`, xu hướng `+5%`)
  3. Chờ duyệt (trạng thái `pending`, nhãn `Cần xử lý`)
  4. Giao dịch hôm nay (nhãn `Hôm nay`, xu hướng `+8%`)
- **2 bảng rút gọn (tối đa 5 dòng/bảng)**:
  - *Bài đăng chờ duyệt*: tiêu đề, người bán, thời gian đăng, nhãn loại bài (`Bán`, `Trao đổi`, `Đấu giá`).
  - *Giao dịch mới nhất*: tên sản phẩm, người mua -> người bán, số tiền (`+...đ`), nhãn `Đã hủy` nếu đơn bị hủy.
- **Truy cập nhanh**: 6 thẻ điều hướng nhanh tới Người dùng, Kiểm duyệt, Danh mục, Báo cáo, Giveaway, Nhật ký.

### 2.2. Kiểm duyệt bài đăng — `/admin/products`
- **2 tab lọc**:
  - `Chờ duyệt` (`?tab=pending`, mặc định): hiện danh sách bài `pending` kèm số đếm.
  - `Tất cả bài đăng` (`?tab=all`): hiện toàn bộ sản phẩm.
- **Bảng dữ liệu**: Sản phẩm (tiêu đề + tên danh mục), Người đăng (họ tên + email), Loại (`Bán`, `Trao đổi`, `Đấu giá`), Giá, Trạng thái (`Chờ duyệt`, `Đang bán`, `Đã bán`, `Đã từ chối`), Ngày đăng, Hành động.
- **Hành động**:
  - Bài đang `pending`: nút **Duyệt** (chuyển sang `active`, ghi audit log `approve_product`) và nút **Từ chối** (chuyển sang `cancelled`, ghi audit log `reject_product`).
  - Bài chưa `sold`: nút **Xóa** (nếu sản phẩm đã phát sinh giao dịch thì chặn xóa và báo lỗi cần giữ lịch sử đơn hàng; nếu chưa có giao dịch thì xóa và ghi audit log `delete_product`).
- **Xuất dữ liệu**: Modal chọn khoảng ngày `from` - `to` để tải file CSV danh sách sản phẩm.

### 2.3. Quản lý danh mục — `/admin/categories`
- **Bảng danh mục**: STT, Icon (10 icon chuẩn: Sách, Điện tử, Phương tiện, Đồ dùng học tập, Đồ gia dụng, Nhạc cụ, Dụng cụ nghệ thuật, Giải trí, Thời trang, Khác), Tên danh mục, Slug, Ngày tạo, Hành động.
- **Thêm / Sửa danh mục (Modal)**:
  - Trường `name` (bắt buộc, tối thiểu 2 ký tự) và `icon` (chọn từ danh sách 10 icon).
  - Tự động sinh `slug` từ tên danh mục, ghi audit log `create_category` / `update_category`.
- **Xóa danh mục**: Nếu danh mục đang có sản phẩm thì chặn xóa và báo `"Không thể xóa danh mục đang có sản phẩm."`; ngược lại xóa và ghi audit log `delete_category`.

### 2.4. Quản lý người dùng — `/admin/users`
- **Header**: Tổng số tài khoản + nút **Xuất Excel (CSV)** mở modal lọc ngày đăng ký.
- **Bảng người dùng**: STT, Người dùng (avatar + tên + email), SĐT, Vai trò (`Admin` / `Sinh viên`), Trạng thái (`Hoạt động` / `Bị khóa` kèm hạn khóa hoặc `Vĩnh viễn`, cùng huy hiệu `X/3 Gậy` nếu có), Ngày tạo, Hành động.
- **Hành động trên từng dòng**:
  - Nút **Chi tiết** -> `/admin/users/detail?id=`.
  - Với tài khoản không phải Admin và không phải chính mình:
    - Nút **Gậy (X)** -> `/admin/users/violations?id=`.
    - Nút **Phạt Gậy**: mở modal nhập `reason` (bắt buộc) và `evidence_url` (tùy chọn, định dạng URL). Quy tắc cộng gậy:
      - Gậy 1: cảnh cáo qua email (`strike_count = 1`).
      - Gậy 2: tự động khóa tài khoản 7 ngày (`strike_count = 2`, `is_locked = 1`).
      - Gậy 3+: tự động khóa vĩnh viễn (`is_locked = 1`, `locked_until = null`).
    - Nút **Khóa / Mở khóa** thủ công: khóa theo các mức thời hạn (`3days`, `1week`, `2weeks`, `1month`, `3months`, `6months`, `forever`) kèm lý do khóa, hoặc mở khóa tài khoản đang bị khóa; ghi audit log `lock_user` / `unlock_user`.

### 2.5. Chi tiết người dùng — `/admin/users/detail?id=`
- **Cột trái**:
  - Thẻ Hero: Avatar chữ cái, Họ tên, Email, xếp hạng sao trung bình và tổng lượt đánh giá.
  - Thẻ Thông tin tài khoản: ID, Vai trò, Trạng thái khóa, Xác thực email, Ngày đăng ký, Xác thực lần cuối.
  - Thẻ Thông tin cá nhân: Họ tên, Email, SĐT, Câu hỏi bảo mật.
  - Thẻ Chi tiết khóa tài khoản (chỉ hiện khi `is_locked = 1`): Lý do khóa, Thời điểm khóa, Hết hạn khóa.
- **Cột phải**:
  - Bảng *Sản phẩm đã đăng*: Sản phẩm, Loại, Giá, Trạng thái, Ngày đăng.
  - Bảng *Lịch sử giao dịch*: Sản phẩm, Vai trò trong giao dịch (`Người mua` / `Người bán`), Số tiền, Phương thức thanh toán (`COD`, `Chuyển khoản`, `ZaloPay`), Ngày.
  - Bảng *Nhật ký bị tố cáo*: Người tố cáo, Lý do + Mô tả + Link bằng chứng, Trạng thái xử lý, Ngày.

### 2.6. Lịch sử vi phạm (Gậy) — `/admin/users/violations?id=`
- **Thẻ Tổng số gậy**: Hiển thị `X/3` và huy hiệu trạng thái (`Tài khoản sạch`, `Đã cảnh cáo`, `Đang bị khóa 7 ngày`, `Bị khóa vĩnh viễn`).
- **Thẻ Thông tin tài khoản**: Ngày tham gia, Trạng thái hiện tại, Lý do khóa hiện tại (nếu có).
- **Bảng Nhật ký nhận gậy**: Gậy thứ mấy, Ngày & giờ nhận, Admin xử lý, Lý do chi tiết và nút xem bằng chứng (nếu có).

### 2.7. Báo cáo giao dịch — `/admin/reports`
- **Bộ lọc thời gian**: `Từ ngày` (`from`, mặc định đầu tháng) và `Đến ngày` (`to`, mặc định hôm nay). Kiểm tra `from <= to`, nếu sai báo lỗi `"Lỗi: Từ ngày không được lớn hơn Đến ngày!"`. Có nút `Lọc`, `Reset` và `Xuất Excel (CSV)`.
- **Chỉ tính các giao dịch hoàn thành** (`order_status` thuộc `delivered`, `received`, `completed`).
- **3 thẻ tổng hợp**: Số giao dịch, Tổng giá trị (VNĐ), Giá trung bình mỗi giao dịch (VNĐ).
- **Biểu đồ cột**: Doanh số theo từng ngày (`dd/mm`) trong khoảng thời gian đã lọc.
- **Bảng giao dịch**: STT, Sản phẩm, Người mua, Người bán, Loại (`Đấu giá` / `Trực tiếp`), Giá, Thời gian, cùng dòng `Tổng cộng` ở chân bảng.

### 2.8. Tố cáo vi phạm — `/admin/system-reports`
- **4 tab lọc trạng thái**: `Tất cả`, `Chưa xử lý` (`pending`, có huy hiệu đếm số lượng chưa xử lý), `Đã giải quyết` (`resolved`), `Bỏ qua` (`ignored`).
- **Bảng tố cáo**: ID, Người tố cáo (link tới chi tiết user), Đối tượng bị tố cáo (User kèm link + Sản phẩm kèm link), Nội dung vi phạm (Lý do, Mô tả, Ghi chú của Admin nếu có, nút Xem bằng chứng đính kèm), Trạng thái, Ngày tạo, Hành động.
- **Hành động (khi `pending`)**:
  - Nút **Xử lý**: mở modal cho phép đính kèm ảnh bằng chứng mới (tùy chọn) và nhập `admin_note` (ghi chú xử lý), chuyển trạng thái sang `resolved`.
  - Nút **Bỏ qua**: xác nhận và chuyển trạng thái sang `ignored`.

### 2.9. Kiểm duyệt Đánh giá — `/admin/ratings`
- **Bảng đánh giá**: ID, Người đánh giá (tên + email, link tới hồ sơ), Người bán (link tới hồ sơ), Sản phẩm, Số sao (1-5 sao) + nội dung bình luận, Trạng thái (`Hiển thị` / `Đã Ẩn`) + ngày tạo, Hành động.
- **Hành động**: Nút **Ẩn** / **Hiện lại** bình luận. Khi bị ẩn, dòng đánh giá đổi màu nền nhạt, nội dung bình luận gạch ngang và gửi thông báo cảnh cáo tiêu chuẩn cộng đồng.

### 2.10. Quản lý Banner — `/admin/banners`
- **Bảng banner**: Thứ tự sắp xếp (`display_order`), Hình ảnh xem trước (tỉ lệ ngang), Thông tin (Tiêu đề nội bộ + Link đích), Trạng thái (nút bấm chuyển trực tiếp giữa `Hiển thị` và `Đang Ẩn`), Hành động (`Sửa`, `Xóa`).
- **Modal Thêm Banner**: Chọn file ảnh (bắt buộc), Tiêu đề nội bộ, Link đích, Thứ tự hiển thị (mặc định `0`), Trạng thái (`Hiển thị ngay` / `Tạm ẩn`).
- **Modal Sửa Banner**: Cập nhật Tiêu đề nội bộ, Link đích, Thứ tự hiển thị.

### 2.11. Quản lý Giveaways & Vòng quay may mắn — `/admin/giveaways` & `/admin/giveaway-spin`
- **Trang danh sách (`/admin/giveaways`)**:
  - Bảng sự kiện: Ảnh + Tên sự kiện, Trạng thái (`Đang diễn ra`, `Chờ xổ số` khi đã qua `end_time` nhưng chưa quay, `Đã kết thúc` khi đã có người trúng), Thời gian kết thúc, Người trúng giải (hiện tên kèm biểu tượng cúp hoặc `Chưa có`), Thao tác (`Quay Số` nếu chưa kết thúc, `Xóa`).
  - Modal Tạo sự kiện mới: Tiêu đề phần thưởng (bắt buộc), Mô tả chi tiết, Thời gian kết thúc (`datetime-local`, bắt buộc), Hình ảnh quà tặng (tùy chọn).
- **Trang Vòng quay may mắn (`/admin/giveaway-spin?id=`)**:
  - Hiển thị tên sự kiện, tổng số sinh viên tham gia.
  - Khung băng chuyền (`spin-container`) với kim chỉ đỏ ở chính giữa và danh sách thẻ người tham gia lặp lại.
  - Khi bấm **Bắt Đầu Quay**: khóa nút, cuộn băng chuyền mượt trong 6 giây tới vị trí người chiến thắng ngẫu nhiên, bắn pháo giấy chúc mừng, hiển thị hộp thoại xác nhận **Lưu kết quả & Đóng sự kiện** (gọi API lưu `winner_id`, đổi trạng thái sự kiện thành `ended` và quay về `/admin/giveaways`).

### 2.12. Nhật ký hành động Admin — `/admin/audit-log`
- Hiển thị thanh thông tin tổng số bản ghi gần nhất (chỉ đọc, không cho phép sửa/xóa).
- **Bảng nhật ký**: STT, Thời gian (ngày + giờ), Admin thực hiện (tên + email), Hành động (huy hiệu màu theo loại hành động), Đối tượng tác động (`Product #ID`, `User #ID`, `Category #ID`...), Ghi chú chi tiết.

---

## 3. Các lỗi và điểm không nhất quán phát hiện trong bản PHP

| # | Vấn đề trong bản PHP | Vị trí | Giải pháp trong bản Next.js |
|---|---|---|---|
| 1 | `User::all()` quên `SELECT strike_count`, khiến cột `X/3 Gậy` và tham số truyền vào modal Phạt Gậy ở trang `/admin/users` luôn bằng `0` | `User.php:172`, `users.php:128,151` | Trả đầy đủ `strikeCount` trong dữ liệu danh sách người dùng và đồng bộ ngay sau khi phạt gậy |
| 2 | Trang `ratings.php` đọc trường `$r['is_hidden']` trong khi DB và Model dùng cột `status` (`'visible'` / `'hidden'`), đồng thời form `ratings/toggle` quên gửi `_csrf` khiến bấm Ẩn/Hiện luôn bị lỗi CSRF | `ratings.php:38,70,82`, `Rating.php:87` | Chuẩn hóa trường `status: 'visible' \| 'hidden'` và xử lý bật/tắt trạng thái bình luận mượt mà |
| 3 | `users.php` khai báo `$durationOptions` (3 ngày -> Vĩnh viễn) và `AdminUserController::toggleUser()` có code khóa có thời hạn, nhưng giao diện `users.php` chỉ vẽ nút "Phạt Gậy" và "Mở khóa", thiếu nút mở modal Khóa thủ công | `users.php:16-24,147-172`, `AdminUserController.php:120` | Cung cấp đầy đủ cả 2 thao tác: **Phạt Gậy** (theo quy tắc 1-2-3 gậy) và **Khóa / Mở khóa** thủ công theo thời hạn |
| 4 | `products.php` có sẵn toàn bộ HTML + JS của `#exportModal` (`exportType="products"`) nhưng trên thanh tiêu đề lại quên đặt nút "Xuất Excel (CSV)" | `products.php:23-27,151-208` | Bổ sung nút "Xuất Excel (CSV)" trên header trang Kiểm duyệt bài đăng giống trang Người dùng |
| 5 | `AdminProductController::rejectProduct()` nhận `$_POST['reject_reason']` để gửi thông báo cho sinh viên, nhưng form ở `products.php` chỉ dùng `confirm()` mà không có ô nhập lý do từ chối | `AdminProductController.php:99`, `products.php:120` | Thay `confirm()` đơn điệu bằng Modal Từ chối bài đăng cho phép Admin nhập lý do từ chối (hoặc chọn lý do mẫu) |
| 6 | Menu Sidebar trên Mobile (`layouts/admin.php`) bị thiếu 4 mục (`Giveaways`, `Banners`, `Tố cáo vi phạm`, `Kiểm duyệt Đánh giá`) và không đánh dấu `.active` trang hiện tại | `layouts/admin.php:143-162` | Dùng chung một cấu hình menu 10 mục cho cả Sidebar Desktop và Mobile Drawer, tự động highlight đúng route đang mở |
| 7 | Thông báo Flash được gọi 2 lần (1 lần trong `layouts/admin.php:228` và 1 lần nữa trong `products.php`, `categories.php`, `users.php`, `banners.php`) | `layouts/admin.php` & các view con | Quản lý thông báo tập trung qua hệ thống Toast / Alert thống nhất, không lặp thông báo |
| 8 | `Category::makeSlug()` dùng `preg_replace('/[^a-z0-9\-]/', '', $slug)` làm mất toàn bộ chữ cái tiếng Việt có dấu (ví dụ `"Đồ điện tử"` biến thành `"in-t"`) | `Category.php:44-50` | Chuẩn hóa hàm `makeSlug` bỏ dấu tiếng Việt (`Đồ điện tử` -> `do-dien-tu`) trước khi lọc ký tự |
| 9 | Bảng `audit_log.php` chỉ định nghĩa nhãn cho 8 hành động cũ, thiếu `strike_user` và các thao tác xử lý tố cáo / ẩn đánh giá / banner / giveaway | `audit_log.php:8-17` | Mở rộng danh mục nhãn hành động trong Audit Log cho toàn bộ thao tác quản trị |
| 10 | Route quay số Giveaway dùng snake_case (`/admin/giveaway_spin`) lệch chuẩn kebab-case của toàn bộ hệ thống (`/admin/system-reports`, `/admin/audit-log`) | `index.php:258`, `giveaways.php:81` | Dùng route chuẩn `/admin/giveaway-spin` (kèm redirect tự động từ `/admin/giveaway_spin` nếu truy cập link cũ) |
| 11 | Trang `/admin/users` và `/admin/products` trong PHP chưa có ô tìm kiếm nhanh theo từ khóa | `users.php`, `products.php` | Bổ sung thêm thanh tìm kiếm nhanh trên client giúp Admin lọc nhanh theo tên/email/tiêu đề mà vẫn giữ nguyên cấu trúc bảng gốc |

---

## 4. Kế hoạch triển khai theo từng Phase trong Tuần 3

- **Phase 1 — Nền tảng Admin & Màn hình Dashboard**:
  - Định nghĩa kiểu dữ liệu (`lib/types/admin.ts`), kho dữ liệu giả lập có trạng thái (`lib/api/admin.ts` lưu trên `localStorage` để thao tác thêm/sửa/xóa/duyệt/khóa/quay số giữ nguyên khi chuyển trang, kèm nút khôi phục dữ liệu mẫu).
  - Xây dựng `AdminShell` (Sidebar 10 mục, Topbar, đổi giao diện Sáng/Tối, kiểm tra quyền `admin`, Toast thông báo, tính năng xuất CSV trực tiếp trên trình duyệt).
  - Hoàn thiện màn hình `/admin` (Dashboard).
- **Phase 2 — Quản lý Sản phẩm, Danh mục & Banner**:
  - Hoàn thiện `/admin/products` (2 tab, Duyệt, Modal Từ chối có nhập lý do, Xóa có kiểm tra ràng buộc giao dịch, Xuất CSV).
  - Hoàn thiện `/admin/categories` (Danh sách, Modal Thêm/Sửa với 10 icon chuẩn, tự sinh slug tiếng Việt chuẩn, chặn xóa khi có sản phẩm).
  - Hoàn thiện `/admin/banners` (Danh sách, bật/tắt hiển thị, Modal Thêm/Sửa banner, xóa banner).
- **Phase 3 — Quản lý Người dùng, Hồ sơ chi tiết & Hệ thống Gậy**:
  - Hoàn thiện `/admin/users` (Danh sách người dùng, Modal Phạt Gậy 1/2/3+, Modal Khóa tài khoản có thời hạn & Mở khóa, Xuất CSV).
  - Hoàn thiện `/admin/users/detail` (Hồ sơ cá nhân, thông tin khóa, 3 bảng Sản phẩm / Giao dịch / Tố cáo).
  - Hoàn thiện `/admin/users/violations` (Thẻ tổng số gậy `X/3` và bảng lịch sử nhận gậy).
- **Phase 4 — Báo cáo Giao dịch, Tố cáo, Đánh giá, Giveaway & Nhật ký Admin**:
  - Hoàn thiện `/admin/reports` (Bộ lọc Từ ngày - Đến ngày có kiểm tra hợp lệ, 3 thẻ thống kê, biểu đồ cột doanh số theo ngày, bảng giao dịch + dòng tổng cộng, Xuất CSV).
  - Hoàn thiện `/admin/system-reports` (4 tab trạng thái, Bỏ qua / Modal Xử lý tố cáo kèm ảnh bằng chứng & ghi chú).
  - Hoàn thiện `/admin/ratings` (Kiểm duyệt đánh giá 1-5 sao, Ẩn/Hiện bình luận).
  - Hoàn thiện `/admin/giveaways` và `/admin/giveaway-spin` (Tạo/xóa sự kiện, băng chuyền quay số 6 giây + hiệu ứng pháo giấy + lưu người trúng giải).
  - Hoàn thiện `/admin/audit-log` (Nhật ký hành động Admin tự động ghi lại các thao tác).
- **Phase 5 — Kiểm thử tự động (Vitest + Playwright), Đo tốc độ & Tài liệu**:
  - Unit test (Vitest) cho các hàm nghiệp vụ & validate của Admin.
  - E2E test & đo tốc độ phản hồi (Playwright) cho các luồng quản trị.
  - Hoàn thiện `docs/ADMIN_API_CONTRACT.md` và báo cáo tuần 3 (`docs/BAO_CAO_TUAN_3.md`).
