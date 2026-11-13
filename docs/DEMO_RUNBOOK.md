# Docker demo runbook

## Điều kiện an toàn

- Docker Desktop/Compose v2 và PHP CLI; dành riêng cho máy demo.
- Đặt `MYSQL_ROOT_PASSWORD` ngẫu nhiên, chỉ dùng cho demo. Không dùng `.env` production hoặc dữ liệu thật.
- Compose này dùng duy nhất database/volume `sinhvien_market_demo` / `demo_mysql_data`; cổng MySQL không publish ra host. Không dùng `docker compose down -v` nếu cần giữ demo.
- `database/schema.sql` xóa/tạo lại toàn bộ bảng. Chỉ import vào volume mới của demo. Không dùng trên database hiện hữu.

## Tạo stack sạch

```powershell
$bytes = New-Object byte[] 32
[Security.Cryptography.RandomNumberGenerator]::Create().GetBytes($bytes)
$env:MYSQL_ROOT_PASSWORD = [Convert]::ToBase64String($bytes)
docker compose -f docker-compose.demo.yml up --build -d
```

Chờ MySQL healthy, sau đó trên **volume demo mới** import schema và chạy migration:

```powershell
Get-Content .\database\schema.sql -Raw | docker compose -f docker-compose.demo.yml exec -T db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" sinhvien_market_demo'
docker compose -f docker-compose.demo.yml exec php php scripts/migrate.php
docker compose -f docker-compose.demo.yml exec php php scripts/seed-demo.php
```

Seeder in mật khẩu ngẫu nhiên dùng một lần ra terminal. Ghi nhận cẩn thận trong môi trường demo nội bộ; script không in lại mật khẩu khi account đã tồn tại và từ chối chạy lần nữa để tránh reset/nhân đôi dữ liệu. Trang demo: `http://localhost:3000`; backend: `http://localhost:8080`.

## Backup/restore demo

Backup đặt trong `storage/backups` và chỉ cho phép database demo:

```powershell
.\scripts\backup-demo.ps1 -Database sinhvien_market_demo -ConfirmDatabase sinhvien_market_demo
```

Restore có thể ghi đè bảng demo; kiểm tra tệp trước và xác nhận chính xác:

```powershell
.\scripts\restore-demo.ps1 -BackupPath .\storage\backups\<backup>.sql `
  -Database sinhvien_market_demo -ConfirmDatabase "RESTORE sinhvien_market_demo"
```

Hai script không chấp nhận tên DB khác `sinhvien_market_demo`. Thử nghiệm/restore chỉ dành cho stack demo cô lập; cần lưu backup ở nơi kiểm soát truy cập. Không commit backup hoặc mật khẩu.

## Dọn stack

```powershell
docker compose -f docker-compose.demo.yml down
```

Lệnh trên giữ volume cho lần demo tiếp theo. Chỉ xóa volume demo sau khi đã xác nhận không cần dữ liệu; không thực hiện trong quy trình bàn giao mặc định.
