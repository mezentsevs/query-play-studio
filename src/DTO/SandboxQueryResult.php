<?php

namespace App\DTO;

class SandboxQueryResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?array $data = null,
        public readonly ?string $error = null,
        public readonly ?int $affectedRows = null,
        public readonly ?string $lastInsertId = null,
        public readonly array $warnings = [],
        public readonly ?int $executionTime = null,
    ) {
    }

    public static function success(
        ?array $data = null,
        ?int $affectedRows = null,
        ?string $lastInsertId = null,
        array $warnings = [],
        ?int $executionTime = null,
    ): self {
        return new self(
            success: true,
            data: $data,
            affectedRows: $affectedRows,
            lastInsertId: $lastInsertId,
            warnings: $warnings,
            executionTime: $executionTime,
        );
    }

    public static function error(string $error, ?int $executionTime = null): self
    {
        return new self(
            success: false,
            error: $error,
            executionTime: $executionTime,
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'data' => $this->data,
            'error' => $this->error,
            'affected_rows' => $this->affectedRows,
            'last_insert_id' => $this->lastInsertId,
            'warnings' => $this->warnings,
            'execution_time_ms' => $this->executionTime,
        ];
    }
}
