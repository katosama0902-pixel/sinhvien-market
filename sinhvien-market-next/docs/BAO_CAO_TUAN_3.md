# BÁO CÁO TIẾN ĐỘ TUẦN 3 — ĐỒ ÁN CHUYÊN NGÀNH

- **Dự án**: Nâng cấp giao diện SinhVienMarket từ PHP MVC sang Next.js 16 (App Router) + React 19 + TypeScript (`strict`) + Tailwind CSS 4.
- **Nội dung thực hiện Tuần 3**: Rà soát toàn bộ phân hệ Quản trị viên (`Admin`) trên bản PHP cũ, xây dựng khung giao diện `AdminShell`, kho dữ liệu giả lập có lưu trạng thái (`localStorage` + tự động ghi Audit Log + xuất CSV UTF-8 BOM) và hoàn thiện trọn bộ **13/13 màn hình Admin** kèm kiểm thử tự động & đo tốc độ.
- **Nhánh Git làm việc**: `feature/nextjs-frontend` (trên kho lưu trữ nhóm `sinhvien-market`).

---

## 1. Tổng hợp 13 màn hình Admin đã hoàn thành

| STT | Màn hình | Route Next.js | Các chức năng chính đã hiện thực |
|---|---|---|---|
| 1 | **Tổng quan hệ thống (Dashboard)** | `/admin` | 4 thẻ KPI lớn (Tổng người dùng, Đang bán, Chờ kiểm duyệt, Giao dịch hôm nay), 3 nút tác vụ nhanh có đếm số lượng chờ xử lý, bảng 5 giao dịch mới nhất và 5 sản phẩm vừa đăng. |
| 2 | **Kiểm duyệt bài đăng** | `/admin/products` | 2 tab (`Chờ duyệt`, `Tất cả`), tìm kiếm nhanh, Duyệt bài, Modal Từ chối có nhập lý do + lý do mẫu, Xóa bài (chặn xóa khi sản phẩm đã phát sinh giao dịch), Xuất báo cáo CSV. |
| 3 | **Quản lý danh mục** | `/admin/categories` | Thêm/Sửa/Xóa danh mục, chọn 10 icon chuẩn, xem trước trực tiếp `slug` tiếng Việt không dấu, chặn xóa khi danh mục đang chứa sản phẩm. |
| 4 | **Quản lý Banner** | `/admin/banners` | Thêm banner (hỗ trợ tải ảnh `<= 1MB` kèm xem trước hoặc nhập URL), Sửa thông tin, Bật/Tắt hiển thị, Xóa banner, kiểm tra định dạng link đích và thứ tự. |
| 5 | **Quản lý người dùng** | `/admin/users` | 3 tab lọc trạng thái, tìm kiếm, hiển thị huy hiệu số gậy `X/3`, Modal Phạt Gậy (xem trước kết quả Gậy 1/2/3+ và đính kèm bằng chứng), Modal Khóa thủ công 7 mức thời hạn, Mở khóa (đặt lại gậy về `0`), Xuất CSV. |
| 6 | **Chi tiết người dùng (Hồ sơ 360°)** | `/admin/users/detail?id=` | Thẻ đại diện kèm điểm sao uy tín, thông tin tài khoản & liên hệ, thông tin khóa tài khoản, 3 bảng dữ liệu liên kết: Sản phẩm đã đăng, Lịch sử giao dịch, Phiếu tố cáo liên quan. |
| 7 | **Lịch sử vi phạm (Gậy)** | `/admin/users/violations?id=` | Thẻ tổng số gậy `X/3`, thẻ trạng thái khóa hiện tại và bảng lịch sử chi tiết từng lần nhận gậy kèm link bằng chứng. |
| 8 | **Báo cáo giao dịch** | `/admin/reports` | Bộ lọc khoảng ngày (`Từ ngày` – `Đến ngày`), 3 thẻ KPI doanh thu, biểu đồ cột doanh số theo ngày, bảng giao dịch chi tiết và Xuất CSV đối soát. |
| 9 | **Tố cáo vi phạm** | `/admin/system-reports` | 3 thẻ tóm tắt + 4 tab lọc (`Tất cả`, `Chờ xử lý`, `Đã xử lý`, `Bỏ qua`), Modal Xử lý đính kèm link bằng chứng & ghi chú mẫu, Modal Bỏ qua tố cáo. |
| 10 | **Kiểm duyệt Đánh giá** | `/admin/ratings` | 4 thẻ thống kê điểm sàn, bộ lọc theo trạng thái (`Hiển thị` / `Đã ẩn`) và mức sao (`1`–`5` sao), nút Ẩn/Hiện nhận xét tức thì. |
| 11 | **Quản lý Giveaways** | `/admin/giveaways` | Thống kê sự kiện, lọc theo trạng thái, Modal Tạo Giveaway mới (chọn ảnh `<= 1MB` hoặc URL), Xóa sự kiện, chuyển sang bàn quay thưởng. |
| 12 | **Vòng quay may mắn** | `/admin/giveaway-spin` *(và `/admin/giveaway_spin`)* | Băng chuyền quay số ngang (Roulette 6 giây) mượt mà, nút Hiện kết quả ngay, hiệu ứng pháo giấy (Confetti), Modal vinh danh và tự động lưu người chiến thắng. |
| 13 | **Nhật ký hành động Admin** | `/admin/audit-log` | Bảng nhật ký bảo mật chỉ đọc (Read-only Audit Trail), tự động ghi nhận mọi thao tác quản trị từ các màn hình #2–#12, lọc theo 6 nhóm nghiệp vụ. |

