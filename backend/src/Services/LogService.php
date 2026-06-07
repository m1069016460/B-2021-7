<?php

namespace App\Services;

use App\Models\LoginLog;
use App\Models\GradeChangeLog;
use App\Models\DataImportLog;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LogService
{
    private LoginLog $loginLogModel;
    private GradeChangeLog $gradeChangeLogModel;
    private DataImportLog $dataImportLogModel;

    public function __construct()
    {
        $this->loginLogModel = new LoginLog();
        $this->gradeChangeLogModel = new GradeChangeLog();
        $this->dataImportLogModel = new DataImportLog();
    }

    public function logLogin(string $username, bool $success, ?string $failureReason = null): int
    {
        $ip = $this->getClientIp();
        $deviceInfo = $this->getDeviceInfo();

        return $this->loginLogModel->logLogin($username, $ip, $deviceInfo, $success, $failureReason);
    }

    public function logGradeChange(
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
        return $this->gradeChangeLogModel->logChange(
            $operatorUsername,
            $studentId,
            $courseId,
            $oldScore,
            $newScore,
            $oldGradeLevel,
            $newGradeLevel,
            $semester,
            $examType,
            $operationType
        );
    }

    public function logDataImport(
        string $operatorUsername,
        string $dataType,
        string $importMethod,
        ?string $fileName,
        int $totalCount,
        int $successCount,
        int $failedCount,
        ?array $failureDetails = null
    ): int {
        return $this->dataImportLogModel->logImport(
            $operatorUsername,
            $dataType,
            $importMethod,
            $fileName,
            $totalCount,
            $successCount,
            $failedCount,
            $failureDetails
        );
    }

    public function getLoginLogs(int $page, int $pageSize, ?string $username = null, ?bool $success = null, ?string $startTime = null, ?string $endTime = null): array
    {
        return $this->loginLogModel->search($page, $pageSize, $username, $success, $startTime, $endTime);
    }

    public function getGradeChangeLogs(
        int $page,
        int $pageSize,
        ?string $operator = null,
        ?int $studentId = null,
        ?int $courseId = null,
        ?string $operationType = null,
        ?string $startTime = null,
        ?string $endTime = null
    ): array {
        return $this->gradeChangeLogModel->search($page, $pageSize, $operator, $studentId, $courseId, $operationType, $startTime, $endTime);
    }

    public function getDataImportLogs(
        int $page,
        int $pageSize,
        ?string $operator = null,
        ?string $dataType = null,
        ?string $importMethod = null,
        ?string $startTime = null,
        ?string $endTime = null
    ): array {
        return $this->dataImportLogModel->search($page, $pageSize, $operator, $dataType, $importMethod, $startTime, $endTime);
    }

    private function getClientIp(): string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
    }

    private function getDeviceInfo(): string
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

        $device = 'Unknown Device';
        $os = 'Unknown OS';
        $browser = 'Unknown Browser';

        if (preg_match('/(iPhone|iPad|iPod)/', $userAgent)) {
            $device = 'iOS Device';
        } elseif (preg_match('/Android/', $userAgent)) {
            $device = 'Android Device';
        } elseif (preg_match('/Windows/', $userAgent)) {
            $device = 'Windows PC';
            if (preg_match('/Windows NT 10.0/', $userAgent)) {
                $os = 'Windows 10/11';
            } elseif (preg_match('/Windows NT 6.3/', $userAgent)) {
                $os = 'Windows 8.1';
            } elseif (preg_match('/Windows NT 6.2/', $userAgent)) {
                $os = 'Windows 8';
            } elseif (preg_match('/Windows NT 6.1/', $userAgent)) {
                $os = 'Windows 7';
            }
        } elseif (preg_match('/Mac OS X/', $userAgent)) {
            $device = 'Macintosh';
            $os = 'macOS';
        } elseif (preg_match('/Linux/', $userAgent)) {
            $device = 'Linux PC';
            $os = 'Linux';
        }

        if (preg_match('/Chrome/', $userAgent) && !preg_match('/Edg/', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Firefox/', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Safari/', $userAgent) && !preg_match('/Chrome/', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/Edg/', $userAgent)) {
            $browser = 'Edge';
        } elseif (preg_match('/MSIE/', $userAgent) || preg_match('/Trident/', $userAgent)) {
            $browser = 'Internet Explorer';
        }

        return "{$device} | {$os} | {$browser}";
    }

    public function maskSensitiveData(array $items, string $logType): array
    {
        foreach ($items as &$item) {
            if ($logType === 'login') {
                if (!empty($item['login_ip'])) {
                    $item['login_ip'] = $this->maskIp($item['login_ip']);
                }
            }
            
            if (!empty($item['operator_username'])) {
                $item['operator_username'] = $this->maskUsername($item['operator_username']);
            }
            if (!empty($item['username'])) {
                $item['username'] = $this->maskUsername($item['username']);
            }
        }
        return $items;
    }

    private function maskIp(string $ip): string
    {
        if (strpos($ip, ':') !== false) {
            $parts = explode(':', $ip);
            if (count($parts) >= 4) {
                return $parts[0] . ':' . $parts[1] . ':' . $parts[2] . ':***:***:***:***:***';
            }
            return $ip;
        }
        
        $parts = explode('.', $ip);
        if (count($parts) === 4) {
            return $parts[0] . '.' . $parts[1] . '.***.***';
        }
        return $ip;
    }

    private function maskUsername(string $username): string
    {
        $len = mb_strlen($username, 'UTF-8');
        if ($len <= 2) {
            return mb_substr($username, 0, 1, 'UTF-8') . '*';
        }
        if ($len <= 4) {
            return mb_substr($username, 0, 1, 'UTF-8') . str_repeat('*', $len - 2) . mb_substr($username, -1, 1, 'UTF-8');
        }
        return mb_substr($username, 0, 2, 'UTF-8') . str_repeat('*', $len - 3) . mb_substr($username, -1, 1, 'UTF-8');
    }

    public function exportLoginLogs(?string $username = null, ?bool $success = null, ?string $startTime = null, ?string $endTime = null): void
    {
        $result = $this->loginLogModel->search(1, 10000, $username, $success, $startTime, $endTime);
        $items = $this->maskSensitiveData($result['items'], 'login');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['ID', '用户名', '登录时间', '登录IP', '设备信息', '登录状态', '失败原因'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);

        $row = 2;
        foreach ($items as $item) {
            $sheet->setCellValue('A' . $row, $item['id']);
            $sheet->setCellValue('B' . $row, $item['username']);
            $sheet->setCellValue('C' . $row, $item['login_time']);
            $sheet->setCellValue('D' . $row, $item['login_ip']);
            $sheet->setCellValue('E' . $row, $item['device_info']);
            $sheet->setCellValue('F' . $row, $item['status'] == 1 ? '成功' : '失败');
            $sheet->setCellValue('G' . $row, $item['failure_reason'] ?? '-');
            $row++;
        }

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $this->outputExcel($spreadsheet, '登录日志_' . date('YmdHis'));
    }

    public function exportGradeChangeLogs(
        ?string $operator = null,
        ?int $studentId = null,
        ?int $courseId = null,
        ?string $operationType = null,
        ?string $startTime = null,
        ?string $endTime = null
    ): void {
        $result = $this->gradeChangeLogModel->search(1, 10000, $operator, $studentId, $courseId, $operationType, $startTime, $endTime);
        $items = $this->maskSensitiveData($result['items'], 'grade');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['ID', '操作用户', '操作时间', '学生ID', '课程ID', '修改前成绩', '修改后成绩', '修改前等级', '修改后等级', '学期', '考试类型', '操作类型'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:L1')->getFont()->setBold(true);

        $row = 2;
        foreach ($items as $item) {
            $sheet->setCellValue('A' . $row, $item['id']);
            $sheet->setCellValue('B' . $row, $item['operator_username']);
            $sheet->setCellValue('C' . $row, $item['operation_time']);
            $sheet->setCellValue('D' . $row, $item['student_id']);
            $sheet->setCellValue('E' . $row, $item['course_id']);
            $sheet->setCellValue('F' . $row, $item['old_score'] ?? '-');
            $sheet->setCellValue('G' . $row, $item['new_score']);
            $sheet->setCellValue('H' . $row, $item['old_grade_level'] ?? '-');
            $sheet->setCellValue('I' . $row, $item['new_grade_level'] ?? '-');
            $sheet->setCellValue('J' . $row, $item['semester']);
            $sheet->setCellValue('K' . $row, $item['exam_type']);
            $opType = $item['operation_type'] == 'create' ? '创建' : ($item['operation_type'] == 'update' ? '修改' : '删除');
            $sheet->setCellValue('L' . $row, $opType);
            $row++;
        }

        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $this->outputExcel($spreadsheet, '成绩修改日志_' . date('YmdHis'));
    }

    public function exportDataImportLogs(
        ?string $operator = null,
        ?string $dataType = null,
        ?string $importMethod = null,
        ?string $startTime = null,
        ?string $endTime = null
    ): void {
        $result = $this->dataImportLogModel->search(1, 10000, $operator, $dataType, $importMethod, $startTime, $endTime);
        $items = $this->maskSensitiveData($result['items'], 'import');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['ID', '操作用户', '操作时间', '数据类型', '导入方式', '文件名', '总记录数', '成功数', '失败数', '失败详情'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:J1')->getFont()->setBold(true);

        $row = 2;
        foreach ($items as $item) {
            $sheet->setCellValue('A' . $row, $item['id']);
            $sheet->setCellValue('B' . $row, $item['operator_username']);
            $sheet->setCellValue('C' . $row, $item['operation_time']);
            $dataTypeText = $item['data_type'] == 'student' ? '学生数据' : '成绩数据';
            $sheet->setCellValue('D' . $row, $dataTypeText);
            $methodText = $item['import_method'] == 'file' ? '文件导入' : '粘贴导入';
            $sheet->setCellValue('E' . $row, $methodText);
            $sheet->setCellValue('F' . $row, $item['file_name'] ?? '-');
            $sheet->setCellValue('G' . $row, $item['total_count']);
            $sheet->setCellValue('H' . $row, $item['success_count']);
            $sheet->setCellValue('I' . $row, $item['failed_count']);
            $errors = is_array($item['failure_details']) ? implode("\n", $item['failure_details']) : ($item['failure_details'] ?? '-');
            $sheet->setCellValue('J' . $row, $errors);
            $row++;
        }

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $this->outputExcel($spreadsheet, '数据导入日志_' . date('YmdHis'));
    }

    private function outputExcel(Spreadsheet $spreadsheet, string $filename): void
    {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }
}
