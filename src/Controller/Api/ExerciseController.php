<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Service\ExerciseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/exercises')]
class ExerciseController extends AbstractController
{
    public function __construct(
        private ExerciseService $exerciseService,
    ) {
    }

    #[Route('', name: 'api_exercises_list', methods: ['GET'])]
    public function listExercises(Request $request): JsonResponse
    {
        $databaseType = $request->query->get('database_type');

        $exercises = $databaseType
            ? $this->exerciseService->getExercisesByDatabaseType($databaseType)
            : $this->exerciseService->getAllExercises();

        $formattedExercises = array_map(function ($exercise) {
            return [
                'id' => $exercise->getId(),
                'title' => $exercise->getTitle(),
                'description' => $exercise->getDescription(),
                'instructions' => $exercise->getInstructions(),
                'database_type' => $exercise->getDatabaseType(),
                'difficulty' => $exercise->getDifficulty(),
                'order_number' => $exercise->getOrderNumber(),
            ];
        }, $exercises);

        return new JsonResponse([
            'status' => 'success',
            'data' => $formattedExercises,
        ]);
    }

    #[Route('/{id}', name: 'api_exercises_show', methods: ['GET'])]
    public function showExercise(int $id): JsonResponse
    {
        $exercise = $this->exerciseService->getExerciseById($id);

        if (!$exercise) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Exercise not found',
            ], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'status' => 'success',
            'data' => [
                'id' => $exercise->getId(),
                'title' => $exercise->getTitle(),
                'description' => $exercise->getDescription(),
                'instructions' => $exercise->getInstructions(),
                'database_type' => $exercise->getDatabaseType(),
                'initial_schema' => $exercise->getInitialSchema(),
                'difficulty' => $exercise->getDifficulty(),
                'order_number' => $exercise->getOrderNumber(),
            ],
        ]);
    }

    #[Route('/{id}/start', name: 'api_exercises_start', methods: ['POST'])]
    public function startExercise(
        #[CurrentUser] User $user,
        int $id,
    ): JsonResponse {
        $exercise = $this->exerciseService->getExerciseById($id);

        if (!$exercise) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Exercise not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $progress = $this->exerciseService->startExercise($user, $exercise);

        return new JsonResponse([
            'status' => 'success',
            'message' => 'Exercise started',
            'data' => [
                'exercise_id' => $exercise->getId(),
                'progress_id' => $progress->getId(),
                'status' => $progress->getStatus(),
                'started_at' => $progress->getStartedAt()->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    #[Route('/{id}/submit', name: 'api_exercises_submit', methods: ['POST'])]
    public function submitSolution(
        #[CurrentUser] User $user,
        int $id,
        Request $request,
    ): JsonResponse {
        $exercise = $this->exerciseService->getExerciseById($id);

        if (!$exercise) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Exercise not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['query']) || !is_string($data['query'])) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Query is required and must be a string',
            ], Response::HTTP_BAD_REQUEST);
        }

        $result = $this->exerciseService->submitSolution($user, $exercise, $data['query']);

        return new JsonResponse([
            'status' => $result['success'] ? 'success' : 'error',
            'message' => $result['success'] ? 'Solution is correct!' : 'Solution is incorrect. Try again.',
            'data' => [
                'correct' => $result['success'],
                'progress' => [
                    'status' => $result['progress']->getStatus(),
                    'attempts' => $result['progress']->getAttempts(),
                    'completed_at' => $result['progress']->getCompletedAt()?->format('Y-m-d H:i:s'),
                    'score' => $result['progress']->getScore(),
                ],
                'query_result' => $result['query_result']->toArray(),
                'expected_result' => $result['expected_result'],
            ],
        ], $result['success'] ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST);
    }

    #[Route('/progress', name: 'api_exercises_progress', methods: ['GET'])]
    public function getUserProgress(
        #[CurrentUser] User $user,
    ): JsonResponse {
        $progress = $this->exerciseService->getUserProgress($user);

        return new JsonResponse([
            'status' => 'success',
            'data' => $progress,
        ]);
    }

    #[Route('/progress/reset', name: 'api_exercises_progress_reset', methods: ['POST'])]
    public function resetProgress(
        #[CurrentUser] User $user,
        Request $request,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $exerciseId = $data['exercise_id'] ?? null;

        $result = $this->exerciseService->resetProgress($user, $exerciseId);

        return new JsonResponse([
            'status' => 'success',
            'message' => 'Progress reset successfully',
            'data' => $result,
        ]);
    }

    #[Route('/{id}/hint', name: 'api_exercises_hint', methods: ['GET'])]
    public function getHint(
        int $id,
    ): JsonResponse {
        $exercise = $this->exerciseService->getExerciseById($id);

        if (!$exercise) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Exercise not found',
            ], Response::HTTP_NOT_FOUND);
        }

        // This would integrate with AI service in the future
        $hints = $this->generateHints($exercise);

        return new JsonResponse([
            'status' => 'success',
            'data' => [
                'exercise_id' => $exercise->getId(),
                'hints' => $hints,
            ],
        ]);
    }

    private function generateHints($exercise): array
    {
        // Simple hints based on exercise difficulty
        $difficulty = $exercise->getDifficulty();
        $databaseType = $exercise->getDatabaseType();

        $hints = [];

        if (1 === $difficulty) {
            $hints[] = 'Start with a basic SELECT statement';
            $hints[] = 'Make sure to include all required columns';
        } elseif (2 === $difficulty) {
            $hints[] = 'You might need to use aggregate functions or JOINs';
            $hints[] = 'Check the WHERE clause conditions carefully';
        } else {
            $hints[] = 'This exercise may require subqueries or window functions';
            $hints[] = 'Consider using Common Table Expressions (CTEs) for complex queries';
        }

        $hints[] = "Remember you're working with $databaseType database";

        return $hints;
    }
}
