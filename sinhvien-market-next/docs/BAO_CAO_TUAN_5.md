# BÁO CÁO TIẾN ĐỘ TUẦN 5 — ĐỒ ÁN CHUYÊN NGÀNH

- **Dự án**: Nâng cấp hệ thống **SinhVienMarket** — Nền tảng Giao dịch Sinh viên & Quản lý Ký túc xá (Next.js 16 + React 19 + TypeScript + Tailwind CSS 4).
- **Nội dung thực hiện Tuần 5**: Đồng bộ nhánh `main` của Leader vào nhánh `feature/nextjs-frontend`, rà soát trực quan 21 màn hình người dùng, hoàn thiện thanh điều hướng chung (`MainShell`) theo phương án gộp menu **Ghép nối (Plan A)** và menu **Ký túc xá**, bổ sung đầy đủ dữ liệu mẫu Mock Mode xóa sạch lỗi `HTTP 500`, và thiết kế lại trọn bộ **7 trang KTX & Ghép nối Sinh viên** cùng **Chợ Sản phẩm (`/products`)** và **Hộp thư Chat (`/chat`)**.
- **Nhánh Git làm việc**: `feature/nextjs-frontend` (trên kho lưu trữ nhóm `katosama0902-pixel/sinhvien-market` và kho dự phòng `shingetsu194/sinhvien-market`).

---

## 1. Tổng hợp các hạng mục đã hoàn thành trong Tuần 5

| STT | Nhóm hạng mục | Màn hình / File cập nhật | Nội dung chi tiết đã thực hiện | Trạng thái |
|---|---|---|---|---|
| 1 | **Đồng bộ Git & Phân tách phạm vi** | Nhánh `feature/nextjs-frontend` | Merge toàn bộ luồng User & KTX từ nhánh `main` của Leader vào `feature/nextjs-frontend`, bảo toàn nguyên vẹn phân hệ AI Tuần 4 (`/requests`) và không tác động vào nhánh `main`. | ✅ Hoàn thành |
| 2 | **Thanh điều hướng `MainShell` & `FlashBanner`** | `components/layout/MainShell.tsx`, `components/auth/FlashBanner.tsx`, `app/globals.css` | • Triển khai **Plan A**: Gộp thành 1 nút **"Ghép nối"** có menu thả xuống chọn giữa **Ghép nối AI (Cần mua)** (`/requests`) và **Tìm sản phẩm phù hợp** (`/marketplace/matching`).<br>• Thêm menu thả xuống **"Ký túc xá"** dẫn tới 6 phân hệ KTX.<br>• Khắc phục triệt để lỗi rớt chữ 2 dòng và lỗi tràn thanh cuộn ngang ở màn hình `1024px–1440px`.<br>• Tự động ẩn `FlashBanner` sau `4.5s` hoặc khi chuyển trang. | ✅ Hoàn thành |
| 3 | **Khung điều hướng con KTX (`KtxPageHeader`)** | `components/residence/KtxPageHeader.tsx` | Xây dựng Hero Header đồng bộ cho toàn bộ phân hệ KTX kèm thanh 6 tab chuyển nhanh và bộ chọn ngày hiển thị chuẩn tiếng Việt `dd/mm/yyyy` (`DateFieldVi`). | ✅ Hoàn thành |
| 4 | **Kho dữ liệu giả lập (`USE_MOCK`) — Xóa lỗi HTTP 500** | `lib/api/residence-finance.ts`, `lib/api/residence-operations.ts`, `lib/api/marketplace-matching.ts`, `lib/api/marketplace.ts`, `lib/api/user-flows.ts` | • Bổ sung nhánh `USE_MOCK` lưu `localStorage` cho Hóa đơn & Phí KTX (2 hóa đơn mẫu, lịch sử thanh toán, 5 thông báo/sự kiện/nội quy).<br>• Bổ sung Mock cho Sơ đồ Tòa/Tầng/Phòng/Giường, Lịch sử lưu trú, Yêu cầu sửa chữa + tự động phân loại ưu tiên, Trợ lý Nội quy và 3 mặt bằng cho thuê.<br>• Bổ sung ảnh minh họa thật cho toàn bộ 9 sản phẩm mẫu (`101`–`109`) và 2 cuộc hội thoại mẫu cho tài khoản `student@demo.vn`. | ✅ Hoàn thành |
| 5 | **Thiết kế lại 7 trang User & KTX của Leader** | `app/marketplace/matching/page.tsx`, `app/residence/page.tsx`, `app/residence/announcements/page.tsx`, `app/residence/finance/page.tsx`, `app/residence/services/page.tsx`, `app/residence/assistant/page.tsx`, `app/rentals/page.tsx` | • **`/marketplace/matching`**: Tự động nạp danh sách gợi ý khi mở trang, thêm chip tìm nhanh 1 chạm, thẻ sản phẩm có ảnh & % độ khớp.<br>• **`/residence`**: Lưới 5 thẻ lối tắt KTX, Việt hóa trạng thái đơn/hợp đồng (`Chờ xét duyệt`, `Đang hiệu lực`...), hiển thị ngày `dd/mm/yyyy`.<br>• **`/residence/announcements`**: Bộ lọc loại tin (*Tất cả / Thông báo / Sự kiện / Nội quy*) & tìm kiếm nhanh.<br>• **`/residence/finance`**: 3 thẻ KPI tài chính, bảng chi tiết khoản thu và nút *"Điền số tiền còn nợ"* 1 chạm.<br>• **`/residence/services`**: 3 mẫu báo hỏng nhanh, tự động gắn nhãn kỹ thuật & mức ưu tiên tiếng Việt.<br>• **`/residence/assistant`**: Giao diện hỏi đáp kèm 5 câu hỏi thường gặp bấm 1 chạm.<br>• **`/rentals`**: Thẻ mặt bằng hiển thị diện tích $\text{m}^2$, giá thuê, vị trí và bấm chọn trực tiếp để điền đơn. | ✅ Hoàn thành |
| 6 | **Nâng cấp Chợ Sản phẩm & Trang Chi tiết** | `app/products/page.tsx`, `app/products/[id]/page.tsx` | Thay thế khung chữ `"IMAGE"` trống bằng ảnh sản phẩm thật, thêm bộ lọc khoảng giá nhanh (*Dưới 100k*, *100k – 300k*, *Trên 300k*) và đồng bộ căn lề `max-w-6xl` trên trang chi tiết sản phẩm. | ✅ Hoàn thành |

