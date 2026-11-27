# KỊCH BẢN DEMO BẢO VỆ ĐỒ ÁN (12–15 phút)

Đề tài: **SinhVien-Market: nền tảng số hóa ký túc xá tích hợp sàn trao đổi sinh viên, ứng dụng AI đa phương thức ghép nối nhu cầu – sản phẩm dựa trên ngữ cảnh lưu trú.**

Thông điệp chính cần để lại cho hội đồng:

1. Sàn mua bán gắn với **ngữ cảnh lưu trú**: tìm "gần tôi" trong cùng tòa, tài khoản cư dân được xác thực.
2. **AI đa phương thức**: tìm bằng mô tả tự nhiên và bằng ảnh, có ràng buộc cứng (ngân sách, danh mục) và gợi ý nới điều kiện.
3. **Số hóa vận hành KTX**: hồ sơ lưu trú → hợp đồng → xếp giường → hóa đơn → thanh toán → sửa chữa.

---

## 0. Chuẩn bị (trước giờ bảo vệ 30 phút)

| Việc | Lệnh / thao tác |
|---|---|
| Khôi phục CSDL demo về bản chuẩn | Nạp file `_sao_luu_w6-w9/db/demo-baseline-w10-*.sql` vào `sinhvien_market_demo` |
| Chạy API | `php -S 127.0.0.1:8081 index.php` (`.env` trỏ `DB_NAME=sinhvien_market_demo`, có `GEMINI_API_KEY`, `PUSHER_*`) |
| Chạy giao diện | `npm run build && npm run start` trong `sinhvien-market-next/frontend` |
| Kiểm tra nhanh | Mở `http://localhost:3000/products`: thấy ảnh sản phẩm; gõ "nồi cơm" thấy kết quả AI |
| Mở sẵn 3 cửa sổ trình duyệt | (1) thường: **sinh viên mua**; (2) ẩn danh: **sinh viên bán**; (3) profile khác: **quản lý KTX / admin** |
| Bật sẵn | Điện thoại hoặc DevTools 390px để minh họa responsive (tùy chọn) |

**Tài khoản demo** (mật khẩu chung nằm trong file `TEST_ACCOUNTS_PASSWORD.txt`, không đọc to):

| Vai trò | Email |
|---|---|
| Sinh viên mua (cư dân tòa B) | `thanhtung.vo@uit.example.edu` |
| Sinh viên bán (cùng tòa B, để minh họa "gần tôi") | `ngocanh.pham@ueh.example.edu` |
| Quản lý KTX | `quanly.ktx@example.com` |
| Admin | `qa.admin@example.test` (cổng `/admin-login`) |

> Nếu mạng yếu hoặc Gemini quá tải, hệ thống tự chuyển sang tìm theo từ khóa (có dải màu vàng thông báo). Khi đó vẫn demo được, chỉ cần giải thích cơ chế dự phòng.

---

## 1. Mở đầu – vấn đề (1 phút)

- Sinh viên ở KTX thường thanh lý và mua lại đồ dùng qua nhóm Facebook. Tin trôi nhanh, khó tìm, không có cơ chế tin cậy.
- Ban quản lý KTX vẫn làm hồ sơ lưu trú, hóa đơn và sửa chữa bằng giấy hoặc Excel.
- Giải pháp: một nền tảng gộp **sàn trao đổi** và **quản lý KTX**, có **AI** ghép nhu cầu với sản phẩm dựa trên ngữ cảnh lưu trú.

## 2. Sàn trao đổi – luồng cơ bản (3 phút) · cửa sổ (2) người bán

1. Đăng nhập, bấm **Đăng bán**. Điền tiêu đề, mô tả, danh mục, giá, chọn ảnh, rồi **Đăng tin**.
   - Nói: *tin được kiểm duyệt AI. Tin chứa nội dung cấm hoặc mã độc sẽ bị từ chối tự động.*
2. Mở **Tin của tôi**: thấy tin mới ở trạng thái chờ duyệt hoặc đang bán.

Cửa sổ (1), người mua:

3. Mở một tin, bấm **Yêu thích**, rồi **Nhắn người bán**. Gửi "Bạn bớt được không?" rồi **Đề nghị giá** thấp hơn.
4. Chuyển sang cửa sổ (2): tin nhắn hiện **ngay lập tức** (Pusher, khoảng 0,7 giây). Bấm **Chấp nhận** đề nghị.
5. Quay lại cửa sổ (1): đặt mua COD. **Giá giao dịch đúng bằng giá đã thỏa thuận.**
6. Cửa sổ (2): **Xác nhận & chuẩn bị giao**, rồi **Đánh dấu đã giao**. Cửa sổ (1): **Xác nhận đã nhận**, chọn sao, **Gửi đánh giá**.
7. Mở hồ sơ người bán: điểm uy tín cập nhật.

## 3. AI đa phương thức (3–4 phút) · cửa sổ (1)

1. Trang **Sản phẩm**, gõ câu tự nhiên: *"cần nồi cơm điện nhỏ cho phòng ký túc xá"*.
   - Kết quả đầu là nồi cơm điện, dù tiêu đề không trùng từng chữ.
   - Giải thích: **Gemini Embedding 2 kết hợp BM25 tiếng Việt**, sau đó `MatchingEngine` lọc ràng buộc.
2. Đặt **ngân sách tối đa** và bỏ tick "Linh hoạt điều kiện": không còn món vượt giá.
   - Bật lại tick: hệ thống gợi ý *"nới ngân sách thêm X đ sẽ có thêm N món"* (counterfactual).
