<?php

namespace App\Services;

use App\Models\LoginLog;
use App\Models\GradeChangeLog;
use App\Models\DataImportLog;

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
}