---

## 2. Kết quả Kiểm thử Tự động (Tuần 5)

- **TypeScript (`npx tsc --noEmit`)**: `0` lỗi kiểu dữ liệu.
- **ESLint (`npx eslint .`)**: `0` lỗi, `0` cảnh báo.
- **Unit Test (`npx vitest run`)**: **3 file test / 54 bài test đạt 100%** (`validation.test.ts`: 30 test, `admin-validation.test.ts`: 18 test, `matching.test.ts`: 6 test).
- **Kiểm tra trực quan trên trình duyệt (`http://localhost:3200`)**: Đã kiểm chứng toàn bộ các màn hình KTX, Ghép nối, Sản phẩm và Tin nhắn hoạt động mượt mà, không còn bất kỳ thông báo lỗi `Request failed with status 500` nào.

---

## 3. Kế hoạch Lộ trình Giao diện các Tuần tiếp theo (Tuần 6 → Tuần 8)

| Tuần | Trọng tâm công việc | Các màn hình phụ trách |
|---|---|---|
| **Tuần 6** | **Cổng Cá nhân, Đăng bán & Luồng Giao dịch của Sinh viên (User Portal & Commerce Flows)** | `/dashboard`, `/my-listings`, `/products/create`, `/transactions`, `/transactions/[id]/payment`, `/wishlist`, `/notifications`, `/profile`, `/users/[id]` |
| **Tuần 7** | **Gamification (Đổi quà, Bảng xếp hạng) & Cụm Xác thực Tài khoản (Auth & Rewards UI)** | `/rewards`, `/leaderboard`, `/login-role`, `/login`, `/register`, `/forgot-password`, `/reset-password`, `/verify-otp`, `/account-locked` |
| **Tuần 8** | **Đồng bộ 5 trang Quản trị KTX & Matching mới vào `AdminShell` + Kiểm thử E2E & Báo cáo Tổng kết** | `/admin/residence`, `/admin/residence/dashboard`, `/admin/residence/finance`, `/admin/residence/operations`, `/admin/marketplace/matching` + Full E2E Playwright |
