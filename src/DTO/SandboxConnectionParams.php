<?php

namespace App\DTO;

use App\Enum\DatabaseType;

class SandboxConnectionParams
{
    public function __construct(
        public readonly DatabaseType $databaseType,
        public readonly string $host,
        public readonly int $port,
        public readonly string $username,
        public readonly string $password,
        public readonly ?string $databaseName = null,
        public readonly ?string $filePath = null,
        public readonly int $userId,
    ) {
    }

    public function getConnectionString(): string
    {
        return match ($this->databaseType) {
            DatabaseType::MYSQL => sprintf(
                'mysql:host=%s;port=%d;dbname=%s',
                $this->host,
                $this->port,
                $this->databaseName,
            ),
            DatabaseType::POSTGRESQL => sprintf(
                'pgsql:host=%s;port=%d;dbname=%s',
                $this->host,
                $this->port,
                $this->databaseName,
            ),
            DatabaseType::SQLITE => sprintf(
                'sqlite:%s',
                $this->filePath,
            ),
        };
    }

    public function getPdoOptions(): array
    {
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ];

        if (DatabaseType::MYSQL === $this->databaseType) {
            $options[\PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci';
        }

        return $options;
    }
}