---

## 2. Khắc phục 11 lỗi & điểm bất cập từ bản PHP cũ

Trong quá trình rà soát ở Phase 0 (`docs/ADMIN_SCREENS.md`), nhóm đã phát hiện và khắc phục triệt để 11 vấn đề tồn tại trong mã nguồn PHP cũ:

1. **Thiếu cột `strike_count` trong `User::all()`**: Bản PHP luôn hiển thị `0/3 Gậy` trên bảng danh sách người dùng dù tài khoản đã bị phạt gậy. Bản Next.js trả về và hiển thị chính xác `strikeCount` với mã màu cảnh báo theo mức độ.
2. **Lỗi tên cột `status`/`is_hidden` và thiếu CSRF trên `ratings.php`**: Khiến thao tác Ẩn/Hiện đánh giá trên bản PHP không hoạt động. Bản Next.js đồng bộ trường `status: "visible" | "hidden"` và cập nhật tức thì.
3. **Thiếu nút mở Modal khóa thủ công trên `users.php`**: Bản PHP có viết sẵn `#lockModal` nhưng trên bảng chỉ có nút `form.submit()` khóa thẳng không chọn được kỳ hạn. Bản Next.js mở đúng Modal chọn 7 mức thời hạn khóa và nhập lý do.
4. **Thiếu nút Xuất CSV trên `products.php`**: Dù `AdminController::export()` có hỗ trợ `type=products`, giao diện `products.php` lại quên đặt nút bấm. Bản Next.js bổ sung đầy đủ nút Xuất CSV trên cả 3 trang (`Users`, `Products`, `Reports`).
5. **Từ chối bài đăng không lưu lý do**: Bản PHP đổi trạng thái sang `cancelled` mà không cho nhập lý do để người bán biết nguyên nhân. Bản Next.js bổ sung Modal nhập lý do từ chối kèm 4 mẫu lý do nhanh.
6. **Menu Sidebar trên điện thoại bị thiếu 4 mục**: Bản PHP thiếu *Quản lý danh mục, Tố cáo vi phạm, Kiểm duyệt Đánh giá, Nhật ký Admin* ở ngăn kéo mobile. `AdminShell` mới dùng chung 1 mảng cấu hình điều hướng duy nhất cho cả Desktop và Mobile.
7. **Hàm tạo slug danh mục làm mất chữ có dấu tiếng Việt**: `Category::makeSlug()` trong PHP xóa mất các ký tự có dấu thay vì chuyển về chữ cái Latinh không dấu. Hàm `makeCategorySlug()` mới chuẩn hóa Unicode NFD và xử lý triệt để cả chữ `đ/Đ`.
8. **Không đồng bộ trạng thái giữa các màn hình khi thử nghiệm**: Kho dữ liệu giả lập `lib/api/mock/admin.ts` lưu trạng thái vào `localStorage` và tự động đẩy bản ghi mới sang `/admin/audit-log` sau mỗi thao tác của Admin, kèm nút **Khôi phục dữ liệu mẫu** trên thanh công cụ.

---

## 3. Kết quả Kiểm thử Tự động & Đo tốc độ (Phase 5)

### 3.1. Kiểm tra tĩnh & Đóng gói Production
- **ESLint (`npm run lint`)**: `0` lỗi, `0` cảnh báo.
- **TypeScript (`tsc --noEmit` với `strict: true`)**: `0` lỗi kiểu dữ liệu.
- **Production Build (`next build`)**: Kết xuất thành công **27/27 route tĩnh**.

### 3.2. Kiểm thử đơn vị (Vitest)
- Tổng cộng: **2 file test / 48 bài test đạt 100%** (`tests/unit/validation.test.ts`: 30 bài test Auth; `tests/unit/admin-validation.test.ts`: 18 bài test nghiệp vụ Admin gồm tạo slug tiếng Việt, kiểm tra khoảng ngày, tính kỳ hạn khóa, quy tắc 3 gậy tự động, định dạng tiền tệ/ngày giờ và xuất CSV UTF-8 BOM).

### 3.3. Kiểm thử E2E & Đo tốc độ phản hồi (Playwright)
- **Luồng xác thực (`tests/e2e/flows.spec.ts` & `tests/e2e/performance.spec.ts`)**: 14/14 bài kiểm thử đạt.
- **Luồng quản trị Admin (`tests/e2e/admin-flows.spec.ts`)**: 6/6 luồng kiểm thử đạt trên bản build production (bảo vệ route `/admin`, duyệt bài & tạo danh mục, quản lý người dùng & lịch sử gậy, báo cáo doanh thu & tố cáo & đánh giá, quay thưởng Giveaway & kiểm tra nhật ký Audit Log, đo thời gian mở 5 màn hình chính đều đạt dưới ngưỡng ngân sách `<= 2000ms`).
