<?php

namespace App\Models;

use Core\Model;

/**
 * User Model - Quản lý tài khoản người dùng
 * Sẽ được triển khai đầy đủ trong Phase 3
 */
class User extends Model
{
    /**
     * Tìm user theo email
     * @return array<string, mixed>|null
     */
    public function findByEmail(string $email): ?array
    {
        return $this->queryOne(
            'SELECT * FROM users WHERE email = ? LIMIT 1',
        [$email]
        );
    }

    /**
     * Tìm user theo ID
     */
    public function findById(int $id): ?array
    {
        return $this->queryOne('SELECT * FROM users WHERE id = ? LIMIT 1', [$id]);
    }

    /**
     * Tạo tài khoản mới
     */
    public function create(string $name, string $email, string $password, string $phone = '', string $question = '', string $answer = '', string $otp = '', string $otpExp = ''): int
    {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $ansHash = password_hash(trim(mb_strtolower($answer)), PASSWORD_BCRYPT, ['cost' => 12]); // Hash answer for safety

        return $this->insert(
            'INSERT INTO users (name, email, password, phone, security_question, security_answer, otp_code, otp_expires_at, is_verified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)',
            [$name, $email, $hash, $phone, $question, $ansHash, $otp, $otpExp]
        );
    }

    /**
     * Xác nhận OTP
     */
    public function verifyOtp(int $userId): void
    {
        $this->execute('UPDATE users SET is_verified = 1, last_verified_at = NOW(), otp_code = NULL, otp_expires_at = NULL WHERE id = ?', [$userId]);
    }

    /**
     * Tạm thời hủy xác minh (để yêu cầu OTP mới)
     */
    public function unverify(int $userId): void
    {
        $this->execute('UPDATE users SET is_verified = 0 WHERE id = ?', [$userId]);
    }

    /**
     * Cập nhật OTP mới
     */
    public function updateOtp(int $userId, string $otp, string $otpExp): void
    {
        $this->execute('UPDATE users SET otp_code = ?, otp_expires_at = ? WHERE id = ?', [$otp, $otpExp, $userId]);
    }

    /**
     * Đổi mật khẩu
     */
    public function updatePassword(int $userId, string $newPassword): void
    {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        $this->execute('UPDATE users SET password = ?, otp_code = NULL, otp_expires_at = NULL WHERE id = ?', [$hash, $userId]);
    }

    /**
     * Cập nhật thông tin hồ sơ cá nhân
     */
    public function updateProfile(int $userId, array $data): void
    {
        $this->execute(
            'UPDATE users SET
                name              = ?,
                phone             = ?,
                university        = ?,
                student_id        = ?,
                dormitory_address = ?,
                social_contact    = ?,
                bio               = ?,
                available_time    = ?
             WHERE id = ?',
            [
                $data['name'],
                $data['phone']             ?? null,
                $data['university']        ?? null,
                $data['student_id']        ?? null,
                $data['dormitory_address'] ?? null,
                $data['social_contact']    ?? null,
                $data['bio']               ?? null,
                $data['available_time']    ?? null,
                $userId,
            ]
        );
    }

    /**
     * Cập nhật đường dẫn ảnh đại diện
     */
    public function changeAvatar(int $userId, string $path): void
    {
        $this->execute('UPDATE users SET avatar = ? WHERE id = ?', [$path, $userId]);
    }

    /**
     * Lấy tất cả user (cho Admin)
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        return $this->query('SELECT id, name, email, phone, role, is_locked, lock_reason, locked_at, locked_until, created_at FROM users ORDER BY created_at DESC');
    }

    /**
     * Toggle trạng thái khóa tài khoản
     * @param int         $newLock    1 = khóa, 0 = mở khóa
     * @param string|null $reason     Lý do khóa (chỉ cần khi khóa)
     * @param string|null $lockedUntil DATETIME khóa đến khi nào (NULL = vĩnh viễn)
     */
    public function toggleLock(int $userId, int $newLock, string $reason = '', ?string $lockedUntil = null): void
    {
        if ($newLock === 1) {
            $this->execute(
                'UPDATE users SET is_locked = 1, lock_reason = ?, locked_at = NOW(), locked_until = ? WHERE id = ?',
                [$reason, $lockedUntil, $userId]
            );
        } else {
            // Mở khóa: xóa hết thông tin khóa
            $this->execute(
                'UPDATE users SET is_locked = 0, lock_reason = NULL, locked_at = NULL, locked_until = NULL WHERE id = ?',
                [$userId]
            );
        }
    }

