# AUTH_SCREENS — Rà soát màn hình xác thực (Phase 0)

Nguồn đã đọc: `app/views/auth/*` (8 view), `AuthController.php`, `OtpController.php`, `PasswordResetController.php`, `core/Middleware.php`, `app/models/LoginAttempt.php`, `index.php` (route). Không sửa gì trong `sinhvien-market-php/`.

## 0. Bảng tổng hợp

| # | View PHP | Route PHP | Route Next.js | Phase |
|---|---|---|---|---|
| 1 | `login.php` | `GET/POST /login` | `/login` | 2 |
| 2 | `register.php` | `GET/POST /register` | `/register` | 2 |
| 3 | `forgot_password.php` | `GET/POST /forgot-password` | `/forgot-password` (2 bước) | 2 |
| 4 | `admin_login.php` | `GET/POST /admin-login` | `/admin-login` | 3 |
| 5 | `login_role.php` | `GET /login-role` | `/login-role` | 4 |
| 6 | `verify_otp.php` | `GET/POST /verify-otp`, `GET /resend-otp` | `/verify-otp` | 4 |
| 7 | `reset_password.php` | `GET/POST /reset-password?token&email` | `/reset-password` | 4 |
| 8 | `account_locked.php` | `GET /account-locked` | `/account-locked` | 4 |

Trang đích ngoài phạm vi (cần route tạm để chuyển tới): sinh viên thành công → `/products`; admin thành công → `/admin`. Hai trang này làm **stub** (chỉ hiện "Đăng nhập thành công").

Quy ước chung của PHP: mọi form có CSRF `_csrf`; thông báo hiển thị bằng Flash (`danger` / `success` / `info` / `warning`) ở đầu form; nút submit bị disable + spinner khi gửi. `requireGuest()`: đã đăng nhập mà vào các trang này thì bị đẩy sang `/products`.

---

## 1. Đăng nhập sinh viên — `/login`

**Trường**

| Trường | Kiểu | Validate | Lỗi |
|---|---|---|---|
| `email` | email, `autocomplete=email`, required | `FILTER_VALIDATE_EMAIL` | "Email không hợp lệ." |
| `password` | password (có nút hiện/ẩn), required | không rỗng | "Vui lòng nhập mật khẩu." |

**Thành phần khác**: link "Quên mật khẩu?" → `/forgot-password`; nút "Tiếp tục với Google" (`/auth/google`); danh sách "Tài khoản gần đây" (tối đa 3, chỉ tên + email, bấm để điền email và focus mật khẩu, có nút × để xoá); link "Đăng ký ngay" → `/register`; link "Quay lại cổng chính" → `/login-role`.

**Thứ tự xử lý của server (quan trọng cho mock)**
1. CSRF sai → về `/login`.
2. IP đã sai ≥ 5 lần trong 15 phút → lỗi `rate_limit`: "Quá nhiều lần thất bại. Thử lại sau {N} phút." (không kiểm tra gì tiếp).
3. Validate email/mật khẩu (lỗi từng trường).
4. Sai email hoặc mật khẩu → tăng bộ đếm. Còn lượt: "Sai email hoặc mật khẩu. Còn {N} lần thử." Hết lượt (lần thứ 5): "Tài khoản bị cấm 15 phút do sai quá nhiều."
5. Đúng mật khẩu nhưng sai vai trò → "Email này không có quyền hạn truy cập cổng đăng nhập này." (không tăng bộ đếm).
6. Tài khoản bị khoá → `/account-locked`.
7. Chưa xác minh (`is_verified = 0`) → tạo OTP, gửi mail, → `/verify-otp` (flash info "Xác minh bảo mật: …"). Nếu gửi mail lỗi → vẫn đăng nhập, flash warning "Hệ thống không thể gửi OTP lúc này. Bạn vẫn được đăng nhập bình thường."
8. Thành công → reset bộ đếm, flash "Chào mừng trở lại, {tên}!" → `/products`.

**Trạng thái UI cần có**: idle, đang gửi, lỗi từng trường, lỗi chung, bị giới hạn (hiện đếm phút, khoá form), thành công (chuyển trang).

---

## 2. Đăng ký — `/register`

| Trường | Kiểu | Validate | Lỗi |
|---|---|---|---|
| `name` | text, required | 2–100 ký tự | "Họ tên từ 2 - 100 ký tự." |
| `email` | email, required | hợp lệ; chưa tồn tại | "Email không hợp lệ." / "Email này đã tồn tại." |
| `phone` | text, tuỳ chọn | `^(0\|\+84)[0-9]{8,10}$` nếu có nhập | "Số điện thoại không hợp lệ." |
| `security_question` | select, required | `q1`/`q2`/`q3` | "Vui lòng điền câu hỏi và câu trả lời bảo mật." |
| `security_answer` | text, required | không rỗng | (cùng lỗi trên) |
| `password` | password, required, `minlength=8` | ≥ 8 ký tự; có ≥ 1 chữ hoa và ≥ 1 chữ số | "Mật khẩu phải có ít nhất 8 ký tự." / "Mật khẩu phải chứa ít nhất 1 chữ hoa và 1 chữ số." |
| `password_confirm` | password, required | khớp `password` | "Mật khẩu xác nhận không khớp." |

