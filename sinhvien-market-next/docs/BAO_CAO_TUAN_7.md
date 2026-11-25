# BÁO CÁO TIẾN ĐỘ ĐỒ ÁN CHUYÊN NGÀNH — TUẦN LỄ 7 (23/11/2026 – 29/11/2026)

- **Trường / Khoa**: Trường Đại học Công nghệ TP.HCM (HUTECH) — Khoa Công nghệ Thông tin
- **Biểu mẫu theo dõi**: `BM03/QT05/ĐT-KT`
- **Tên đề tài** (theo phiếu đăng ký cập nhật ngày 19/11/2026): **SinhVien-Market: Nền tảng số hóa ký túc xá tích hợp sàn trao đổi sinh viên, ứng dụng AI đa phương thức ghép nối nhu cầu – sản phẩm dựa trên ngữ cảnh lưu trú.**
- **Giảng viên hướng dẫn**: **GV. Dương Thành Phết**
- **Nhóm sinh viên thực hiện**:
  1. **Lê Lương Đại Hợp** — MSSV: `2380600761` — Lớp: `23DTHC1`
  2. **Nguyễn Bảo Trân** — MSSV: `2380602295` — Lớp: `23DTHC1`
- **Ngành**: Công nghệ thông tin — **Chuyên ngành**: Công nghệ phần mềm
- **Thời gian thực hiện**: **Tuần lễ 7 (`23/11/2026 – 29/11/2026`)**
- **Nhánh mã nguồn**: `merge/tran-ui-into-integration`

---

## 1. Nội dung công việc theo Phiếu theo dõi tiến độ (Tuần 7)

| STT | Nội dung công việc | Tài liệu / Mã nguồn kết quả | Trạng thái |
|---|---|---|---|
| 1 | **Thực nghiệm và đánh giá mô hình AI ghép nối trên hệ thống thật** | `scripts/eval/run-benchmark.php`, `docs/BAO_CAO_BENCHMARK_W8.md`, `scripts/eval/results/benchmark_w8.json` | ✅ Hoàn thành |
| 2 | **Rà soát bảo mật và tối ưu hiệu năng** | `GoogleAuthController.php`, `PusherService.php`, `database/schema.sql`, `SmartSearchService.php`, `docs/BAO_CAO_HARDENING_W9.md` | ✅ Hoàn thành |
| 3 | **Kiểm thử toàn hệ thống và sửa lỗi** | `scripts/qa/` (smoke test API, duyệt giao diện), trang `/requests`, các trang chat / KTX / admin | ✅ Hoàn thành |
| 4 | **Nhắn tin thời gian thực và tài liệu triển khai** | `RealtimeApiController.php`, `lib/realtime.ts`, `docs/HUONG_DAN_CHAY.md`, `.github/workflows/phpunit.yml` | ✅ Hoàn thành |

---

## 2. Chi tiết kết quả thực hiện trong Tuần 7

### 2.1. Thực nghiệm và đánh giá mô hình AI ghép nối
- **Đánh giá trực tiếp pipeline đang chạy** (Gemini Embedding 2 + BM25 tiếng Việt + `MatchingEngine` lọc ràng buộc), thay cho prototype Python. Bộ dữ liệu: 30 REQUEST, 41 OFFER, danh mục thật, đã bỏ xe cộ.
- **Phương pháp khắc phục điểm yếu của lần thử nghiệm trước:**
  - chia cố định tập dev/test (15/15, phân tầng theo kịch bản);
  - chỉ tinh chỉnh ngưỡng ngữ nghĩa trên dev, báo cáo kết quả trên test;
  - "đáp án đúng" tính theo đúng luật ràng buộc của hệ thống thật;
  - vector được lưu cache nên kết quả tái tạo được mà không cần API key.
- **Ablation 6 biến thể** (baseline cũ, chỉ BM25, Gemini có/không BM25, có/không kênh ảnh), kèm **khoảng tin cậy 95% bằng bootstrap ghép cặp**.
- **Kết quả trên tập test:**
  - hệ thống đang chạy đạt **Overall 0,82, nDCG@5 0,95**;
  - **BM25 góp +0,12** (KTC 95% [+0,01; +0,27]): có ý nghĩa thống kê;
  - so với baseline cũ: +0,34 (KTC [+0,12; +0,58]);
  - kênh ảnh: +0,03, mới là bằng chứng sơ bộ vì chỉ có 6 ảnh.
- **Độ trễ:** nhúng câu truy vấn (gọi API) trung vị 0,84 giây, p95 1,46 giây; chấm điểm và xếp hạng 41 tin khi đã có vector chỉ khoảng 1,6 mili giây.
- **Quyết định:** giữ ngưỡng 0,40 cho hệ thống thật, vì đường cong trên dev phẳng trong mức nhiễu, còn ngưỡng 0,40 cho danh sách kết quả sạch hơn. Báo cáo có phần phân tích lỗi từng REQUEST chưa đạt.
- **Thay đổi so với báo cáo tuần 5:** tìm kiếm AI trên trang Sản phẩm và tìm bằng ảnh giờ dùng Gemini thật qua backend. Cách nhận diện ảnh bằng chữ ký màu RGB 8×8 và luật khái niệm viết tay chỉ còn ở chế độ dữ liệu mẫu (mock).

