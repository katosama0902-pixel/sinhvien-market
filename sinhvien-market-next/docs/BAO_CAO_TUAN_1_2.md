# BÁO CÁO TIẾN ĐỘ ĐỒ ÁN CHUYÊN NGÀNH - TUẦN 1 & TUẦN 2

- **Đề tài**: Tái cấu trúc hệ thống Chợ Sinh Viên (**SinhVienMarket**) từ PHP MVC sang Next.js Frontend + Node.js Backend
- **Phạm vi phụ trách**: Giao diện và luồng xử lý nhóm **Màn hình Xác thực (Authentication - 8 màn hình)**
- **Thư mục mã nguồn**: `sinhvien-market-next/frontend/` (tách riêng với bản gốc `sinhvien-market-php/`)
- **Thời gian thực hiện**: Tuần 1 & Tuần 2 (báo cáo gộp do tuần 1 làm bù trong tuần 2)

## 1. Tổng hợp công việc đã hoàn thành

| Giai đoạn | Hạng mục công việc | Trạng thái |
|---|---|---|
| **Phase 0** | Khảo sát 8 view và các controller/middleware xác thực bản PHP; lập tài liệu [`docs/AUTH_SCREENS.md`](./AUTH_SCREENS.md) và ghi nhận 12 điểm chưa đồng nhất của bản cũ | Hoàn thành |
| **Phase 1** | Khởi tạo dự án Next.js 16 (App Router, TypeScript, Tailwind CSS 4); cấu hình bộ màu `#6366f1`, font `Plus Jakarta Sans`, chế độ Sáng/Tối; xây dựng các UI component dùng chung, Zod schema, API client và Mock API kèm 4 tài khoản demo | Hoàn thành |
| **Phase 2** | Xây dựng khung `AuthShell` và 3 màn hình sinh viên: Đăng nhập (`/login`), Đăng ký (`/register`), Quên mật khẩu 2 bước (`/forgot-password`) cùng 2 trang đích tạm (`/products`, `/admin`) | Hoàn thành |
| **Phase 3** | Xây dựng cổng Đăng nhập Quản trị viên (`/admin-login`) với giao diện nền tối riêng, hộp cảnh báo bảo mật (tối đa 5 lần sai / 15 phút) và kiểm tra quyền `role === "admin"` | Hoàn thành |
| **Phase 4** | Xây dựng 4 màn hình đi kèm: Chọn cổng đăng nhập (`/login-role`), Xác minh OTP 6 số (`/verify-otp`), Đặt lại mật khẩu (`/reset-password`), Thông báo khóa tài khoản (`/account-locked`) | Hoàn thành |
| **Phase 5** | Viết Unit Test (Vitest - 30 test) + E2E & đo tốc độ phản hồi (Playwright - 14 test); hoàn thiện [`docs/AUTH_API_CONTRACT.md`](./AUTH_API_CONTRACT.md) cho Backend và [`README.md`](../sinhvien-market-next/frontend/README.md) | Hoàn thành |

## 2. Các điểm cải tiến so với bản PHP cũ

1. **Đồng bộ quy tắc mật khẩu**: Áp dụng chung quy tắc tối thiểu 8 ký tự, có ít nhất 1 chữ hoa và 1 chữ số kèm thanh đo độ mạnh 4 mức cho cả trang Đăng ký (`/register`) và Đặt lại mật khẩu (`/reset-password`) (bản PHP cũ chỉ kiểm tra >= 8 ký tự ở trang đặt lại mật khẩu).
2. **Lưu trữ an toàn trên trình duyệt**: Mục *"Tài khoản gần đây"* tại `/login` chỉ lưu tên và email trong `localStorage`, không lưu mật khẩu hay token.
3. **Hiển thị đầy đủ lỗi validate cùng lúc**: Kết hợp Zod và React Hook Form để hiển thị đồng thời lỗi của tất cả các trường chưa hợp lệ trong một lần bấm gửi.
4. **Tách biệt tầng gọi API và Mock**: Các màn hình gọi qua `lib/api/auth.ts`; khi ghép với Backend Node.js chỉ cần đổi `NEXT_PUBLIC_USE_MOCK=false` trong `.env.local` mà không cần sửa lại giao diện.