Câu hỏi bảo mật (giá trị → nhãn): `q1` "Tên trường cấp 1 của bạn là gì?", `q2` "Tên thú cưng đầu tiên của bạn?", `q3` "Bạn thân thời thơ ấu của bạn tên gì?".

**Thanh độ mạnh mật khẩu** (JS PHP): điểm 0–4 = (≥8 ký tự) + (có hoa) + (có số) + (có ký tự đặc biệt); nhãn Yếu / Trung bình / Khá mạnh / Mạnh; màu `#ef4444`, `#f59e0b`, `#3b82f6`, `#10b981`; chiều rộng = điểm × 25%.

**Giới hạn**: tối đa 3 lần đăng ký thành công / 10 phút → "Bạn đã tạo quá nhiều tài khoản. Vui lòng đợi {N} phút rồi thử lại."
**Lỗi chung**: "Phiên làm việc hết hạn. Vui lòng thử lại." (CSRF), "Không thể tạo tài khoản lúc này."
**Thành công**: flash "Đăng ký thành công! Vui lòng nhập mã OTP đã được gửi đến email {email}." → `/verify-otp`. Có nút Google và link "Đăng nhập".

---

## 3. Quên mật khẩu — `/forgot-password` (2 bước)

**Bước 1**: trường `email` (placeholder `you@student.edu.vn`). Email không tồn tại → "Email không tồn tại." Có → lưu email vào session, hiện bước 2.
**Bước 2**: hiện câu hỏi bảo mật của tài khoản và trường `security_answer`. Câu trả lời được chuẩn hoá `trim + lowercase` trước khi so sánh.
- Sai → "Câu trả lời bảo mật không chính xác!" (ở lại bước 2).
- Đúng → tạo token 15 phút, gửi mail, flash "Hãy kiểm tra email để lấy liên kết đổi mật khẩu." → `/login`.
- Tài khoản không có câu hỏi → "Email không tồn tại hoặc chưa cài đặt câu hỏi bảo mật." (quay về bước 1).

Link "Quay lại đăng nhập" → `/login`.

## 4. Đăng nhập admin — `/admin-login`

Giao diện tối (nền gradient tối, kính mờ, tông hồng). Nhãn "EMAIL QUẢN TRỊ" (placeholder `admin@sinhvienmarket.vn`), "MẬT KHẨU". Ghi chú bảo mật: "Hệ thống bảo mật theo dõi mọi lần đăng nhập. Tối đa 5 lần sai / 15 phút." Link "Quay lại cổng chính" → `/login-role`.
Dùng **cùng** `processLogin` với `/login` (cùng giới hạn, cùng thông báo; vai trò mong đợi `admin`; thành công → `/admin`). Không có nút Google, không có "tài khoản gần đây".

## 5. Chọn vai trò — `/login-role`

Hai thẻ: "Tài khoản Sinh Viên" → `/login`; "Tài khoản Admin" → `/admin-login`. Nền gradient tối + blob động, hiệu ứng hover nâng thẻ. Không có form/dữ liệu.

## 6. Xác minh OTP — `/verify-otp`

Điều kiện vào: phải có phiên xác minh (`verify_email`), nếu không → `/login`.
- Trường `otp_code`: text, `maxlength=6`, placeholder `------`, `autocomplete=off`, required.
- Sai mã → "Mã OTP không chính xác. Còn {N} lần thử." (tối đa 5 lần/phiên). Hết lượt → "Quá nhiều lần nhập sai. Phiên xác minh bị hủy." → `/login`.
- Hết hạn (15 phút) → "Mã OTP đã hết hạn. Vui lòng nhấn Gửi lại mã."
- Đúng → "Xác minh thành công! Chào mừng bạn." → `/products`. Email đuôi edu tự động được huy hiệu sinh viên.
- Gửi lại: tối đa 3 lần / 10 phút, hiện "Còn N/3 lượt gửi lại"; hết lượt: "Đã dùng hết lượt. Đợi 10 phút để gửi lại." (server: "Bạn đã gửi lại OTP quá nhiều lần. Vui lòng đợi {N} phút rồi thử lại."). Gửi lại thành công: "Đã gửi lại mã OTP. Vui lòng kiểm tra email."

## 7. Đặt lại mật khẩu — `/reset-password?token=&email=`

- Thiếu `token` hoặc `email` → "Liên kết không hợp lệ." → `/login`.
- Trường: `password` (≥ 8 ký tự) và `password_confirm`, mỗi ô có nút hiện/ẩn. `email` và `token` là hidden.
- Token sai hoặc quá 15 phút → "Liên kết hết hạn hoặc sai." → `/login`.
- Mật khẩu yếu hoặc không khớp → "Mật khẩu không hợp lệ hoặc không khớp." (ở lại trang).
- Thành công → "Đổi mật khẩu thành công! Bạn có thể đăng nhập ngay." → `/login`.

