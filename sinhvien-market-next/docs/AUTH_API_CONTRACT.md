# AUTH_API_CONTRACT - Đặc tả API Xác thực

Tài liệu thống nhất giữa Frontend (`sinhvien-market-next/frontend`) và Backend Node.js. Kiểu dữ liệu phía Frontend nằm tại `lib/api/types.ts`. Các thông báo lỗi tiếng Việt giữ theo bản PHP gốc (`sinhvien-market-php`).

## 1. Quy ước chung
- **Base URL**: `NEXT_PUBLIC_API_URL` (mặc định `http://localhost:4000/api`).
- **Quản lý phiên (Session)**: dùng cookie `httpOnly` (`Secure` khi chạy HTTPS, `SameSite=Lax`, `Path=/`). Phía Frontend gọi `fetch` với `credentials: "include"`.
- **Chống CSRF**: Frontend gửi kèm header `X-Requested-With: XMLHttpRequest`. Backend cần từ chối (`403`) các request ghi (`POST`/`PUT`/`DELETE`) thiếu header này và kiểm tra `Origin`.
- **Định dạng dữ liệu**: JSON (`Content-Type: application/json`), tên trường viết theo `camelCase`.
- **Response thành công**: HTTP `200` (hoặc `201`) với cấu trúc:
  ```json
  { "data": <kết_quả> }
  ```
- **Response lỗi**: HTTP `4xx` / `5xx` với cấu trúc:
  ```json
  {
    "error": {
      "code": "<MÃ_LỖI>",
      "message": "<Thông báo tiếng Việt>",
      "fields": { "<tên_trường>": "<Thông báo lỗi của trường>" },
      "meta": { "attemptsLeft": 4, "minutesLeft": 15, "remaining": 4, "resendRemaining": 2 }
    }
  }
  ```

## 2. Danh sách mã lỗi (`ApiErrorCode`)

| Mã lỗi (`code`) | HTTP | Thông báo chuẩn (`message`) | `fields` / `meta` |
|---|---|---|---|
| `VALIDATION_ERROR` | 422 | `"Dữ liệu không hợp lệ."` | `fields` |
| `INVALID_CREDENTIALS` | 401 | `"Sai email hoặc mật khẩu. Còn {N} lần thử."` | `meta.attemptsLeft` |
| `RATE_LIMITED` | 429 | Thông báo giới hạn tương ứng (đăng nhập / đăng ký / gửi lại OTP) | `meta.minutesLeft`, `meta.resendRemaining` |
| `WRONG_ROLE` | 403 | `"Email này không có quyền hạn truy cập cổng đăng nhập này."` | - |
| `NO_VERIFY_SESSION` | 401 | `"Không có phiên xác minh. Vui lòng đăng nhập lại."` | - |
| `OTP_INVALID` | 400 | `"Mã OTP không chính xác. Còn {N} lần thử."` | `meta.remaining` |
| `OTP_EXPIRED` | 400 | `"Mã OTP đã hết hạn. Vui lòng nhấn Gửi lại mã."` | - |
| `OTP_SESSION_CANCELLED` | 403 | `"Quá nhiều lần nhập sai. Phiên xác minh bị hủy."` | - |
| `EMAIL_NOT_FOUND` | 404 | `"Email không tồn tại."` | - |
| `NO_SECURITY_QUESTION` | 404 | `"Email không tồn tại hoặc chưa cài đặt câu hỏi bảo mật."` | - |
| `SECURITY_ANSWER_INCORRECT` | 400 | `"Câu trả lời bảo mật không chính xác!"` | - |
| `RESET_LINK_INVALID` | 400 | `"Liên kết hết hạn hoặc sai."` | - |
| `NOT_AUTHENTICATED` | 401 | `"Bạn cần đăng nhập để truy cập trang này."` | - |
| `NOT_LOCKED` | 409 | `"Tài khoản không bị khoá."` | - |
| `NOT_IMPLEMENTED` | 501 | `"Đăng nhập bằng Google sẽ sớm được hỗ trợ."` | - |

## 3. Quy tắc kiểm tra dữ liệu
- **Email**: đúng định dạng email, chuẩn hoá `trim()` và không phân biệt hoa/thường.
- **Mật khẩu** (áp dụng cho cả Đăng ký và Đặt lại mật khẩu):
  - Tối thiểu `8` ký tự (`"Mật khẩu phải có ít nhất 8 ký tự."`).
  - Có ít nhất `1` chữ hoa `[A-Z]` và `1` chữ số `[0-9]` (`"Mật khẩu phải chứa ít nhất 1 chữ hoa và 1 chữ số."`).
- **Họ và tên (`name`)**: từ `2` đến `100` ký tự sau khi `trim()`.
- **Số điện thoại (`phone`)**: tuỳ chọn (cho phép chuỗi rỗng `""`), nếu có phải khớp `^(0|\+84)[0-9]{8,10}$`.
- **Câu hỏi & câu trả lời bảo mật**:
  - `securityQuestion` nhận một trong 3 giá trị `"q1" | "q2" | "q3"`:
    - `q1`: `"Tên trường cấp 1 của bạn là gì?"`
    - `q2`: `"Tên thú cưng đầu tiên của bạn?"`
    - `q3`: `"Bạn thân thời thơ ấu của bạn tên gì?"`
  - `securityAnswer`: chuẩn hoá `trim().toLowerCase()` trước khi băm và khi so sánh.
