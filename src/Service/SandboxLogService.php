<?php

namespace App\Service;

use App\Entity\SandboxLog;
use App\Entity\User;
use App\Enum\DatabaseType;
use App\Repository\SandboxLogRepository;
use Doctrine\ORM\EntityManagerInterface;

class SandboxLogService
{
    public function __construct(
        private SandboxLogRepository $sandboxLogRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function logQuery(
        User $user,
        DatabaseType $databaseType,
        string $query,
        bool $success,
        ?string $error = null,
        ?array $result = null,
        ?int $executionTime = null,
        ?string $operationType = null,
    ): SandboxLog {
        $log = new SandboxLog();
        $log->setUser($user);
        $log->setDatabaseType($databaseType->value);
        $log->setQuery($query);
        $log->setIsSuccessful($success);
        $log->setExecutionTime($executionTime ?? 0);

        if ($error) {
            $log->setError($error);
        }

        if (null !== $result) {
            // Limit result size for storage
            $formattedResult = json_encode($this->limitResultSize($result));

            if (strlen($formattedResult) > 10000) {
                $formattedResult = json_encode(['truncated' => true, 'row_count' => count($result)]);
            }

            $log->setResult($formattedResult);
        }

        if ($operationType) {
            $log->setOperationType($operationType);
        } else {
            $log->setOperationType($this->detectOperationType($query));
        }

        $this->entityManager->persist($log);
        $this->entityManager->flush();

        return $log;
    }

    private function limitResultSize(array $result, int $maxRows = 50, int $maxCellLength = 100): array
    {
        if (count($result) <= $maxRows) {
            $limited = $result;
        } else {
            $limited = array_slice($result, 0, $maxRows);
            $limited[] = ['_truncated' => true, 'remaining_rows' => count($result) - $maxRows];
        }

        foreach ($limited as &$row) {
            foreach ($row as &$cell) {
                if (is_string($cell) && strlen($cell) > $maxCellLength) {
                    $cell = substr($cell, 0, $maxCellLength).'...';
                }
            }
        }

        return $limited;
    }

    private function detectOperationType(string $query): string
    {
        $normalized = strtoupper(trim($query));

        if (str_starts_with($normalized, 'SELECT')) {
            return 'SELECT';
        }

        if (str_starts_with($normalized, 'INSERT')) {
            return 'INSERT';
        }

        if (str_starts_with($normalized, 'UPDATE')) {
            return 'UPDATE';
        }

        if (str_starts_with($normalized, 'DELETE')) {
            return 'DELETE';
        }

        if (str_starts_with($normalized, 'CREATE')) {
            return 'CREATE';
        }

        if (str_starts_with($normalized, 'ALTER')) {
            return 'ALTER';
        }

        if (str_starts_with($normalized, 'DROP')) {
            return 'DROP';
        }

        if (str_starts_with($normalized, 'TRUNCATE')) {
            return 'TRUNCATE';
        }

        if (str_starts_with($normalized, 'SHOW')) {
            return 'SHOW';
        }

        if (str_starts_with($normalized, 'DESCRIBE') || str_starts_with($normalized, 'DESC')) {
            return 'DESCRIBE';
        }

        if (str_starts_with($normalized, 'EXPLAIN')) {
            return 'EXPLAIN';
        }

        return 'OTHER';
    }

    public function cleanupOldLogs(int $daysToKeep = 30): int
    {
        $cutoffDate = new \DateTimeImmutable(sprintf('-%d days', $daysToKeep));

        $query = $this->entityManager->createQuery(
            'DELETE FROM App\Entity\SandboxLog l WHERE l.executedAt < :cutoff',
        )->setParameter('cutoff', $cutoffDate);

        return $query->execute();
    }
}