## 8. Tài khoản bị khoá — `/account-locked`

Điều kiện vào: có phiên và `is_locked = 1`. Chưa đăng nhập → `/login-role`. Không bị khoá (hoặc admin đã mở) → `/`.
Hiện: **Thời điểm bị khoá** (định dạng `HH:mm:ss ngày dd/MM/yyyy`, ẩn nếu rỗng), **Lý do khoá** (mặc định "Vi phạm điều khoản sử dụng"), **Thời hạn** (`locked_until` hoặc "Vĩnh viễn (liên hệ Admin để được hỗ trợ)"). Nút "Khiếu nại" (`mailto:admin@sinhvienmarket.vn?subject=Khiếu nại khóa tài khoản — {email}`) và "Đăng xuất".

---

## 9. Các điểm không nhất quán / rủi ro tìm thấy trong PHP

| # | Vấn đề | Vị trí | Đề xuất cho bản Next |
|---|---|---|---|
| 1 | Placeholder đăng nhập ghi "you@student.edu.vn **hoặc Số điện thoại**" nhưng server chỉ nhận email | `login.php:70`, `AuthController` | Ghi "Email" (đã trong yêu cầu) |
| 2 | Quy tắc mật khẩu lệch: đăng ký cần ≥8 + hoa + số; đặt lại chỉ cần ≥8 | `AuthController:325-329` vs `PasswordResetController:122` | Dùng quy tắc chặt cho cả hai (**cần leader chốt**) |
| 3 | Quên mật khẩu báo "Email không tồn tại" → lộ email nào có tài khoản | `PasswordResetController:59` | Ghi vào contract, backend sửa. Mock hiển thị thông báo chung |
| 4 | "Tài khoản gần đây" của PHP đọc cookie `_recent_user` do server đặt, không phải chỉ localStorage | `login.php` JS `checkNewLogin` | Next lưu `localStorage` (tên + email) sau khi đăng nhập thành công |
| 5 | Giới hạn đăng ký lưu **theo session** (khoá `md5(ip)` trong `$_SESSION`), xoá cookie là qua mặt được; chỉ đếm đăng ký thành công | `AuthController:286-355` | Contract ghi "giới hạn theo IP phía server" |
| 6 | Sai vai trò không tăng bộ đếm; lỗi "cấm 15 phút" ở lần 5 nhưng thông báo bị giới hạn lại dùng chữ "Thử lại sau N phút" (hai câu khác nhau) | `AuthController:106-140` | Giữ cả hai thông báo như PHP |
| 7 | Token đặt lại mật khẩu dùng chung cột `otp_code` với OTP xác minh; chưa thấy token bị huỷ sau khi dùng | `PasswordResetController:78-80` | Contract yêu cầu token dùng một lần |
| 8 | `forgot_password.php` còn gắn `?email=` vào action dù email đã chuyển sang session | `forgot_password.php:36` | Không bê sang Next |
| 9 | Hai email admin mặc định bỏ qua OTP (cứng trong code); email trên giao diện/`mailto` là `.vn`, tài khoản mặc định là `.com` | `AuthController:176`, `admin_login.php:89` | Chỉ ghi chú, không hiển thị email thật ngoài placeholder |
| 10 | Giới hạn OTP: sai tối đa 5 lần (yêu cầu ghi chung "số lần cho phép") | `OtpController:44` | Dùng 5 |
| 11 | Tài khoản bị khoá vẫn nhận session (`$_SESSION['user']`) để hiển thị trang khoá | `AuthController:153-168` | Next: API đăng nhập trả `status: "locked"` kèm thông tin khoá, không cần coi là đã đăng nhập |
| 12 | Nút Google chỉ là link `/auth/google` | `login.php:122` | Placeholder (đã trong yêu cầu) |

## 10. Quyết định mock (Phase 1)

Tài khoản demo theo prompt: `student@demo.vn / Demo1234`, `admin@demo.vn / Admin1234`, `unverified@demo.vn / Demo1234`, `locked@demo.vn / Demo1234`. OTP demo `123456`. Mock có độ trễ giả 300–800 ms và đếm sai đăng nhập để test trạng thái bị giới hạn.

## 11. Quyết định đã chốt (20/10/2026)

1. Mật khẩu: dùng quy tắc chặt (≥8 ký tự, ≥1 chữ hoa, ≥1 chữ số) cho cả đăng ký và đặt lại.
2. Quên mật khẩu: giữ như PHP ("Email không tồn tại." ở bước 1), chỉ là tạm thời. Vẫn ghi chú trong contract rằng backend nên cân nhắc không tiết lộ email có tồn tại hay không.
3. Trang đích `/products` và `/admin` làm stub.
