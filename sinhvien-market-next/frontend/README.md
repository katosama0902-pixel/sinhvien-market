# SinhVienMarket Next.js Frontend

Ứng dụng giao diện Next.js cho SinhVienMarket. Backend nghiệp vụ và MySQL vẫn
được giữ trong ứng dụng PHP MVC hiện tại; Next.js gọi backend qua `/api` và
rewrite cùng origin để chia sẻ cookie phiên.

## Trạng thái chuyển đổi

- Đã có các màn hình đăng nhập, đăng ký, xác minh OTP và giao diện quản trị.
- Trang chủ, danh sách và chi tiết sản phẩm hỗ trợ cả mock lẫn API PHP.
- Các luồng user đã có giao diện Next.js cho hồ sơ cá nhân/công khai, tin đăng,
  chat và đề nghị giá, lịch sử giao dịch, mua bán/đấu giá, thông báo, xu/điểm
  thưởng và wishlist.
- PHP có các JSON adapter cho đăng nhập/đăng ký/OTP/khôi phục mật khẩu, hồ sơ,
  marketplace và các luồng user. Các thao tác ghi dùng phiên PHP và CSRF.
- Trao đổi hàng hóa hiện chỉ hỗ trợ thỏa thuận qua chat; chưa tạo đơn trao đổi.
  Checkout không khởi tạo chuyển hướng qua cổng thanh toán. Google login và
  chỉnh sửa tin đăng cũng chưa được hỗ trợ.
- Các luồng mới đã được kiểm tra với dữ liệu mock. Chưa thể xác nhận tích hợp
  database/email thực tế nếu chưa cấu hình và chạy backend PHP.

## Chạy trên Windows

1. Mở terminal tại `sinhvien-market-next/frontend`:

   ```powershell
   npm install
   Copy-Item .env.example .env.local
   ```

2. Chế độ mock mặc định cho phép xem giao diện mà không cần backend. Chạy:

   ```powershell
   npm run dev
   ```

3. Mở `http://localhost:3000`.

Để gọi backend PHP, cấu hình `PHP_BACKEND_URL` trong `.env.local` thành URL gốc
của ứng dụng PHP (không có dấu `/` cuối), ví dụ URL Apache/Laragon đang dùng.
Giữ `NEXT_PUBLIC_API_URL=/api`; Next.js sẽ proxy các request `/api/*` đến PHP.
Đặt `NEXT_PUBLIC_USE_MOCK=false` khi backend PHP, MySQL (`pdo_mysql`) và cấu
hình email cần thiết đã sẵn sàng. Đăng ký/khôi phục mật khẩu cần gửi email;
auction checkout và các thao tác dữ liệu cần database hoạt động.

## Tài khoản mock

| Vai trò / trạng thái | Email | Mật khẩu | Câu trả lời bảo mật | OTP |
|---|---|---|---|---|
| Sinh viên đã xác minh | `student@demo.vn` | `Demo1234` | `demo` | - |
| Quản trị viên | `admin@demo.vn` | `Admin1234` | `demo` | - |
| Chưa xác minh OTP | `unverified@demo.vn` | `Demo1234` | `demo` | `123456` |
| Tài khoản bị khóa | `locked@demo.vn` | `Demo1234` | `demo` | - |

## Kiểm tra

```powershell
npm run lint
npx tsc --noEmit
npm test
npm run test:e2e
```

Payment callbacks/IPN tiếp tục được xử lý bởi backend PHP; không chuyển xử lý
thanh toán sang trình duyệt Next.js. Exchange checkout trả lỗi rõ ràng thay vì
tạo giao dịch giả.
