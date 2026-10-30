# SinhVienMarket Frontend (Next.js - Nhóm Màn Hình Xác Thực)

Giao diện nhóm màn hình xác thực (**Authentication**) của hệ thống **SinhVienMarket**, chuyển đổi từ bản PHP MVC (`sinhvien-market-php`) sang **Next.js 16 (App Router) + React 19 + TypeScript + Tailwind CSS 4 + Zod + React Hook Form**.

## 1. Danh sách các màn hình

| Đường dẫn | Mô tả | Tệp nguồn |
|---|---|---|
| `/` | Tự động chuyển hướng về `/login-role` | `app/page.tsx` |
| `/login-role` | Cổng chọn vai trò đăng nhập (Sinh Viên / Ban Quản Trị) | `app/login-role/page.tsx` |
| `/login` | Đăng nhập Sinh Viên (lưu tài khoản gần đây, giới hạn 5 lần sai / 15 phút) | `app/login/page.tsx` |
| `/register` | Đăng ký tài khoản Sinh Viên (đo độ mạnh mật khẩu 4 mức, giới hạn 3 lần / 10 phút) | `app/register/page.tsx` |
| `/forgot-password` | Quên mật khẩu 2 bước bằng câu hỏi bảo mật | `app/forgot-password/page.tsx` |
| `/reset-password` | Đặt lại mật khẩu mới từ liên kết (`?token=...&email=...`) | `app/reset-password/page.tsx` |
| `/verify-otp` | Xác minh mã OTP 6 chữ số (gửi lại tối đa 3 lần / 10 phút, sai tối đa 5 lần) | `app/verify-otp/page.tsx` |
| `/account-locked` | Thông báo tài khoản bị khóa kèm thời điểm, lý do và thời hạn mở lại | `app/account-locked/page.tsx` |
| `/admin-login` | Cổng đăng nhập nội bộ dành cho Quản trị viên (kiểm tra `role === "admin"`) | `app/admin-login/page.tsx` |
| `/products`, `/admin` | Trang đích tạm (stub) để kiểm tra luồng đăng nhập & đăng xuất | `app/products/page.tsx`, `app/admin/page.tsx` |

## 2. Hướng dẫn chạy trên Windows (Chế độ Mock)

1. Mở terminal tại thư mục `sinhvien-market-next/frontend`:
   ```powershell
   cd "sinhvien-market-next\frontend"
   npm install
   ```
2. Kiểm tra file `.env.local` (nếu chưa có thì copy từ `.env.example`):
   ```env
   NEXT_PUBLIC_USE_MOCK=true
   NEXT_PUBLIC_API_URL=http://localhost:4000/api
   ```
3. Chạy server dev:
   ```powershell
   npm run dev
   ```
4. Mở trình duyệt tại `http://localhost:3000`.

## 3. Tài khoản Demo có sẵn

| Vai trò / Trạng thái | Email | Mật khẩu | Câu trả lời bảo mật | Mã OTP | Kết quả khi đăng nhập |
|---|---|---|---|---|---|
| Sinh viên (Đã xác minh) | `student@demo.vn` | `Demo1234` | `demo` | - | Vào thẳng `/products` |
| Quản trị viên (Admin) | `admin@demo.vn` | `Admin1234` | `demo` | - | Vào `/admin` (từ `/admin-login`) |
| Sinh viên (Chưa xác minh OTP) | `unverified@demo.vn` | `Demo1234` | `demo` | `123456` | Chuyển sang `/verify-otp` |
| Sinh viên (Đang bị khóa) | `locked@demo.vn` | `Demo1234` | `demo` | - | Chuyển sang `/account-locked` |

## 4. Các lệnh kiểm tra & chạy test

```powershell
# Kiểm tra ESLint
npm run lint

# Kiểm tra kiểu TypeScript
npx tsc --noEmit

# Chạy Unit Test (Vitest)
npm test

# Chạy E2E Test & đo tốc độ phản hồi (Playwright)
npm run test:e2e
```

## 5. Kết nối với Backend Node.js thật

Khi ghép nối với Backend (xem đặc tả trong `docs/AUTH_API_CONTRACT.md`), đổi biến môi trường trong `.env.local`:

```env
NEXT_PUBLIC_USE_MOCK=false
NEXT_PUBLIC_API_URL=http://localhost:4000/api
```

Sau đó khởi động lại `npm run dev`.
