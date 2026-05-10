<?php

namespace Core;

/**
 * Flash Message Helper
 * Lưu 1 thông báo vào session, hiển thị 1 lần rồi tự xóa
 *
 * Cách dùng:
 *   Controller: Flash::set('success', 'Đăng nhập thành công!');
 *   View/Layout: <?= Flash::render() ?>
 *
 * Các type tương ứng Bootstrap: success | danger | warning | info
 */
class Flash
{
    /**
     * Lưu flash message vào session
     *
     * @param string $type    Bootstrap alert type: 'success', 'danger', 'warning', 'info'
     * @param string $message Nội dung thông báo
     */
    public static function set(string $type, string $message): void
    {
        $_SESSION['flash'] = [
            'type'    => $type,
            'message' => $message,
        ];
    }

    /**
     * Render flash message (nếu có) và xóa khỏi session ngay sau đó
     * Trả về HTML string để echo trong layout
     */
    public static function render(): string
    {
        if (empty($_SESSION['flash'])) {
            return '';
        }

        $type    = htmlspecialchars($_SESSION['flash']['type'], ENT_QUOTES, 'UTF-8');
        $message = htmlspecialchars($_SESSION['flash']['message'], ENT_QUOTES, 'UTF-8');

        unset($_SESSION['flash']);

        return <<<HTML
        <div class="alert alert-{$type} alert-dismissible fade show mb-0 rounded-0 border-0" role="alert">
            <div class="container">
                <i class="bi bi-{$type}-circle-fill me-2"></i>{$message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
        HTML;
    }

    /**
     * Kiểm tra có flash message không (dùng để check trước khi render)
     */
    public static function has(): bool
    {
        return !empty($_SESSION['flash']);
    }
}
