<?php
$content = file_get_contents('c:/laragon/www/sinhvien-market/app/views/products/detail.php');

$search = <<<HTML
  // ── Countdown timer ─────────────────────────────────────────────
  function startCountdown(seconds) {
    clearInterval(cdInterval);
    var el = document.getElementById('countdownTimer');
    if (!el) return;

    cdInterval = setInterval(function() {
      seconds--;
      if (seconds <= 0) {
        clearInterval(cdInterval);
        fetchPrice(); // lấy giá mới ngay khi countdown về 0
      }
      var m = Math.floor(seconds / 60).toString().padStart(2, '0');
      var s = (seconds % 60).toString().padStart(2, '0');
      el.textContent = m + ':' + s;
      // Đổi màu đỏ khi còn < 10 giây
      el.style.color = seconds < 10 ? '#ef4444' : '';
    }, 1000);
  }
HTML;

$replace = <<<HTML
  // ── Countdown timer ─────────────────────────────────────────────
  function startCountdown(seconds) {
    clearInterval(cdInterval);
    var el = document.getElementById('countdownTimer');
    if (!el) return;

    function renderTime() {
      if (seconds <= 0) {
        clearInterval(cdInterval);
        fetchPrice(); // lấy giá mới ngay khi countdown về 0
        return;
      }
      var m = Math.floor(seconds / 60).toString().padStart(2, '0');
      var s = (seconds % 60).toString().padStart(2, '0');
      el.textContent = m + ':' + s;
      el.style.color = seconds < 10 ? '#ef4444' : '';
    }

    renderTime();
    if (seconds > 0) {
      cdInterval = setInterval(function() {
        seconds--;
        renderTime();
      }, 1000);
    }
  }
HTML;

$content = str_replace(str_replace("\n", "\r\n", $search), $replace, $content);
$content = str_replace($search, $replace, $content); // In case line endings are LF

$search2 = <<<HTML
        // Reset countdown
        if (data.next_drop_in_seconds && !data.is_at_floor) {
          startCountdown(data.next_drop_in_seconds);
        }
HTML;

$replace2 = <<<HTML
        // Reset countdown
        var el = document.getElementById('countdownTimer');
        if (data.is_at_floor) {
          clearInterval(cdInterval);
          if (el) el.innerHTML = '<span class="badge bg-danger">🔒 Đã chạm giá sàn</span>';
        } else if (data.next_drop_in_seconds) {
          startCountdown(data.next_drop_in_seconds);
        }
HTML;

$content = str_replace(str_replace("\n", "\r\n", $search2), $replace2, $content);
$content = str_replace($search2, $replace2, $content);

file_put_contents('c:/laragon/www/sinhvien-market/app/views/products/detail.php', $content);
echo "Replaced countdown JS";
