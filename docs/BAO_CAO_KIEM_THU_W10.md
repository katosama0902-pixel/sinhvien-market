# BÁO CÁO KIỂM THỬ TOÀN HỆ THỐNG — TUẦN 10–11

- **Đề tài:** SinhVien-Market: Nền tảng số hóa ký túc xá tích hợp sàn trao đổi sinh viên, ứng dụng AI đa phương thức ghép nối nhu cầu – sản phẩm dựa trên ngữ cảnh lưu trú
- **Nhánh:** `merge/tran-ui-into-integration`
- **Ngày kiểm thử:** 27/11/2026
- **Người thực hiện:** Lê Lương Đại Hợp, Nguyễn Bảo Trân

---

## 1. Mục tiêu và phạm vi

Kiểm tra toàn bộ chức năng trên hệ thống chạy thật, đi từ thao tác đơn giản đến phức tạp:

- **Thao tác cơ bản:** xem tin, đăng ký, đăng nhập, đăng tin.
- **Giao dịch:** mua bán, chat, trả giá, thanh toán, đánh giá.
- **Tìm kiếm AI đa phương thức:** tìm bằng mô tả và bằng ảnh.
- **Ký túc xá:** phân hệ sinh viên và ban quản lý.
- **Quản trị và bảo mật.**

Lỗi phát hiện trong quá trình kiểm thử được sửa ngay, sau đó toàn bộ bộ kiểm thử được chạy lại.

Không dùng dữ liệu giả lập (mock). Gemini, Pusher và cơ sở dữ liệu đều là dịch vụ thật.

## 2. Môi trường

| Thành phần | Cấu hình |
|---|---|
| CSDL | MariaDB 10.4 (CSDL demo `sinhvien_market_demo`, khôi phục về bản chuẩn trước mỗi lần chạy) |
| API | PHP 8.3, `php -S` cổng 8081 |
| Giao diện | Next.js 16 bản build production (`next start`) cổng 3000 |
| AI | Gemini Embedding 2 (768 chiều) + BM25 tiếng Việt; trợ lý nội quy và phân loại yêu cầu sửa chữa chạy cục bộ |
| Trình duyệt | Chromium (Playwright): máy tính 1366×860 và điện thoại 390×844 |

**Dữ liệu chuẩn (baseline):**

- **Tài khoản:** 26 tài khoản, gồm 20 sinh viên test, quản lý KTX, nhân viên kỹ thuật và admin QA.
- **Tin đăng:** 111 tin, trong đó 73 tin có ảnh thật giấy phép tự do.
- **Vector:** 111 vector văn bản và 73 vector ảnh.
- **Ký túc xá:**
  - 12 hóa đơn tháng 11;
  - 6 phiếu sửa chữa;
  - 3 ki-ốt;
  - 2 hồ sơ lưu trú chờ duyệt;
  - 6 giường trống;
  - 3 thông báo.

## 3. Bộ kiểm thử

| Bộ kiểm thử | Tệp | Nội dung |
|---|---|---|
| Kiểm thử API đầu-cuối | `scripts/qa/full_e2e.py` | 260 kiểm tra, 11 nhóm A–K, gọi API qua cổng giao diện (có CSRF, cookie phiên) |
| Kiểm thử giao diện | `scripts/qa/ui_e2e.cjs` | 25 kịch bản thao tác bằng trình duyệt; dò lỗi 45 trang theo 4 vai trò; kiểm tra màn hình 390px; chụp 24 ảnh màn hình |
| Unit test backend | `tests/Unit` (PHPUnit) | 105 test |
| Unit test frontend | Vitest | 57 test |
| Kiểm tra tĩnh | ESLint, TypeScript, `next build` | 0 lỗi |

Cách chạy lại: xem mục 8.

## 4. Kết quả tổng hợp (lần chạy cuối)

