<?php
/**
 * Trang 404 - Không tìm thấy trang
 * Sẽ được style đầy đủ ở Phase 8
 */
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>404 - Không tìm thấy trang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height:100vh;background:#f8f9fa">
    <div class="text-center">
        <h1 class="display-1 fw-bold text-primary">404</h1>
        <p class="fs-4">Trang bạn tìm không tồn tại.</p>
        <a href="http://localhost:8080/sinhvien-market" class="btn btn-primary">← Về trang chủ</a>
    </div>
</body>
</html>
