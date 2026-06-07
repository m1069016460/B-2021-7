<?php

namespace App\Models;

class GradeChangeLog extends BaseModel
{
    protected string $table = 'grade_change_logs';

    public function logChange(
        string $operatorUsername,
        int $studentId,
        int $courseId,
        ?float $oldScore,
        float $newScore,
        ?string $oldGradeLevel,
        ?string $newGradeLevel,
        string $semester,
        string $examType,
        string $operationType = 'update'
    ): int {
        return $this->create([
            'operator_username' => $operatorUsername,
            'student_id' => $studentId,
            'course_id' => $courseId,
            'old_score' => $oldScore,
            'new_score' => $newScore,
            'old_grade_level' => $oldGradeLevel,
            'new_grade_level' => $newGradeLevel,
            'semester' => $semester,
            'exam_type' => $examType,
            'operation_type' => $operationType
        ]);
    }

    public function search(
        int $page,
        int $pageSize,
        ?string $operator = null,
        ?int $studentId = null,
        ?int $courseId = null,
        ?string $operationType = null,
        ?string $startTime = null,
        ?string $endTime = null
    ): array {
        $where = [];
        $values = [];

        if ($operator !== null) {
            $where[] = 'operator_username LIKE ?';
            $values[] = '%' . $operator . '%';
        }

        if ($studentId !== null) {
            $where[] = 'student_id = ?';
            $values[] = $studentId;
        }

        if ($courseId !== null) {
            $where[] = 'course_id = ?';
            $values[] = $courseId;
        }

        if ($operationType !== null) {
            $where[] = 'operation_type = ?';
            $values[] = $operationType;
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