| Nhóm | Nội dung chính | Kết quả |
|---|---|---|
| A. Khách vãng lai | Trang chủ, danh mục, phân trang 12 tin, lọc giá, chi tiết, ảnh sản phẩm, chặn truy cập trang cá nhân | **19/19** |
| B. Đăng ký – OTP – đăng nhập | Đăng ký (5 trường hợp sai), OTP sai/đúng, chưa xác minh thì chưa có phiên, đăng xuất, sai mật khẩu, quên mật khẩu, tách cổng admin | **27/27** |
| C. Hồ sơ | Xem/sửa hồ sơ, kiểm tra SĐT, tài khoản ngân hàng, đổi mật khẩu sai | **6/6** |
| D. Đăng tin | Đăng tin bán có ảnh, trao đổi, đấu giá ngược; 4 trường hợp dữ liệu sai (kể cả file giả ảnh); admin duyệt; điểm danh nhận xu; đẩy tin; xóa tin | **19/19** |
| E. Mua bán | Yêu thích, chat (chặn người ngoài), trả giá và chốt giá, mua COD, luồng trạng thái đơn, đánh giá người bán, chuyển khoản + QR VietQR + biên lai, hủy đơn | **58/58** |
| F. AI đa phương thức | Tìm bằng mô tả, ràng buộc ngân sách, tìm bằng ảnh, "gần tôi", lưu và đối chiếu nhu cầu | **17/17** |
| G. Báo cáo – thông báo | Báo cáo tin (5 trường hợp chặn), admin xử lý, thông báo, xu thưởng | **12/12** |
| H. KTX – sinh viên | Hợp đồng, hóa đơn, thanh toán, thông báo, yêu cầu sửa chữa + AI phân loại, trợ lý nội quy, mặt bằng | **20/20** |
| I. KTX – ban quản lý | Duyệt/từ chối hồ sơ, xếp giường, xác nhận/từ chối thanh toán, lập/hủy hóa đơn, xử lý sửa chữa, duyệt/chấm dứt thuê ki-ốt, thông báo, trả phòng | **33/33** |
| J. Quản trị | Dashboard, duyệt/từ chối tin, khóa/mở khóa tài khoản, danh mục, đánh giá, nhật ký kiểm toán, báo cáo doanh thu, so sánh mô hình AI | **36/36** |
| K. Bảo mật | Phân quyền, CSRF, kênh realtime, SQL injection, chống dò mật khẩu, không lộ email/OTP | **13/13** |
| **Tổng API** | | **260/260** |
| UI-1 → UI-7 | Đăng nhập và đăng tin bằng form, tìm AI, báo cáo, đánh giá, XSS, dò lỗi 45 trang, điện thoại 390px | **25/25** |
| PHPUnit / Vitest | | **105/105** · **57/57** |

### Một số số đo đáng chú ý

**Tìm kiếm AI:**

- Truy vấn "cần nồi cơm điện nhỏ cho phòng ký túc xá" chạy ở chế độ `semantic` (Gemini + BM25), mất 0,8 giây.
- 3 kết quả đầu: *Nồi cơm điện mini 0.8L*, *Nồi cơm điện 1L còn tốt*, *Nồi lẩu điện mini đa năng*.
- Với ràng buộc cứng ngân sách 300.000đ, không có kết quả nào vượt ngân sách.

**Tìm bằng ảnh:**

- Ảnh chồng sách trả về 5 kết quả đều là giáo trình, mất khoảng 2,3 giây do phải gọi API nhúng ảnh.
- Khách chưa đăng nhập bị yêu cầu đăng nhập, nhằm hạn chế lạm dụng hạn mức API.

