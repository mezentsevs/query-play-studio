<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Enum\DatabaseType;
use App\Service\AiAssistantService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/ai')]
class AiAssistantController extends AbstractController
{
    public function __construct(
        private AiAssistantService $aiAssistantService,
    ) {
    }

    #[Route('/status', name: 'api_ai_status', methods: ['GET'])]
    public function getStatus(): JsonResponse
    {
        return new JsonResponse([
            'status' => 'success',
            'data' => [
                'enabled' => $this->aiAssistantService->isEnabled(),
                'provider' => $this->aiAssistantService->isEnabled() ? 'configured' : 'disabled',
                'supported_providers' => $this->aiAssistantService->getSupportedProviders(),
            ],
        ]);
    }

    #[Route('/ask', name: 'api_ai_ask', methods: ['POST'])]
    public function askQuestion(
        #[CurrentUser] User $user,
        Request $request,
    ): JsonResponse {
        if (!$this->aiAssistantService->isEnabled()) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'AI assistant is disabled',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['question']) || !is_string($data['question'])) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Question is required and must be a string',
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $contextType = $data['context_type'] ?? 'general';
            $exerciseId = $data['exercise_id'] ?? null;
            $databaseType = isset($data['database_type']) ? DatabaseType::tryFrom($data['database_type']) : null;
            $sqlQuery = $data['sql_query'] ?? null;
            $errorMessage = $data['error_message'] ?? null;

            $result = $this->aiAssistantService->askQuestion(
                $user,
                $data['question'],
                $contextType,
                $exerciseId,
                $databaseType,
                $sqlQuery,
                $errorMessage,
            );

            return new JsonResponse([
                'status' => 'success',
                'data' => $result,
            ]);
        } catch (\RuntimeException $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/explain-error', name: 'api_ai_explain_error', methods: ['POST'])]
    public function explainSqlError(
        #[CurrentUser] User $user,
        Request $request,
    ): JsonResponse {
        if (!$this->aiAssistantService->isEnabled()) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'AI assistant is disabled',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['sql_query'], $data['error_message'], $data['database_type'])) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'sql_query, error_message, and database_type are required',
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $databaseType = DatabaseType::tryFrom($data['database_type']);

            if (!$databaseType) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Invalid database type',
                ], Response::HTTP_BAD_REQUEST);
            }

            $result = $this->aiAssistantService->explainSqlError(
                $user,
                $data['sql_query'],
                $data['error_message'],
                $databaseType,
            );

            return new JsonResponse([
                'status' => 'success',
                'data' => $result,
            ]);
        } catch (\RuntimeException $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/optimize-query', name: 'api_ai_optimize_query', methods: ['POST'])]
    public function optimizeSqlQuery(
        #[CurrentUser] User $user,
        Request $request,
    ): JsonResponse {
        if (!$this->aiAssistantService->isEnabled()) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'AI assistant is disabled',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['sql_query'], $data['database_type'])) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'sql_query and database_type are required',
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $databaseType = DatabaseType::tryFrom($data['database_type']);

            if (!$databaseType) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Invalid database type',
                ], Response::HTTP_BAD_REQUEST);
            }

            $schema = $data['schema'] ?? null;

            $result = $this->aiAssistantService->optimizeSqlQuery(
                $user,
                $data['sql_query'],
                $databaseType,
                $schema,
            );

            return new JsonResponse([
                'status' => 'success',
                'data' => $result,
            ]);
        } catch (\RuntimeException $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/exercise-help', name: 'api_ai_exercise_help', methods: ['POST'])]
    public function getExerciseHelp(
        #[CurrentUser] User $user,
        Request $request,
    ): JsonResponse {
        if (!$this->aiAssistantService->isEnabled()) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'AI assistant is disabled',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['exercise_id'], $data['question'])) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'exercise_id and question are required',
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $result = $this->aiAssistantService->getExerciseHelp(
                $user,
                (int) $data['exercise_id'],
                $data['question'],
            );

            return new JsonResponse([
                'status' => 'success',
                'data' => $result,
            ]);
        } catch (\RuntimeException $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/conversations', name: 'api_ai_conversations', methods: ['GET'])]
    public function getConversations(
        #[CurrentUser] User $user,
        Request $request,
    ): JsonResponse {
        $limit = (int) $request->query->get('limit', 50);

        $conversations = $this->aiAssistantService->getConversationHistory($user, $limit);

        $formattedConversations = array_map(function ($conversation) {
            return [
                'id' => $conversation->getId(),
                'context_type' => $conversation->getContextType(),
                'exercise_id' => $conversation->getExerciseId(),
                'database_type' => $conversation->getDatabaseType(),
                'user_message' => $conversation->getUserMessage(),
                'ai_response' => $conversation->getAiResponse(),
                'created_at' => $conversation->getCreatedAt()->format('Y-m-d H:i:s'),
                'metadata' => $conversation->getMetadata(),
            ];
        }, $conversations);

        return new JsonResponse([
            'status' => 'success',
            'data' => $formattedConversations,
        ]);
    }
}
