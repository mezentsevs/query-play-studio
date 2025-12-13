<?php

namespace App\Service;

use App\DTO\SandboxConnectionParams;
use App\DTO\SandboxQueryResult;
use App\Entity\User;
use App\Enum\DatabaseType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class SandboxManager
{
    public function __construct(
        private ParameterBagInterface $params,
        private SandboxQueryExecutor $queryExecutor,
    ) {
    }

    public function getConnectionParams(User $user, DatabaseType $databaseType): SandboxConnectionParams
    {
        return match ($databaseType) {
            DatabaseType::MYSQL => $this->getMysqlConnectionParams($user),
            DatabaseType::POSTGRESQL => $this->getPostgresConnectionParams($user),
            DatabaseType::SQLITE => $this->getSqliteConnectionParams($user),
        };
    }

    public function executeQuery(User $user, DatabaseType $databaseType, string $query): SandboxQueryResult
    {
        $connectionParams = $this->getConnectionParams($user, $databaseType);

        return $this->queryExecutor->executeQuery($connectionParams, $query);
    }

    public function getDatabaseStructure(User $user, DatabaseType $databaseType): array
    {
        $connectionParams = $this->getConnectionParams($user, $databaseType);

        return match ($databaseType) {
            DatabaseType::MYSQL => $this->getMysqlStructure($connectionParams),
            DatabaseType::POSTGRESQL => $this->getPostgresStructure($connectionParams),
            DatabaseType::SQLITE => $this->getSqliteStructure($connectionParams),
        };
    }

    private function getMysqlConnectionParams(User $user): SandboxConnectionParams
    {
        return new SandboxConnectionParams(
            databaseType: DatabaseType::MYSQL,
            host: 'qps-mysql-sandbox',
            port: 3307,
            username: 'root',
            password: $this->params->get('mysql_sandbox_root_password'),
            databaseName: $this->params->get('mysql_sandbox_database'),
            userId: $user->getId(),
        );
    }

    private function getPostgresConnectionParams(User $user): SandboxConnectionParams
    {
        return new SandboxConnectionParams(
            databaseType: DatabaseType::POSTGRESQL,
            host: 'qps-postgres-sandbox',
            port: 5432,
            username: 'postgres',
            password: $this->params->get('postgres_sandbox_root_password'),
            databaseName: $this->params->get('postgres_sandbox_database'),
            userId: $user->getId(),
        );
    }

    private function getSqliteConnectionParams(User $user): SandboxConnectionParams
    {
        $fileName = sprintf('user_%d.sqlite', $user->getId());
        $filePath = sprintf('/var/lib/sqlite/%s', $fileName);

        return new SandboxConnectionParams(
            databaseType: DatabaseType::SQLITE,
            host: 'localhost',
            port: 0,
            username: '',
            password: '',
            filePath: $filePath,
            userId: $user->getId(),
        );
    }

    private function getMysqlStructure(SandboxConnectionParams $params): array
    {
        $tablesQuery = "SHOW TABLES LIKE 'user_{$params->userId}_%'";

        $result = $this->queryExecutor->executeQuery($params, $tablesQuery);

        if (!$result->success) {
            return ['error' => $result->error];
        }

        $structure = [];

        foreach ($result->data as $row) {
            $tableName = reset($row);

            $structure[$tableName] = $this->getMysqlTableStructure($params, $tableName);
        }

        return $structure;
    }

    private function getMysqlTableStructure(SandboxConnectionParams $params, string $tableName): array
    {
        $describeQuery = "DESCRIBE `{$tableName}`";

        $result = $this->queryExecutor->executeQuery($params, $describeQuery);

        return $result->success ? $result->data : [];
    }

    private function getPostgresStructure(SandboxConnectionParams $params): array
    {
        $tablesQuery = "
            SELECT table_name 
            FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_name LIKE 'user_{$params->userId}_%'
        ";

        $result = $this->queryExecutor->executeQuery($params, $tablesQuery);

        if (!$result->success) {
            return ['error' => $result->error];
        }

        $structure = [];

        foreach ($result->data as $row) {
            $tableName = $row['table_name'];

            $structure[$tableName] = $this->getPostgresTableStructure($params, $tableName);
        }

        return $structure;
    }

    private function getPostgresTableStructure(SandboxConnectionParams $params, string $tableName): array
    {
        $columnsQuery = "
            SELECT 
                column_name, 
                data_type, 
                is_nullable,
                column_default
            FROM information_schema.columns 
            WHERE table_name = '{$tableName}'
            ORDER BY ordinal_position
        ";

        $result = $this->queryExecutor->executeQuery($params, $columnsQuery);

        return $result->success ? $result->data : [];
    }

    private function getSqliteStructure(SandboxConnectionParams $params): array
    {
        if (!file_exists($params->filePath)) {
            $this->createSqliteDatabase($params);
        }

        $tablesQuery = "SELECT name FROM sqlite_master WHERE type='table' AND name LIKE 'user_{$params->userId}_%'";

        $result = $this->queryExecutor->executeQuery($params, $tablesQuery);

        if (!$result->success) {
            return ['error' => $result->error];
        }

        $structure = [];

        foreach ($result->data as $row) {
            $tableName = $row['name'];

            $structure[$tableName] = $this->getSqliteTableStructure($params, $tableName);
        }

        return $structure;
    }

    private function getSqliteTableStructure(SandboxConnectionParams $params, string $tableName): array
    {
        $pragmaQuery = "PRAGMA table_info('{$tableName}')";

        $result = $this->queryExecutor->executeQuery($params, $pragmaQuery);

        return $result->success ? $result->data : [];
    }

    private function createSqliteDatabase(SandboxConnectionParams $params): void
    {
        touch($params->filePath);
        chmod($params->filePath, 0o666);
    }
}