**Trả giá:** người mua đề nghị 120.000đ cho món niêm yết 150.000đ, người bán chấp nhận. Giao dịch được tạo đúng 120.000đ (sau khi sửa lỗi #3).

**Chống dò mật khẩu:** sau 5 lần đăng nhập sai liên tiếp, máy chủ trả `429 Too Many Requests`.

**Giao diện:**

- 35/35 ảnh trên trang Sản phẩm tải thành công.
- 11 trang chính không tràn ngang ở màn hình 390px.
- 45 trang của 4 vai trò không có lỗi JavaScript hay lỗi máy chủ 5xx.

## 5. Lỗi phát hiện và cách khắc phục

| # | Mức độ | Lỗi | Cách phát hiện | Khắc phục |
|---|---|---|---|---|
| 1 | Cao (thiếu chức năng) | Không có cách **đánh giá người bán** trên giao diện mới; điểm uy tín luôn 0 | Rà soát luồng mua hàng | API `POST /api/user/ratings` (chỉ người mua, chỉ khi đơn hoàn tất, 1 lần/giao dịch, 1–5 sao) + form chọn sao trên trang Giao dịch; người bán nhận thông báo |
| 2 | Cao (thiếu chức năng) | Không có cách **báo cáo tin vi phạm**; trang admin "Báo cáo hệ thống" luôn rỗng | Rà soát luồng admin | API `POST /api/user/reports` (lý do cố định, không tự báo cáo, không gửi trùng khi đang chờ) + nút "Báo cáo tin đăng" trên trang sản phẩm |
| 3 | Cao (sai nghiệp vụ) | Người bán **chấp nhận đề nghị giá** nhưng khi đặt mua vẫn tính **giá niêm yết** | Kiểm thử E: giao dịch ra 150.000đ thay vì 120.000đ | Khi đặt mua, dùng giá đã được chấp nhận gần nhất của chính người mua cho món đó (nếu thấp hơn giá niêm yết) |
| 4 | Cao (bảo mật) | API chốt đề nghị giá chỉ lọc theo mã tin nhắn: người bán có thể chấp nhận/từ chối đề nghị **thuộc hội thoại khác**, gửi mã không tồn tại vẫn báo thành công | Kiểm thử E (mã tin nhắn sai vẫn trả 200) | Chỉ cập nhật đề nghị thuộc đúng hội thoại và còn ở trạng thái chờ; ngược lại trả 409. Thêm 3 kiểm thử chặn |
| 5 | Trung bình | Trợ lý nội quy trả lời câu "Mấy giờ KTX đóng cửa buổi tối?" bằng nội quy **tiền điện** | Kiểm thử H | Ánh xạ cách hỏi thường ngày ("đóng cửa", "về muộn", "bạn bè ngủ lại"…) sang từ ngữ trong nội quy; thêm unit test |
| 6 | Trung bình | Câu hỏi ngoài phạm vi (giá vé xe buýt) vẫn nhận một nội quy không liên quan | Kiểm thử H | Bỏ từ đệm câu hỏi ("bao nhiêu", "đến"…) trước khi xếp hạng; thêm unit test |
| 7 | Thấp (giao diện) | Thông báo "Hãy chọn số sao" không mất khi đã chọn sao | Ảnh chụp màn hình UI-4 | Xóa thông báo lỗi khi chọn sao |
| 8 | Hạ tầng | MariaDB của CSDL demo bị sập (mã 139) khi đọc thông tin cột, do bảng phân quyền trong thư mục dữ liệu cũ bị hỏng | Khi sao lưu bằng `mysqldump` | Tạo lại thư mục dữ liệu mới, nạp lại bản sao lưu sạch, chạy lại seed KTX; sao lưu chuẩn mới ở `_sao_luu_w6-w9/db/` |

Sau khi sửa, toàn bộ bộ kiểm thử được chạy lại trên CSDL khôi phục từ bản chuẩn: 260/260 API, 25/25 giao diện, 105/105 PHPUnit.

### Các điểm kiểm tra cho thấy hệ thống hoạt động đúng thiết kế

Những trường hợp dưới đây ban đầu có vẻ lạ nhưng là hành vi đúng:

- **Đăng nhập tài khoản chưa xác minh hoặc đang bị khóa trả HTTP 200** kèm `status = verify_otp | locked`. Giao diện dựa vào trạng thái này để chuyển trang; không có phiên đăng nhập nào được tạo.
- **Tin đăng chứa mã HTML/JavaScript bị kiểm duyệt AI tự từ chối.** Khi chèn thẳng mã vào CSDL để thử, giao diện vẫn hiển thị nguyên văn và không thực thi. Tin nhắn chat cũng vậy.
- **Tìm bằng ảnh cần kèm một mô tả tối thiểu.** Giao diện tự điền "Tìm món đồ giống ảnh mẫu".

## 6. Bổ sung dữ liệu demo (W10)

- **Ảnh sản phẩm:** thêm ảnh cho 34 tin (73/108 tin có ảnh).
  - Nguồn: ảnh giấy phép tự do (CC0, Public domain, CC BY, CC BY-SA) trên Wikimedia Commons.
  - Mỗi ảnh được duyệt bằng mắt: đã loại 34 ảnh không đúng món (tranh vẽ, ảnh người, vật thể khác).
  - Tác giả, giấy phép và trang nguồn ghi trong `database/demo/test-marketplace/ATTRIBUTION.json`.
  - Đã tạo vector ảnh Gemini cho các tin mới có ảnh.
- **Dữ liệu ký túc xá** (`scripts/seed-demo-ktx.php`, chỉ chạy trên DB demo/test):
  - tài khoản quản lý và kỹ thuật;
  - 4 danh mục phí;
  - 12 hóa đơn ở đủ các trạng thái;
  - 6 phiếu sửa chữa ở đủ các trạng thái;
  - 3 ki-ốt;
  - giường trống;
  - hồ sơ chờ duyệt;
  - 3 thông báo.

## 7. Hạn chế còn lại

- **Thanh toán ZaloPay/MoMo:** chỉ kiểm tra phần chặn và luồng trạng thái. Chưa gọi cổng thật vì không có tài khoản sandbox.
- **Đăng nhập Google:** không kiểm thử tự động, vì cần tài khoản Google thật. Đã kiểm thử thủ công ở tuần 7.
- **Tìm bằng ảnh:** độ trễ 2–3 giây do gọi API nhúng ảnh. Kết quả phụ thuộc vào hạn mức API Gemini; khi hết hạn mức, hệ thống quay về tìm theo từ khóa.
- **Trợ lý nội quy:** chỉ truy xuất nguyên văn nội quy (không sinh câu trả lời), nên câu hỏi diễn đạt quá xa vẫn có thể không tìm thấy. Khi đó trợ lý hướng người dùng liên hệ Ban quản lý.

## 8. Cách chạy lại

```bash
# CSDL demo đã nạp bản chuẩn; API (8081) và giao diện (3000) đang chạy
python -I scripts/qa/full_e2e.py http://localhost:3000 <file_mat_khau_test> <seed_output_demo> \
       database/demo/test-marketplace/accounts.json ket_qua_api.json
node scripts/qa/ui_e2e.cjs sinhvien-market-next/frontend http://localhost:3000 <file_mat_khau_test> \
       database/demo/test-marketplace/accounts.json database/demo/test-marketplace/images/t003.jpg thu_muc_ket_qua
vendor/bin/phpunit
cd sinhvien-market-next/frontend && npx vitest run && npx eslint . && npx tsc --noEmit
```

- Mật khẩu tài khoản test chỉ đọc từ file, không ghi trong mã nguồn.
- Bộ kiểm thử thay đổi dữ liệu (duyệt hồ sơ, xác nhận thanh toán…), nên cần khôi phục CSDL demo từ bản chuẩn trước mỗi lần chạy.
