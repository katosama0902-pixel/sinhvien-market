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

        $type    = $_SESSION['flash']['type'] ?? 'info';
        $message = htmlspecialchars($_SESSION['flash']['message'], ENT_QUOTES, 'UTF-8');

        unset($_SESSION['flash']);

        // Style sẵn bằng inline-CSS (app dùng Tailwind, KHÔNG có Bootstrap).
        // Khung có nền sáng + chữ đậm → đọc rõ trên cả nền sáng lẫn tối.
        $themes = [
            'success' => ['#dcfce7', '#166534', '#86efac', 'check-circle-fill'],
            'danger'  => ['#fee2e2', '#991b1b', '#fca5a5', 'x-circle-fill'],
            'error'   => ['#fee2e2', '#991b1b', '#fca5a5', 'x-circle-fill'],
            'warning' => ['#fef3c7', '#92400e', '#fcd34d', 'exclamation-triangle-fill'],
            'info'    => ['#dbeafe', '#1e40af', '#93c5fd', 'info-circle-fill'],
        ];
        [$bg, $fg, $border, $icon] = $themes[$type] ?? $themes['info'];

        return <<<HTML
        <div role="alert" style="display:flex;align-items:center;gap:.6rem;background:{$bg};color:{$fg};border:1px solid {$border};border-radius:12px;padding:.7rem 1rem;font-weight:600;font-size:.9rem;box-shadow:0 4px 14px rgba(0,0,0,.08);margin:0 0 .75rem">
            <i class="bi bi-{$icon}" style="font-size:1.1rem;flex-shrink:0"></i>
            <span style="flex:1;min-width:0">{$message}</span>
            <button type="button" onclick="this.parentElement.remove()" aria-label="Đóng" style="background:transparent;border:0;color:{$fg};opacity:.55;cursor:pointer;font-size:1.3rem;line-height:1;padding:0;flex-shrink:0">&times;</button>
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
