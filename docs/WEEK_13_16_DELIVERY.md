# SinhVienMarket — deliverables tuần 13–16

## Tuần 13–14: trợ lý cục bộ và vận hành KTX

- `LocalRepairClassifier` phân nhóm yêu cầu sửa chữa bằng từ khóa tiếng Việt, đưa ra mức ưu tiên gợi ý và độ tin cậy; không tự đổi ưu tiên hoặc trạng thái xử lý.
- `LocalDormRulesAssistant` chỉ truy xuất nội quy `rule` đang công bố. Trợ lý không gọi mạng, không lưu câu hỏi, không nạp dữ liệu yêu cầu dịch vụ/hóa đơn, và chỉ trả tiêu đề/nội dung nội quy được phép xem.
- Dashboard trả số liệu tổng hợp vận hành, không trả tên, email, ghi chú hay ID người dùng.
- Quyền mới và bảng phân loại được khai báo trong migration additive `database/migrations/20261114_week13_14_local_intelligence.sql`. Apply tuần tự sau migration nền KTX và tuần 9–12, chỉ sau khi chủ dự án cho phép thay đổi DB.
- API yêu cầu session hiện hành, quyền backend và CSRF ở endpoint ghi dữ liệu. Trợ lý lọc audience theo hợp đồng lưu trú còn hiệu lực; dashboard cần `residence.operations.dashboard.read`.
- Bộ phân loại/trợ lý là baseline tra cứu từ khóa, không phải mô hình ngôn ngữ hoặc tư vấn an toàn. Người quản lý quyết định cuối cùng; trường hợp nguy hiểm cần liên hệ trực tiếp Ban quản lý/cơ quan khẩn cấp.

## Tuần 15: kiểm thử và bảo vệ dữ liệu

- PHPUnit bao gồm kiểm thử xác thực chữ ký mô phỏng MoMo/ZaloPay, đối chiếu mã giao dịch/số tiền/phương thức và tính idempotent khi giao dịch đã paid.
- Các test cho classifier, truy xuất nội quy, CSRF và route là unit/in-process; chúng không gọi cổng thanh toán hoặc DB thật.
- IPN MoMo và ZaloPay fail-closed khi thiếu chữ ký/secret, đối chiếu payment method, reference, amount và trạng thái `pending`. Browser return cũng phải có chữ ký hợp lệ.
- Backup/restore chỉ nhắm `sinhvien_market_demo`, không chấp nhận database mặc định/production. Restore yêu cầu xác nhận chuỗi chính xác. Chưa thực thi backup/restore nào trong workspace này.

## Tuần 16: dữ liệu demo, Docker, bàn giao

- Seed fixture ở `scripts/seed-demo.php` tạo tài khoản giả với mật khẩu sinh ngẫu nhiên một lần, nội quy demo và yêu cầu sửa chữa giả. Script từ chối nếu tên database không khớp chính xác `sinhvien_market_demo`, hoặc môi trường là production; không chạy script này trên dữ liệu hiện có.
- `docker-compose.demo.yml` là demo stack độc lập, volume MySQL riêng, không publish cổng DB và dùng Next.js production build. Chỉ mở rộng ra mạng nội bộ sau khi đặt secret riêng, HTTPS/reverse proxy, firewall và rà soát cấu hình.
- Quy trình khởi tạo và demo chi tiết ở [`DEMO_RUNBOOK.md`](./DEMO_RUNBOOK.md).

## Kiểm tra kỹ thuật

Nhóm PHPUnit không cần database:

```powershell
vendor\bin\phpunit tests\Unit\LocalRepairClassifierTest.php tests\Unit\LocalDormRulesAssistantTest.php tests\Unit\PaymentWebhookGuardTest.php tests\Unit\PaymentSignatureTest.php tests\Unit\PaymentWebhookProcessorTest.php tests\Unit\ResidenceAiAuthorizationPolicyTest.php tests\Unit\LocalMarketplaceEmbeddingServiceTest.php tests\Unit\ApiRequestSecurityTest.php
```

DB-backed integration verification cần một database `*_test` cô lập và migrations đã được cho phép; tuyệt đối không dùng database mặc định/production.

Frontend:

```powershell
Set-Location .\sinhvien-market-next\frontend
npm run lint
npm test
npm run build
```

Lint cú pháp PHP có thể chạy bằng `php -l` trên các file PHP đã thay đổi. Lệnh Docker demo, migration, seed và backup/restore là hướng dẫn; không tự chạy trong quá trình phát triển.

## Kịch bản trình bày (5–7 phút)

1. Đăng nhập bằng tài khoản demo sinh viên; gửi yêu cầu sửa chữa “ổ cắm có tia lửa” và xem phân loại cục bộ/ưu tiên gợi ý.
2. Mở trợ lý nội quy, hỏi về giờ yên tĩnh; chỉ ra nguồn nội quy và thông báo fallback khi không có nội quy phù hợp.
3. Đăng nhập quản lý, xem danh sách yêu cầu và dashboard số liệu tổng hợp; xác nhận không có email/tên cư dân ở dashboard.
4. Trình diễn test chữ ký callback giả lập và trường hợp webhook lặp; không gọi cổng thanh toán thật.
5. Kết thúc bằng giới hạn: dữ liệu demo, classifier từ khóa, kết quả không thay thế xác minh của nhân viên.