    /**
     * Lấy đầy đủ thông tin user (cho Admin xem chi tiết)
     */
    public function findByIdFull(int $id): ?array
    {
        return $this->queryOne(
            'SELECT id, name, email, phone, role,
                    is_locked, lock_reason, locked_at, locked_until,
                    is_verified, last_verified_at, security_question,
                    created_at
             FROM users WHERE id = ? LIMIT 1',
            [$id]
        );
    }

    /**
     * Đếm tổng số user
     */
    public function countAll(): int
    {
        return $this->count('SELECT COUNT(*) FROM users WHERE role = "student"');
    }

    // ─── Google OAuth Methods ─────────────────────────────────────────────────

    /**
     * Tìm user theo Google ID
     */
    public function findByGoogleId(string $googleId): ?array
    {
        return $this->queryOne(
            'SELECT * FROM users WHERE google_id = ? LIMIT 1',
            [$googleId]
        );
    }

    /**
     * Tạo tài khoản mới từ Google (không có password, is_verified = 1)
     */
    public function createFromGoogle(string $name, string $email, string $googleId, string $avatarUrl): int
    {
        return $this->insert(
            'INSERT INTO users (name, email, password, google_id, avatar_url, role, is_verified, created_at)
             VALUES (?, ?, ?, ?, ?, "student", 1, NOW())',
            [$name, $email, '', $googleId, $avatarUrl]
        );
    }

    /**
     * Gắn Google ID vào tài khoản đã có
     */
    public function linkGoogle(int $userId, string $googleId, string $avatarUrl): void
    {
        $this->execute(
            'UPDATE users SET google_id = ?, avatar_url = ? WHERE id = ?',
            [$googleId, $avatarUrl, $userId]
        );
    }

    // ─── Feature 1: Huy hiệu Sinh Viên Đã Xác Thực ───────────────────────────

