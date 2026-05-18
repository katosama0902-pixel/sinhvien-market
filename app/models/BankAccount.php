<?php

namespace App\Models;

use Core\Model;

class BankAccount extends Model
{
    public function findByUserId(int $userId): ?array
    {
        return $this->queryOne('SELECT * FROM bank_accounts WHERE user_id = ? LIMIT 1', [$userId]);
    }

    public function save(int $userId, string $bankName, string $bankCode, string $accountNo, string $accountName): void
    {
        // Chuyển tên chủ tài khoản thành in hoa không dấu nếu có thể (hiện tại cứ uppercase)
        $accountName = mb_strtoupper($accountName, 'UTF-8');
        
        $this->execute(
            'INSERT INTO bank_accounts (user_id, bank_name, bank_code, account_no, account_name) 
             VALUES (?, ?, ?, ?, ?) 
             ON DUPLICATE KEY UPDATE 
                bank_name = VALUES(bank_name), 
                bank_code = VALUES(bank_code), 
                account_no = VALUES(account_no), 
                account_name = VALUES(account_name)',
            [$userId, $bankName, $bankCode, $accountNo, $accountName]
        );
    }
}
