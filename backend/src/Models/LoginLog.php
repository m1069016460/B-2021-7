<?php

namespace App\Models;

class LoginLog extends BaseModel
{
    protected string $table = 'login_logs';

    public function logLogin(string $username, string $ip, string $deviceInfo, bool $success, ?string $failureReason = null): int
    {
        return $this->create([
            'username' => $username,
            'login_ip' => $ip,
            'device_info' => $deviceInfo,
            'status' => $success ? 1 : 0,
            'failure_reason' => $failureReason
        ]);
    }

    public function getRecentLogs(int $limit = 50): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY login_time DESC LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function search(int $page, int $pageSize, ?string $username = null, ?bool $success = null, ?string $startTime = null, ?string $endTime = null): array
    {
        $where = [];
        $values = [];

        if ($username !== null) {
            $where[] = 'username LIKE ?';
            $values[] = '%' . $username . '%';
        }

        if ($success !== null) {
            $where[] = 'status = ?';
            $values[] = $success ? 1 : 0;
        }

        if ($startTime !== null) {
            $where[] = 'login_time >= ?';
            $values[] = $startTime;
        }

        if ($endTime !== null) {
            $where[] = 'login_time <= ?';
            $values[] = $endTime;
        }

        $whereStr = !empty($where) ? implode(' AND ', $where) : '';
        $offset = ($page - 1) * $pageSize;

        if ($whereStr) {
            $sql = "SELECT * FROM {$this->table} WHERE {$whereStr} ORDER BY login_time DESC LIMIT {$pageSize} OFFSET {$offset}";
            $countSql = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$whereStr}";
        } else {
            $sql = "SELECT * FROM {$this->table} ORDER BY login_time DESC LIMIT {$pageSize} OFFSET {$offset}";
            $countSql = "SELECT COUNT(*) as count FROM {$this->table}";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
        $items = $stmt->fetchAll();

        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($values);
        $total = (int) $countStmt->fetch()['count'];

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'pageSize' => $pageSize
        ];
    }
}