    /**
     * Kiểm tra email có phải email sinh viên không (@edu.vn, @ac.vn, @student.)
     */
    public static function isStudentEmail(string $email): bool
    {
        $studentDomains = ['@edu.vn', '@ac.vn', '@student.', '@students.'];
        foreach ($studentDomains as $domain) {
            if (str_contains(strtolower($email), $domain)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Đánh dấu người dùng là sinh viên đã xác thực
     */
    public function verifyStudent(int $userId): void
    {
        $this->execute('UPDATE users SET is_student_verified = 1 WHERE id = ?', [$userId]);
    }

    // ─── Feature 2: Hệ thống Xu & Đẩy Tin ───────────────────────────────────

    /**
     * Lấy số xu hiện tại của user
     */
    public function getCoins(int $userId): int
    {
        $row = $this->queryOne('SELECT coins FROM users WHERE id = ?', [$userId]);
        return (int)($row['coins'] ?? 0);
    }

    /**
     * Cộng xu cho user
     */
    public function addCoins(int $userId, int $amount): void
    {
        $this->execute('UPDATE users SET coins = COALESCE(coins, 0) + ? WHERE id = ?', [$amount, $userId]);
    }

    /**
     * Trừ xu (trả về false nếu không đủ xu)
     */
    public function spendCoins(int $userId, int $amount): bool
    {
        $current = $this->getCoins($userId);
        if ($current < $amount) {
            return false;
        }
        $this->execute('UPDATE users SET coins = coins - ? WHERE id = ?', [$amount, $userId]);
        return true;
    }

    /**
     * Kiểm tra user có thể check-in hôm nay không
     */
    public function canCheckin(int $userId): bool
    {
        $row = $this->queryOne('SELECT last_checkin FROM users WHERE id = ?', [$userId]);
        $last = $row['last_checkin'] ?? null;
        return $last !== date('Y-m-d'); // check-in nếu chưa check-in hôm nay
    }

    /**
     * Thực hiện check-in: Tính toán streak 7 ngày và số xu thưởng
     * Trả về mảng ['bonus' => int, 'streak' => int]
     */
    public function doCheckin(int $userId): array
    {
        $row = $this->queryOne('SELECT checkin_streak, last_checkin FROM users WHERE id = ?', [$userId]);
        $streak = (int)($row['checkin_streak'] ?? 0);
        $lastCheckin = $row['last_checkin'] ?? null;
        
        $bonus = 10;
        
        // Tính toán chuỗi liên tiếp
        if ($lastCheckin) {
            $diff = (new \DateTime())->diff(new \DateTime($lastCheckin))->days;
            if ($diff == 1) {
                $streak++; // Check-in liên tiếp
            } else {
                $streak = 1; // Bị đứt chuỗi
            }
        } else {
            $streak = 1;
        }

        // Nếu đạt mốc 7 ngày, thưởng lớn 50 xu và reset hoặc giữ nguyên.
        // Ở đây thiết kế: Vừa đạt ngày 7 được thưởng 50 xu.
        if ($streak == 7) {
            $bonus = 50;
        } elseif ($streak > 7) {
            $streak = 1; // Qua chu kỳ mới
            $bonus = 10;
        }

        $this->execute(
            'UPDATE users SET coins = COALESCE(coins, 0) + ?, checkin_streak = ?, last_checkin = CURDATE() WHERE id = ?',
            [$bonus, $streak, $userId]
        );

        return ['bonus' => $bonus, 'streak' => $streak];
    }
    /**
     * Lấy thứ hạng/cấp bậc người bán dựa trên số lượng sản phẩm đã đăng
     * Trả về mảng chứa name, color, icon để render UI
     */
    public function getRankLevel(int $userId): array
    {
        $countRow = $this->queryOne('SELECT COUNT(*) as total FROM products WHERE user_id = ?', [$userId]);
        $total = (int)($countRow['total'] ?? 0);

        if ($total >= 5) {
            return ['name' => 'Uy tín', 'color' => 'warning', 'icon' => 'star-fill'];
        } elseif ($total >= 1) {
            return ['name' => 'Tích cực', 'color' => 'info', 'icon' => 'activity'];
        }
        return ['name' => 'Tân binh', 'color' => 'secondary', 'icon' => 'person'];
    }

    /**
     * Lấy bảng xếp hạng người bán (Top Sellers)
     * Scoring: sold_count × 5 + avg_rating × 10 + product_count × 1
     * @return array<int, array<string, mixed>>
     */
    public function getLeaderboard(int $limit = 10): array
    {
        return $this->query(
            "SELECT
                u.id, u.name, u.avatar, u.avatar_url, u.is_student_verified,
                COUNT(DISTINCT p.id)                                                AS product_count,
                COUNT(DISTINCT CASE WHEN p.status = 'sold' THEN p.id END)          AS sold_count,
                COALESCE(ROUND(AVG(r.stars), 1), 0)                                AS avg_rating,
                COUNT(DISTINCT r.id)                                                AS rating_count,
                (COUNT(DISTINCT CASE WHEN p.status = 'sold' THEN p.id END) * 5
                    + COALESCE(AVG(r.stars), 0) * 10
                    + COUNT(DISTINCT p.id) * 1)                                     AS score
             FROM users u
             LEFT JOIN products p ON p.user_id = u.id
             LEFT JOIN ratings  r ON r.ratee_id = u.id
             WHERE u.role = 'student' AND u.is_locked = 0
             GROUP BY u.id
             ORDER BY score DESC, sold_count DESC, avg_rating DESC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Lấy thứ hạng của 1 user cụ thể trong bảng xếp hạng
     */
    public function getMyRank(int $userId): int
    {
        $result = $this->queryOne(
            "SELECT ranked.rank_pos FROM (
                SELECT u.id,
                       RANK() OVER (
                           ORDER BY
                               (COUNT(DISTINCT CASE WHEN p.status = 'sold' THEN p.id END) * 5
                                + COALESCE(AVG(r.stars), 0) * 10
                                + COUNT(DISTINCT p.id) * 1) DESC,
                               COUNT(DISTINCT CASE WHEN p.status = 'sold' THEN p.id END) DESC,
                               COALESCE(AVG(r.stars), 0) DESC
                       ) AS rank_pos
                FROM users u
                LEFT JOIN products p ON p.user_id = u.id
                LEFT JOIN ratings  r ON r.ratee_id = u.id
                WHERE u.role = 'student' AND u.is_locked = 0
                GROUP BY u.id
             ) ranked
             WHERE ranked.id = ?",
            [$userId]
        );
        return (int)($result['rank_pos'] ?? 0);
    }
}

