<?php

namespace App\Service;

use App\DTO\SandboxConnectionParams;
use App\DTO\SandboxQueryResult;

class SandboxQueryExecutor
{
    private const DANGEROUS_KEYWORDS = [
        'DROP DATABASE',
        'DROP SCHEMA',
        'SHUTDOWN',
        'KILL',
        'CREATE USER',
        'GRANT ALL',
        'REVOKE ALL',
        'FLUSH PRIVILEGES',
        'ALTER USER',
    ];

    public function __construct(
        private SandboxConnectionFactory $connectionFactory,
    ) {
    }

    public function executeQuery(
        SandboxConnectionParams $params,
        string $query,
        array $parameters = [],
    ): SandboxQueryResult {
        $startTime = microtime(true);

        try {
            // Security validation
            $this->validateQuerySafety($query);

            // Apply table prefixes for user isolation
            $processedQuery = $this->applyTablePrefixes($query, $params);

            $connection = $this->connectionFactory->createConnection($params);

            // Execute the query
            $statement = $connection->prepare($processedQuery);
            $statement->execute($parameters);

            // Determine query type and collect results
            $queryType = $this->getQueryType($processedQuery);

            $result = match ($queryType) {
                'SELECT', 'SHOW', 'DESCRIBE', 'EXPLAIN' => $this->handleSelectQuery($statement, $startTime),
                'INSERT' => $this->handleInsertQuery($statement, $connection, $startTime),
                'UPDATE', 'DELETE' => $this->handleUpdateDeleteQuery($statement, $startTime),
                'CREATE', 'ALTER', 'DROP' => $this->handleDdlQuery($statement, $startTime),
                default => $this->handleOtherQuery($statement, $startTime),
            };

            return $result;
        } catch (\PDOException $e) {
            $executionTime = (int) ((microtime(true) - $startTime) * 1000);

            return SandboxQueryResult::error($e->getMessage(), $executionTime);
        } catch (\RuntimeException $e) {
            $executionTime = (int) ((microtime(true) - $startTime) * 1000);

            return SandboxQueryResult::error($e->getMessage(), $executionTime);
        }
    }

    private function validateQuerySafety(string $query): void
    {
        $normalizedQuery = strtoupper($query);

        foreach (self::DANGEROUS_KEYWORDS as $keyword) {
            if (str_contains($normalizedQuery, strtoupper($keyword))) {
                throw new \RuntimeException(sprintf('Query contains potentially dangerous operation: %s', $keyword));
            }
        }

        // Additional safety checks
        if (str_contains($normalizedQuery, 'DELETE FROM') && !str_contains($normalizedQuery, 'WHERE')) {
            throw new \RuntimeException('DELETE queries without WHERE clause are not allowed for safety reasons.');
        }

        if (str_contains($normalizedQuery, 'UPDATE') && !str_contains($normalizedQuery, 'WHERE')) {
            throw new \RuntimeException('UPDATE queries without WHERE clause are not allowed for safety reasons.');
        }
    }

    private function applyTablePrefixes(string $query, SandboxConnectionParams $params): string
    {
        $userId = $params->userId;
        $prefix = 'user_'.$userId.'_';

        // Simple pattern matching for table names (very basic implementation)
        // In production, this should use a proper SQL parser
        $patterns = [
            '/\bFROM\s+([a-zA-Z_][a-zA-Z0-9_]*)\b/i' => 'FROM '.$prefix.'$1',
            '/\bJOIN\s+([a-zA-Z_][a-zA-Z0-9_]*)\b/i' => 'JOIN '.$prefix.'$1',
            '/\bINTO\s+([a-zA-Z_][a-zA-Z0-9_]*)\b/i' => 'INTO '.$prefix.'$1',
            '/\bTABLE\s+([a-zA-Z_][a-zA-Z0-9_]*)\b/i' => 'TABLE '.$prefix.'$1',
            '/\bUPDATE\s+([a-zA-Z_][a-zA-Z0-9_]*)\b/i' => 'UPDATE '.$prefix.'$1',
            '/\bDROP TABLE\s+([a-zA-Z_][a-zA-Z0-9_]*)\b/i' => 'DROP TABLE '.$prefix.'$1',
            '/\bTRUNCATE TABLE\s+([a-zA-Z_][a-zA-Z0-9_]*)\b/i' => 'TRUNCATE TABLE '.$prefix.'$1',
        ];

        $processedQuery = $query;

        foreach ($patterns as $pattern => $replacement) {
            $processedQuery = preg_replace($pattern, $replacement, $processedQuery);
        }

        return $processedQuery;
    }

    private function getQueryType(string $query): string
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

        if (str_starts_with($normalized, 'SHOW')) {
            return 'SHOW';
        }

        if (str_starts_with($normalized, 'DESCRIBE')) {
            return 'DESCRIBE';
        }

        if (str_starts_with($normalized, 'EXPLAIN')) {
            return 'EXPLAIN';
        }

        return 'OTHER';
    }

    private function handleSelectQuery(\PDOStatement $statement, float $startTime): SandboxQueryResult
    {
        $data = $statement->fetchAll();
        $executionTime = (int) ((microtime(true) - $startTime) * 1000);

        return SandboxQueryResult::success(
            data: $data,
            executionTime: $executionTime,
        );
    }

    private function handleInsertQuery(\PDOStatement $statement, \PDO $connection, float $startTime): SandboxQueryResult
    {
        $affectedRows = $statement->rowCount();
        $lastInsertId = $connection->lastInsertId();
        $executionTime = (int) ((microtime(true) - $startTime) * 1000);

        return SandboxQueryResult::success(
            affectedRows: $affectedRows,
            lastInsertId: $lastInsertId,
            executionTime: $executionTime,
        );
    }

    private function handleUpdateDeleteQuery(\PDOStatement $statement, float $startTime): SandboxQueryResult
    {
        $affectedRows = $statement->rowCount();
        $executionTime = (int) ((microtime(true) - $startTime) * 1000);

        return SandboxQueryResult::success(
            affectedRows: $affectedRows,
            executionTime: $executionTime,
        );
    }

    private function handleDdlQuery(\PDOStatement $statement, float $startTime): SandboxQueryResult
    {
        $executionTime = (int) ((microtime(true) - $startTime) * 1000);

        return SandboxQueryResult::success(
            executionTime: $executionTime,
        );
    }

    private function handleOtherQuery(\PDOStatement $statement, float $startTime): SandboxQueryResult
    {
        $executionTime = (int) ((microtime(true) - $startTime) * 1000);

        return SandboxQueryResult::success(
            executionTime: $executionTime,
        );
    }
}