### 2.2. Rà soát bảo mật và tối ưu hiệu năng
- **Bảo mật** (chi tiết trong `docs/BAO_CAO_HARDENING_W9.md`):
  - **Đăng nhập Google:**
    - từ chối email Google chưa xác minh (chống chiếm tài khoản);
    - chặn tài khoản đã bị khóa;
    - tạo lại session sau khi đăng nhập (chống session fixation);
    - tài khoản quản trị bắt buộc dùng cổng Admin.
  - Gỡ ngoại lệ bỏ qua OTP viết cứng cho email admin.
  - `schema.sql` không còn tạo tài khoản Gmail thật kèm mật khẩu công khai; thay bằng tài khoản mẫu thuộc tên miền dành riêng cho thử nghiệm.
  - Gỡ khóa bí mật Pusher viết cứng trong mã nguồn; kênh chat chuyển từ kênh công khai sang **kênh private** có xác thực.
  - Cấu hình chứng chỉ CA cho PHP (lỗi cURL 60 khi gọi Google/Gemini).
- **Hiệu năng tìm kiếm AI:** sửa lỗi truy vấn N+1. Đo trên 111 tin:
  - truy vấn SQL mỗi lượt tìm: **191 → 3**;
  - dữ liệu đọc từ DB: **5,0 MB → 2,4 MB**;
  - thời gian nạp vector: **141 ms → 75 ms**.
- **CI (GitHub Actions):** PHP 8.3; thêm job frontend (ESLint, Vitest, build, kiểm tra kiểu); chạy trên cả nhánh `fix/**`, `merge/**`.

### 2.3. Kiểm thử toàn hệ thống và sửa lỗi
- **Kiểm thử hồi quy trên CSDL dựng mới hoàn toàn:**
  - smoke test API 3 hành trình demo + bảo mật: **44/44 đạt**;
  - duyệt giao diện 4 vai trò, 56 lượt trang: **0 lỗi JavaScript**.
- **Kiểm thử thăm dò như người dùng thật** (người mua, người bán, admin; 84 trang; kiểm tra cả màn hình điện thoại 390px). Phát hiện và sửa **6 lỗi**:
  1. chat không tự cập nhật tin mới;
  2. người bán vẫn thấy ô "Đề nghị giá" cho món của mình;
  3. Enter không gửi tin;
  4. hiển thị mã tiếng Anh thô (trạng thái, mức ưu tiên, nhóm sửa chữa…);
  5. tin trả lời tự động hiện ký hiệu markdown;
  6. chip mã trạng thái dùng để debug còn sót trên giao diện.
- **Bổ sung trang "Nhu cầu đã lưu" (`/requests`):**
  - xem lại các nhu cầu đã lưu từ ô tìm kiếm;
  - cho AI đối chiếu lại với tin đang bán;
  - đóng nhu cầu.
- Kiểm tra lại sau khi sửa: **13/13** kịch bản trên trình duyệt đạt, dò lại 84 trang không phát sinh lỗi mới.

### 2.4. Nhắn tin thời gian thực và tài liệu triển khai
- **Chat thời gian thực qua Pusher, dùng kênh private theo từng người dùng:**
  - API `GET /api/realtime/config` và `POST /api/realtime/auth`; chỉ ký quyền nghe kênh của chính người đăng nhập, bắt buộc có CSRF;
  - frontend dùng `pusher-js`; trang hội thoại và danh sách tin nhắn cập nhật ngay khi có tin;
  - khi mất kết nối realtime, trang tự quay về hỏi lại mỗi 5 giây.
- **Kết quả đo:** tin nhắn hiện bên người nhận sau **0,6–0,7 giây** (4/4 lần thử).
- **Kiểm thử bảo mật (đều đạt):**
  - khách chưa đăng nhập → 401;
  - nghe kênh của người khác, kênh công khai kiểu cũ, hoặc thiếu CSRF → 403.
- **Tài liệu:**
  - `docs/HUONG_DAN_CHAY.md`: cài đặt CSDL, cấu hình `.env`, chạy API/giao diện, dữ liệu demo, kiểm thử và bảng lỗi thường gặp;
  - cập nhật README.

### 2.5. Tổng hợp kiểm thử cuối tuần
| Bộ kiểm thử | Kết quả |
|---|---|
| PHPUnit (Backend) | **103/103** |
| Vitest (Frontend) | **57/57** |
| ESLint / TypeScript / `next build` | 0 lỗi / 0 lỗi / đạt |
| Smoke test API (CSDL sạch) | 44/44 |

---

## 3. Khó khăn và cách xử lý
- **Prototype AI dùng luật viết tay và dữ liệu mẫu, chưa phản ánh hệ thống thật.** Nhóm chuyển sang đánh giá chính pipeline đang chạy, có chia dev/test và khoảng tin cậy.
- **Mô hình `gemini-2.5-flash` ngừng cấp cho khóa API mới, và các model mới đôi lúc quá tải.** Nhóm thêm cơ chế thử lần lượt nhiều model, có thể cấu hình qua `GEMINI_CHAT_MODEL`.
- **App Pusher cũ không còn hoạt động.** Nhóm tạo app mới và chuyển toàn bộ sang kênh private.
- **Bộ dữ liệu đánh giá nhỏ** (30 REQUEST, 6 ảnh). Nhóm nêu rõ giới hạn trong báo cáo và dùng khoảng tin cậy thay vì chỉ một con số.

## 4. Kế hoạch Tuần 8 (30/11/2026 – 06/12/2026)
- [ ] Gộp nhánh `merge/tran-ui-into-integration` vào `main` (pull request, review chéo).
- [ ] Bổ sung dữ liệu demo cho phân hệ KTX (hóa đơn, phiếu sửa chữa, mặt bằng) và ảnh sản phẩm.
- [ ] Viết báo cáo đồ án: chương Thực nghiệm (từ `BAO_CAO_BENCHMARK_W8.md`) và chương Kiểm thử – Bảo mật (từ `BAO_CAO_HARDENING_W9.md`).
- [ ] Soạn kịch bản demo và slide bảo vệ.
