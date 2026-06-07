<?php

namespace App\Controllers;

use App\Services\LogService;
use App\Utils\Response;

class LogController
{
    private LogService $logService;

    public function __construct()
    {
        $this->logService = new LogService();
    }

    private function checkAdminPermission(array $params): bool
    {
        $user = $params['_user'] ?? null;
        if (!$user || $user['role'] !== 'admin') {
            Response::error('无权限访问', 403);
            return false;
        }
        return true;
    }

    public function loginLogs(array $params): void
    {
        if (!$this->checkAdminPermission($params)) return;

        $page = (int) ($_GET['page'] ?? 1);
        $pageSize = (int) ($_GET['pageSize'] ?? 20);
        $username = $_GET['username'] ?? null;
        $success = isset($_GET['success']) ? ($_GET['success'] === '1') : null;
        $startTime = $_GET['startTime'] ?? null;
        $endTime = $_GET['endTime'] ?? null;

        $result = $this->logService->getLoginLogs($page, $pageSize, $username, $success, $startTime, $endTime);
        $result['items'] = $this->logService->maskSensitiveData($result['items'], 'login');

        Response::success([
            'items' => $result['items'],
            'total' => $result['total'],
            'page' => $result['page'],
            'pageSize' => $result['pageSize'],
            'totalPages' => ceil($result['total'] / $result['pageSize'])
        ]);
    }

    public function gradeChangeLogs(array $params): void
    {
        if (!$this->checkAdminPermission($params)) return;

        $page = (int) ($_GET['page'] ?? 1);
        $pageSize = (int) ($_GET['pageSize'] ?? 20);
        $operator = $_GET['operator'] ?? null;
        $studentId = isset($_GET['studentId']) ? (int) $_GET['studentId'] : null;
        $courseId = isset($_GET['courseId']) ? (int) $_GET['courseId'] : null;
        $operationType = $_GET['operationType'] ?? null;
        $startTime = $_GET['startTime'] ?? null;
        $endTime = $_GET['endTime'] ?? null;

        $result = $this->logService->getGradeChangeLogs($page, $pageSize, $operator, $studentId, $courseId, $operationType, $startTime, $endTime);
        $result['items'] = $this->logService->maskSensitiveData($result['items'], 'grade');

        Response::success([
            'items' => $result['items'],
            'total' => $result['total'],
            'page' => $result['page'],
            'pageSize' => $result['pageSize'],
            'totalPages' => ceil($result['total'] / $result['pageSize'])
        ]);
    }

    public function dataImportLogs(array $params): void
    {
        if (!$this->checkAdminPermission($params)) return;

        $page = (int) ($_GET['page'] ?? 1);
        $pageSize = (int) ($_GET['pageSize'] ?? 20);
        $operator = $_GET['operator'] ?? null;
        $dataType = $_GET['dataType'] ?? null;
        $importMethod = $_GET['importMethod'] ?? null;
        $startTime = $_GET['startTime'] ?? null;
        $endTime = $_GET['endTime'] ?? null;

        $result = $this->logService->getDataImportLogs($page, $pageSize, $operator, $dataType, $importMethod, $startTime, $endTime);
        $result['items'] = $this->logService->maskSensitiveData($result['items'], 'import');

        Response::success([
            'items' => $result['items'],
            'total' => $result['total'],
            'page' => $result['page'],
            'pageSize' => $result['pageSize'],
            'totalPages' => ceil($result['total'] / $result['pageSize'])
        ]);
    }

    public function exportLoginLogs(array $params): void
    {
        if (!$this->checkAdminPermission($params)) return;

        $username = $_GET['username'] ?? null;
        $success = isset($_GET['success']) ? ($_GET['success'] === '1') : null;
        $startTime = $_GET['startTime'] ?? null;
        $endTime = $_GET['endTime'] ?? null;

        $this->logService->exportLoginLogs($username, $success, $startTime, $endTime);
    }

    public function exportGradeChangeLogs(array $params): void
    {
        if (!$this->checkAdminPermission($params)) return;

        $operator = $_GET['operator'] ?? null;
        $studentId = isset($_GET['studentId']) ? (int) $_GET['studentId'] : null;
        $courseId = isset($_GET['courseId']) ? (int) $_GET['courseId'] : null;
        $operationType = $_GET['operationType'] ?? null;
        $startTime = $_GET['startTime'] ?? null;
        $endTime = $_GET['endTime'] ?? null;

        $this->logService->exportGradeChangeLogs($operator, $studentId, $courseId, $operationType, $startTime, $endTime);
    }

    public function exportDataImportLogs(array $params): void
    {
        if (!$this->checkAdminPermission($params)) return;

        $operator = $_GET['operator'] ?? null;
        $dataType = $_GET['dataType'] ?? null;
        $importMethod = $_GET['importMethod'] ?? null;
        $startTime = $_GET['startTime'] ?? null;
        $endTime = $_GET['endTime'] ?? null;

        $this->logService->exportDataImportLogs($operator, $dataType, $importMethod, $startTime, $endTime);
    }
}
