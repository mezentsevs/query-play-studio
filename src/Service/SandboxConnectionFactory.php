<?php

namespace App\Service;

use App\DTO\SandboxConnectionParams;
use App\Enum\DatabaseType;

class SandboxConnectionFactory
{
    public function createConnection(SandboxConnectionParams $params): \PDO
    {
        try {
            $connection = new \PDO(
                $params->getConnectionString(),
                $params->username,
                $params->password,
                $params->getPdoOptions(),
            );

            // Set up session-specific settings for isolation
            $this->setupConnection($connection, $params);

            return $connection;
        } catch (\PDOException $e) {
            throw new \RuntimeException(sprintf('Failed to connect to %s sandbox: %s', $params->databaseType->value, $e->getMessage()), 0, $e);
        }
    }

    private function setupConnection(\PDO $connection, SandboxConnectionParams $params): void
    {
        match ($params->databaseType) {
            DatabaseType::MYSQL => $this->setupMysqlConnection($connection, $params),
            DatabaseType::POSTGRESQL => $this->setupPostgresConnection($connection, $params),
            DatabaseType::SQLITE => $this->setupSqliteConnection($connection, $params),
        };
    }

    private function setupMysqlConnection(\PDO $connection, SandboxConnectionParams $params): void
    {
        // Set session variables for the user
        $prefix = 'user_'.$params->userId.'_';

        $connection->exec("SET @user_prefix = '$prefix'");

        // Enable strict mode for better SQL compliance
        $connection->exec("SET sql_mode = 'STRICT_ALL_TABLES,NO_ENGINE_SUBSTITUTION'");
    }

    private function setupPostgresConnection(\PDO $connection, SandboxConnectionParams $params): void
    {
        // PostgreSQL doesn't support session variables like MySQL, but we can set search_path
        // However for table prefix isolation we'll handle it differently in query execution
        $connection->exec("SET TIME ZONE 'UTC'");
    }

    private function setupSqliteConnection(\PDO $connection, SandboxConnectionParams $params): void
    {
        // Enable foreign keys and set timeout
        $connection->exec('PRAGMA foreign_keys = ON');
        $connection->exec('PRAGMA busy_timeout = 3000');
        $connection->exec('PRAGMA journal_mode = WAL');
    }
}
