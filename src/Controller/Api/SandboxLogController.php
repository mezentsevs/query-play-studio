<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\SandboxLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/sandbox/logs')]
class SandboxLogController extends AbstractController
{
    public function __construct(
        private SandboxLogRepository $sandboxLogRepository,
    ) {
    }

    #[Route('', name: 'api_sandbox_logs_list', methods: ['GET'])]
    public function listLogs(
        #[CurrentUser] User $user,
        Request $request,
    ): JsonResponse {
        $limit = (int) $request->query->get('limit', 50);
        $offset = (int) $request->query->get('offset', 0);
        $databaseType = $request->query->get('database_type');

        $logs = $databaseType
            ? $this->sandboxLogRepository->findByUserAndDatabaseType($user, $databaseType, $limit)
            : $this->sandboxLogRepository->findByUser($user, $limit);

        $formattedLogs = array_map(fn ($log) => $this->formatLog($log), $logs);

        return new JsonResponse([
            'status' => 'success',
            'data' => $formattedLogs,
            'pagination' => [
                'limit' => $limit,
                'offset' => $offset,
                'total' => count($formattedLogs),
            ],
        ]);
    }

    #[Route('/{id}', name: 'api_sandbox_logs_show', methods: ['GET'])]
    public function showLog(
        #[CurrentUser] User $user,
        int $id,
    ): JsonResponse {
        $log = $this->sandboxLogRepository->find($id);

        if (!$log) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Log not found',
            ], Response::HTTP_NOT_FOUND);
        }

        if ($log->getUser()->getId() !== $user->getId()) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Access denied',
            ], Response::HTTP_FORBIDDEN);
        }

        return new JsonResponse([
            'status' => 'success',
            'data' => $this->formatLog($log),
        ]);
    }

    #[Route('/clear', name: 'api_sandbox_logs_clear', methods: ['POST'])]
    public function clearLogs(
        #[CurrentUser] User $user,
        Request $request,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $confirm = $data['confirm'] ?? false;
        $databaseType = $data['database_type'] ?? null;

        if (!$confirm) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Clear operation requires confirm=true parameter',
            ], Response::HTTP_BAD_REQUEST);
        }

        $logs = $databaseType
            ? $this->sandboxLogRepository->findByUserAndDatabaseType($user, $databaseType, 1000)
            : $this->sandboxLogRepository->findByUser($user, 1000);

        $deletedCount = 0;

        foreach ($logs as $log) {
            $this->sandboxLogRepository->remove($log, false);
            ++$deletedCount;
        }

        $this->sandboxLogRepository->getEntityManager()->flush();

        return new JsonResponse([
            'status' => 'success',
            'message' => 'Logs cleared successfully',
            'data' => [
                'deleted_count' => $deletedCount,
                'database_type' => $databaseType,
            ],
        ]);
    }

    #[Route('/statistics', name: 'api_sandbox_logs_statistics', methods: ['GET'])]
    public function getStatistics(
        #[CurrentUser] User $user,
    ): JsonResponse {
        $logs = $this->sandboxLogRepository->findByUser($user, 1000);

        $statistics = [
            'total_queries' => count($logs),
            'successful_queries' => 0,
            'failed_queries' => 0,
            'total_execution_time' => 0,
            'by_database_type' => [],
            'by_operation_type' => [],
            'recent_activity' => [],
        ];

        $lastWeek = new \DateTimeImmutable('-7 days');

        foreach ($logs as $log) {
            // Count success/failure
            if ($log->isSuccessful()) {
                ++$statistics['successful_queries'];
            } else {
                ++$statistics['failed_queries'];
            }

            // Total execution time
            $statistics['total_execution_time'] += $log->getExecutionTime();

            // By database type
            $dbType = $log->getDatabaseType();
            if (!isset($statistics['by_database_type'][$dbType])) {
                $statistics['by_database_type'][$dbType] = [
                    'count' => 0,
                    'successful' => 0,
                    'failed' => 0,
                    'total_time' => 0,
                ];
            }

            ++$statistics['by_database_type'][$dbType]['count'];

            if ($log->isSuccessful()) {
                ++$statistics['by_database_type'][$dbType]['successful'];
            } else {
                ++$statistics['by_database_type'][$dbType]['failed'];
            }

            $statistics['by_database_type'][$dbType]['total_time'] += $log->getExecutionTime();

            // By operation type
            $opType = $log->getOperationType() ?? 'unknown';

            if (!isset($statistics['by_operation_type'][$opType])) {
                $statistics['by_operation_type'][$opType] = 0;
            }

            ++$statistics['by_operation_type'][$opType];

            // Recent activity (last 7 days)
            if ($log->getExecutedAt() > $lastWeek) {
                $date = $log->getExecutedAt()->format('Y-m-d');

                if (!isset($statistics['recent_activity'][$date])) {
                    $statistics['recent_activity'][$date] = 0;
                }

                ++$statistics['recent_activity'][$date];
            }
        }

        // Calculate averages
        if (count($logs) > 0) {
            $statistics['average_execution_time'] = $statistics['total_execution_time'] / count($logs);
            $statistics['success_rate'] = ($statistics['successful_queries'] / count($logs)) * 100;
        } else {
            $statistics['average_execution_time'] = 0;
            $statistics['success_rate'] = 0;
        }

        return new JsonResponse([
            'status' => 'success',
            'data' => $statistics,
        ]);
    }

    private function formatLog($log): array
    {
        return [
            'id' => $log->getId(),
            'database_type' => $log->getDatabaseType(),
            'query' => $log->getQuery(),
            'result' => $log->getResult(),
            'error' => $log->getError(),
            'execution_time_ms' => $log->getExecutionTime(),
            'executed_at' => $log->getExecutedAt()->format('Y-m-d H:i:s'),
            'is_successful' => $log->isSuccessful(),
            'operation_type' => $log->getOperationType(),
        ];
    }
}
