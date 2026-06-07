<?php

namespace App\Models;

class DataImportLog extends BaseModel
{
    protected string $table = 'data_import_logs';

    public function logImport(
        string $operatorUsername,
        string $dataType,
        string $importMethod,
        ?string $fileName,
        int $totalCount,
        int $successCount,
        int $failedCount,
        ?array $failureDetails = null
    ): int {
        return $this->create([
            'operator_username' => $operatorUsername,
            'data_type' => $dataType,
            'import_method' => $importMethod,
            'file_name' => $fileName,
            'total_count' => $totalCount,
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'failure_details' => $failureDetails !== null ? json_encode($failureDetails, JSON_UNESCAPED_UNICODE) : null
        ]);
    }

    public function search(
        int $page,
        int $pageSize,
        ?string $operator = null,
        ?string $dataType = null,
        ?string $importMethod = null,
        ?string $startTime = null,
        ?string $endTime = null
    ): array {
        $where = [];
        $values = [];

        if ($operator !== null) {
            $where[] = 'operator_username LIKE ?';
            $values[] = '%' . $operator . '%';
        }

        if ($dataType !== null) {
            $where[] = 'data_type = ?';
            $values[] = $dataType;
        }

        if ($importMethod !== null) {
            $where[] = 'import_method = ?';
            $values[] = $importMethod;
        }

        if ($startTime !== null) {
            $where[] = 'operation_time >= ?';
            $values[] = $startTime;
        }

        if ($endTime !== null) {
            $where[] = 'operation_time <= ?';
            $values[] = $endTime;
        }

        $whereStr = !empty($where) ? implode(' AND ', $where) : '';
        $offset = ($page - 1) * $pageSize;

        if ($whereStr) {
            $sql = "SELECT * FROM {$this->table} WHERE {$whereStr} ORDER BY operation_time DESC LIMIT {$pageSize} OFFSET {$offset}";
            $countSql = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$whereStr}";
        } else {
            $sql = "SELECT * FROM {$this->table} ORDER BY operation_time DESC LIMIT {$pageSize} OFFSET {$offset}";
            $countSql = "SELECT COUNT(*) as count FROM {$this->table}";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
        $items = $stmt->fetchAll();

        foreach ($items as &$item) {
            if (!empty($item['failure_details'])) {
                $item['failure_details'] = json_decode($item['failure_details'], true);
            }
        }

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