- **Giới hạn tần suất (Rate Limit)**:
  - Đăng nhập: tối đa `5` lần sai / `15` phút theo IP.
  - Đăng ký: tối đa `3` lần tạo tài khoản / `10` phút theo IP phía server.
  - Nhập OTP: tối đa `5` lần sai trong một phiên xác minh.
  - Gửi lại OTP: tối đa `3` lần / `10` phút.
- **Thời hạn mã**: OTP 6 chữ số (hết hạn 15 phút); token đặt lại mật khẩu hết hạn 15 phút, chỉ dùng 1 lần và tách riêng khỏi cột OTP.

## 4. Chi tiết các Endpoint

### 4.1. `POST /auth/login`
- **Request**: `{ "role": "student" | "admin", "email": string, "password": string }`
- **Thứ tự xử lý**: (1) kiểm tra giới hạn IP -> (2) validate dữ liệu -> (3) kiểm tra email/mật khẩu (sai thì tăng bộ đếm, lần thứ 5 trả `RATE_LIMITED`) -> (4) kiểm tra khớp vai trò (`WRONG_ROLE`, không tăng bộ đếm) -> (5) kiểm tra khóa tài khoản -> (6) kiểm tra xác minh OTP -> (7) tạo phiên đăng nhập.
- **Response `data`** (1 trong 3 dạng):
  ```jsonc
  // Thành công
  {
    "status": "ok",
    "user": { "id": 1, "name": "Nguyễn Minh Anh", "email": "student@demo.vn", "role": "student" },
    "redirectTo": "/products", // hoặc "/admin" nếu role === "admin"
    "message": "Chào mừng trở lại, Nguyễn Minh Anh!"
  }

  // Chưa xác minh OTP
  {
    "status": "verify_otp",
    "email": "unverified@demo.vn",
    "message": "Xác minh bảo mật: Mã OTP đang được gửi vào email của bạn."
  }

  // Tài khoản bị khóa
  {
    "status": "locked",
    "lock": {
      "name": "Lê Thu Hà",
      "email": "locked@demo.vn",
      "reason": "Đăng tin lừa đảo nhiều lần",
      "lockedAt": "2026-10-15T09:30:00+07:00",
      "lockedUntil": null
    }
  }
  ```

### 4.2. `POST /auth/register`
- **Request**: `{ name, email, phone, securityQuestion, securityAnswer, password, passwordConfirm }`
- **Response `data`**: `{ "status": "verify_otp", "email": string, "message": "Đăng ký thành công! Vui lòng nhập mã OTP đã được gửi đến email ..." }`
- **Lỗi**: `VALIDATION_ERROR` (trả đầy đủ lỗi của tất cả các trường cùng lúc, gồm cả `"Email này đã tồn tại."`), `RATE_LIMITED`.

### 4.3. `GET /auth/otp/session`
- **Response `data`**: `{ "email": string, "resendRemaining": 0..3 }`
- **Lỗi**: `NO_VERIFY_SESSION`.

### 4.4. `POST /auth/otp/verify`
- **Request**: `{ "otp": "123456" }`
- **Response `data`**: `{ "user": AuthUser, "redirectTo": "/products", "message": "Xác minh thành công! Chào mừng bạn." }`
- **Lỗi**: `VALIDATION_ERROR`, `OTP_INVALID`, `OTP_EXPIRED`, `OTP_SESSION_CANCELLED`, `NO_VERIFY_SESSION`.

### 4.5. `POST /auth/otp/resend`
- **Response `data`**: `{ "resendRemaining": 0..3, "message": "Đã gửi lại mã OTP. Vui lòng kiểm tra email." }`
- **Lỗi**: `RATE_LIMITED`, `NO_VERIFY_SESSION`.

### 4.6. `POST /auth/password/forgot/question` (Bước 1)
- **Request**: `{ "email": string }`
- **Response `data`**: `{ "email": string, "question": string }`
- **Lỗi**: `VALIDATION_ERROR`, `EMAIL_NOT_FOUND`, `NO_SECURITY_QUESTION`.
- *Lưu ý*: Tạm thời giữ thông báo `"Email không tồn tại."` giống bản PHP; khi triển khai thực tế Backend có thể cân nhắc trả thông báo chung kết hợp giới hạn tần suất theo IP để tránh dò email.

### 4.7. `POST /auth/password/forgot/answer` (Bước 2)
- **Request**: `{ "email": string, "securityAnswer": string }`
- **Response `data`**: `{ "message": "Hãy kiểm tra email để lấy liên kết đổi mật khẩu." }` (Backend gửi email chứa link `/reset-password?token=...&email=...`; trường `devResetLink` chỉ có ở bản mock).
- **Lỗi**: `SECURITY_ANSWER_INCORRECT`, `EMAIL_NOT_FOUND`.

### 4.8. `POST /auth/password/reset`
- **Request**: `{ email, token, password, passwordConfirm }`
- **Response `data`**: `{ "message": "Đổi mật khẩu thành công! Bạn có thể đăng nhập ngay." }`
- **Lỗi**: `RESET_LINK_INVALID`, `VALIDATION_ERROR`.

### 4.9. `GET /auth/locked-info`
- **Response `data`**: `LockInfo` (`{ name?, email, reason, lockedAt, lockedUntil }`).
- **Lỗi**: `NOT_AUTHENTICATED`, `NOT_LOCKED`.

### 4.10. `GET /auth/me` & `POST /auth/logout`
- `GET /auth/me`: trả `AuthUser` hoặc lỗi `NOT_AUTHENTICATED` (401).
- `POST /auth/logout`: xoá phiên trên server và trả `{ "message": "Đã đăng xuất thành công." }`.