3. Bấm biểu tượng **máy ảnh**, chọn ảnh một chồng sách: kết quả là các giáo trình (**tìm bằng ảnh**).
4. Bật **Gần tôi**: chỉ hiện người bán **cùng tòa KTX**. Đây là ngữ cảnh lưu trú, server tự xác định tòa từ giường được xếp.
5. **Lưu nhu cầu**, mở trang **Nhu cầu đã lưu**, bấm "Tìm lại": AI đối chiếu nhu cầu với tin mới.
6. (Nếu được hỏi về đánh giá) Mở `/admin/marketplace/matching` bằng admin:
   - so sánh các mô hình trên cùng một truy vấn;
   - nêu số liệu benchmark: Overall 0,82; nDCG@5 0,95; BM25 đóng góp +0,12 (KTC 95% [+0,01; +0,27]).

## 4. Ký túc xá số (3–4 phút)

Cửa sổ (1), sinh viên cư dân:

1. **Ký túc xá → Tài chính**: hóa đơn tháng 11 (tiền phòng, điện vượt định mức, gửi xe). Ghi nhận chuyển khoản một phần.
2. **Sửa chữa**: gửi "Vòi nước bồn rửa bị rò rỉ". AI phân loại là **Nước – ưu tiên cao**.
3. **Trợ lý nội quy**: hỏi *"Mấy giờ KTX đóng cửa?"*. Trợ lý trả lời đúng nội quy giờ giới nghiêm và chỉ trích nội quy đã công bố, không bịa.

Cửa sổ (3), quản lý KTX:

4. **Dashboard vận hành**: tỷ lệ lấp đầy, công nợ, yêu cầu tồn.
5. **Hồ sơ lưu trú**: duyệt hồ sơ chờ, rồi **xếp giường trống**.
6. **Tài chính**: xác nhận khoản chuyển khoản vừa ghi nhận; số dư hóa đơn giảm.
7. **Vận hành**: chuyển phiếu sửa chữa sang *Đang xử lý*, giao cho nhân viên kỹ thuật.
8. (Nếu còn thời gian) **Mặt bằng**: duyệt đơn thuê ki-ốt KT-S02.

## 5. Quản trị & an toàn (1–2 phút) · admin

1. **Báo cáo hệ thống**: thấy báo cáo tin vi phạm do sinh viên gửi. Bấm **Đã xử lý**.
2. **Người dùng**: khóa một tài khoản 3 ngày. Tài khoản đó đăng nhập thì bị chặn. Mở khóa lại.
3. **Nhật ký kiểm toán**: mọi thao tác quản trị đều được ghi lại.
4. Nêu nhanh các biện pháp bảo mật:
   - CSRF cho mọi thao tác ghi;
   - phân quyền RBAC 4 vai trò;
   - kênh chat private;
   - chống dò mật khẩu (HTTP 429);
   - kiểm thử 260 trường hợp API và 25 kịch bản giao diện đều đạt.

## 6. Kết (30 giây)

- Đã làm được: sàn trao đổi đầy đủ luồng mua bán; AI đa phương thức có đánh giá định lượng; phân hệ KTX 4 vai trò.
- Hướng phát triển:
  - cổng thanh toán thật;
  - ứng dụng di động;
  - mở rộng bộ dữ liệu đánh giá AI;
  - trợ lý nội quy có sinh câu trả lời (RAG).

---

## Phương án dự phòng

| Sự cố | Cách xử lý |
|---|---|
| Gemini quá tải hoặc hết hạn mức | Tìm kiếm tự về chế độ từ khóa (dải vàng). Giải thích cơ chế dự phòng; chiếu ảnh `docs/` kết quả benchmark |
| Pusher mất kết nối | Chat tự hỏi lại mỗi 5 giây, vẫn demo được (chậm hơn) |
| Lỗi dữ liệu trong lúc demo | Nạp lại bản chuẩn `demo-baseline-w10-*.sql` (khoảng 30 giây) |
| Máy chiếu độ phân giải thấp | Giao diện responsive. Có thể thu nhỏ trình duyệt hoặc dùng chế độ 390px |

## Câu hỏi hội đồng có thể đặt và ý trả lời

| Câu hỏi | Ý trả lời |
|---|---|
| AI "đa phương thức" ở đâu? | Văn bản và ảnh nhúng vào **cùng không gian vector** Gemini Embedding 2. Truy vấn bằng chữ tìm được ảnh, truy vấn bằng ảnh tìm được tin |
| Vì sao cần BM25 khi đã có embedding? | Embedding hiểu ngữ nghĩa nhưng yếu với tên riêng, mã model (Casio fx-580). BM25 bù phần này: +0,12 Overall, có ý nghĩa thống kê |
| "Ngữ cảnh lưu trú" cụ thể là gì? | Tòa nhà lấy từ giường được xếp (không cho người dùng tự khai), dùng cho bộ lọc "gần tôi" và tích xanh cư dân |
| Đánh giá có khách quan không? | Tách tập dev/test, chỉ chỉnh ngưỡng trên dev, báo cáo trên test, có khoảng tin cậy bootstrap. Hạn chế: bộ dữ liệu nhỏ (30 REQUEST) |
| Dữ liệu cá nhân được bảo vệ thế nào? | Dashboard không lộ email; hồ sơ công khai không có email; mật khẩu bcrypt; kênh chat private theo người dùng |
