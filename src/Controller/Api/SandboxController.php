<?php

namespace App\Controller\Api;

use App\DTO\SandboxConnectionParams;
use App\Entity\User;
use App\Enum\DatabaseType;
use App\Service\SandboxLogService;
use App\Service\SandboxManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/sandbox')]
class SandboxController extends AbstractController
{
    public function __construct(
        private SandboxManager $sandboxManager,
        private SandboxLogService $sandboxLogService,
    ) {
    }

    #[Route('/{databaseType}/query', name: 'api_sandbox_query', methods: ['POST'])]
    public function executeQuery(
        #[CurrentUser] User $user,
        string $databaseType,
        Request $request,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['query']) || !is_string($data['query'])) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Query is required and must be a string',
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $dbType = DatabaseType::tryFrom($databaseType);

            if (!$dbType) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Invalid database type. Supported types: mysql, postgresql, sqlite',
                ], Response::HTTP_BAD_REQUEST);
            }

            $query = trim($data['query']);

            if (empty($query)) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Query cannot be empty',
                ], Response::HTTP_BAD_REQUEST);
            }

            $result = $this->sandboxManager->executeQuery($user, $dbType, $query);

            // Log the query execution
            $this->sandboxLogService->logQuery(
                $user,
                $dbType,
                $query,
                $result->success,
                $result->error,
                $result->data,
                $result->executionTime,
            );

            return new JsonResponse([
                'status' => $result->success ? 'success' : 'error',
                'data' => $result->toArray(),
            ], $result->success ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        } catch (\RuntimeException $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{databaseType}/structure', name: 'api_sandbox_structure', methods: ['GET'])]
    public function getDatabaseStructure(
        #[CurrentUser] User $user,
        string $databaseType,
    ): JsonResponse {
        try {
            $dbType = DatabaseType::tryFrom($databaseType);

            if (!$dbType) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Invalid database type. Supported types: mysql, postgresql, sqlite',
                ], Response::HTTP_BAD_REQUEST);
            }

            $structure = $this->sandboxManager->getDatabaseStructure($user, $dbType);

            return new JsonResponse([
                'status' => 'success',
                'data' => $structure,
            ]);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        } catch (\RuntimeException $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{databaseType}/reset', name: 'api_sandbox_reset', methods: ['POST'])]
    public function resetSandbox(
        #[CurrentUser] User $user,
        string $databaseType,
        Request $request,
    ): JsonResponse {
        try {
            $dbType = DatabaseType::tryFrom($databaseType);

            if (!$dbType) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Invalid database type. Supported types: mysql, postgresql, sqlite',
                ], Response::HTTP_BAD_REQUEST);
            }

            $data = json_decode($request->getContent(), true);
            $force = $data['force'] ?? false;

            if (!$force) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Reset operation requires force=true parameter',
                ], Response::HTTP_BAD_REQUEST);
            }

            $result = $this->resetUserSandbox($user, $dbType);

            return new JsonResponse([
                'status' => 'success',
                'message' => 'Sandbox reset successfully',
                'data' => $result,
            ]);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        } catch (\RuntimeException $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/supported-types', name: 'api_sandbox_supported_types', methods: ['GET'])]
    public function getSupportedTypes(): JsonResponse
    {
        $types = array_map(fn ($type) => [
            'value' => $type->value,
            'label' => ucfirst($type->value),
            'description' => $this->getDatabaseDescription($type),
        ], DatabaseType::cases());

        return new JsonResponse([
            'status' => 'success',
            'data' => $types,
        ]);
    }

    #[Route('/{databaseType}/health', name: 'api_sandbox_health', methods: ['GET'])]
    public function checkHealth(
        #[CurrentUser] User $user,
        string $databaseType,
    ): JsonResponse {
        try {
            $dbType = DatabaseType::tryFrom($databaseType);

            if (!$dbType) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Invalid database type',
                ], Response::HTTP_BAD_REQUEST);
            }

            // Simple test query to check connectivity
            $testQuery = match ($dbType) {
                DatabaseType::MYSQL => 'SELECT 1',
                DatabaseType::POSTGRESQL => 'SELECT 1',
                DatabaseType::SQLITE => 'SELECT 1',
            };

            $result = $this->sandboxManager->executeQuery($user, $dbType, $testQuery);

            return new JsonResponse([
                'status' => $result->success ? 'success' : 'error',
                'healthy' => $result->success,
                'message' => $result->success ? 'Database connection is healthy' : 'Database connection failed',
                'details' => $result->toArray(),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'healthy' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function resetUserSandbox(User $user, DatabaseType $databaseType): array
    {
        $connectionParams = $this->sandboxManager->getConnectionParams($user, $databaseType);

        return match ($databaseType) {
            DatabaseType::MYSQL => $this->resetMysqlSandbox($user, $connectionParams),
            DatabaseType::POSTGRESQL => $this->resetPostgresSandbox($user, $connectionParams),
            DatabaseType::SQLITE => $this->resetSqliteSandbox($connectionParams),
        };
    }

    private function resetMysqlSandbox(User $user, SandboxConnectionParams $connectionParams): array
    {
        $prefix = 'user_'.$connectionParams->userId.'_';
        $dropTablesQuery = "SELECT CONCAT('DROP TABLE IF EXISTS `', table_name, '`;') 
                           FROM information_schema.tables 
                           WHERE table_schema = DATABASE() 
                           AND table_name LIKE '{$prefix}%'";

        $result = $this->sandboxManager->executeQuery($user, DatabaseType::MYSQL, $dropTablesQuery);

        if (!$result->success) {
            throw new \RuntimeException('Failed to get table list: '.$result->error);
        }

        $dropQueries = array_column($result->data, 0);

        foreach ($dropQueries as $dropQuery) {
            $dropResult = $this->sandboxManager->executeQuery($user, DatabaseType::MYSQL, $dropQuery);

            if (!$dropResult->success) {
                throw new \RuntimeException('Failed to drop table: '.$dropResult->error);
            }
        }

        return [
            'dropped_tables' => count($dropQueries),
            'status' => 'success',
        ];
    }

    private function resetPostgresSandbox(User $user, SandboxConnectionParams $connectionParams): array
    {
        $prefix = 'user_'.$connectionParams->userId.'_';
        $dropTablesQuery = "SELECT 'DROP TABLE IF EXISTS \"' || tablename || '\" CASCADE;' 
                           FROM pg_tables 
                           WHERE schemaname = 'public' 
                           AND tablename LIKE '{$prefix}%'";

        $result = $this->sandboxManager->executeQuery($user, DatabaseType::POSTGRESQL, $dropTablesQuery);

        if (!$result->success) {
            throw new \RuntimeException('Failed to get table list: '.$result->error);
        }

        $dropQueries = array_column($result->data, '?column?');

        foreach ($dropQueries as $dropQuery) {
            $dropResult = $this->sandboxManager->executeQuery($user, DatabaseType::POSTGRESQL, $dropQuery);

            if (!$dropResult->success) {
                throw new \RuntimeException('Failed to drop table: '.$dropResult->error);
            }
        }

        return [
            'dropped_tables' => count($dropQueries),
            'status' => 'success',
        ];
    }

    private function resetSqliteSandbox(SandboxConnectionParams $connectionParams): array
    {
        if (file_exists($connectionParams->filePath)) {
            unlink($connectionParams->filePath);
        }

        // Recreate empty database
        touch($connectionParams->filePath);
        chmod($connectionParams->filePath, 0o666);

        return [
            'status' => 'success',
            'message' => 'SQLite file reset',
        ];
    }

    private function getDatabaseDescription(DatabaseType $type): string
    {
        return match ($type) {
            DatabaseType::MYSQL => 'MySQL relational database',
            DatabaseType::POSTGRESQL => 'PostgreSQL relational database',
            DatabaseType::SQLITE => 'SQLite embedded database',
        };
    }
}