## 3. Kết quả kiểm thử & Đo tốc độ xử lý (theo yêu cầu của Leader)

### 3.1. Unit Test (Vitest - `tests/unit/validation.test.ts`)
- **Kết quả**: `30 / 30` test đạt (thời gian chạy ~18ms).
- **Nội dung**: Kiểm tra các điều kiện validate của `loginSchema`, `registerSchema`, `forgotAnswerSchema`, `otpSchema`, `resetPasswordSchema` và hàm tính độ mạnh mật khẩu `getPasswordStrength`.

### 3.2. Kiểm thử luồng E2E (Playwright - `tests/e2e/flows.spec.ts`)
- **Kết quả**: `8 / 8` kịch bản đạt trên trình duyệt Chromium:
  1. Đăng nhập sinh viên thành công -> chuyển tới `/products`.
  2. Đăng nhập sai mật khẩu -> hiển thị thông báo lỗi và số lần thử còn lại.
  3. Đăng nhập tài khoản bị khóa (`locked@demo.vn`) -> chuyển tới `/account-locked` và hiển thị thông tin khóa.
  4. Đăng ký tài khoản mới -> chuyển sang `/verify-otp` -> thử nhập OTP sai -> nhập OTP đúng (`123456`) -> vào `/products`.
  5. Bấm đăng ký khi để trống -> hiện đầy đủ thông báo lỗi từng trường.
  6. Quên mật khẩu 2 bước -> mở liên kết đặt lại -> cập nhật mật khẩu mới tại `/reset-password` -> về `/login`.
  7. Truy cập `/reset-password` thiếu `token`/`email` -> tự động đẩy về `/login`.
  8. Kiểm tra phân quyền tại `/admin-login`: chặn tài khoản sinh viên, cho phép tài khoản `admin@demo.vn` vào `/admin`.

### 3.3. Bảng kết quả đo tốc độ phản hồi (`tests/e2e/performance.spec.ts`)

| Hạng mục đo (trên bản Production Build) | Kết quả đo | Ngưỡng yêu cầu | Đánh giá |
|---|---:|---:|:---:|
| Thời gian tải và sẵn sàng tương tác trang `/login` | **160 ms** | <= 2000 ms | **ĐẠT** |
| Thời gian tải và sẵn sàng tương tác trang `/register` | **78 ms** | <= 2000 ms | **ĐẠT** |
| Tốc độ hiển thị lỗi validate tại `/login` (đo trực tiếp trong trình duyệt) | **11 ms** | <= 150 ms | **ĐẠT** |
| Tốc độ hiển thị lỗi validate tại `/register` (đo trực tiếp trong trình duyệt) | **18 ms** | <= 150 ms | **ĐẠT** |
| Tổng thời gian Đăng nhập -> chuyển sang `/products` *(đã gồm 300-800ms giả lập mạng)* | **1099 ms** | <= 1500 ms | **ĐẠT** |
| Tổng thời gian Đăng ký -> chuyển sang `/verify-otp` *(đã gồm 300-800ms giả lập mạng)* | **1111 ms** | <= 1500 ms | **ĐẠT** |
| Khóa nút bấm ngay khi đang gửi (chống bấm đúp) | **Có (Disabled + Spinner)** | Bắt buộc | **ĐẠT** |

## 4. Kế hoạch tiếp theo
- Bàn giao tài liệu [`docs/AUTH_API_CONTRACT.md`](./AUTH_API_CONTRACT.md) cho thành viên phụ trách Backend Node.js để tích hợp API thật.
- Nhận phân công nhóm màn hình tiếp theo từ Leader.
