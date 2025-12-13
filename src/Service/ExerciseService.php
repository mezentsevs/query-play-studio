<?php

namespace App\Service;

use App\DTO\SandboxQueryResult;
use App\Entity\Exercise;
use App\Entity\User;
use App\Entity\UserExerciseProgress;
use App\Enum\DatabaseType;
use App\Repository\ExerciseRepository;
use App\Repository\UserExerciseProgressRepository;
use Doctrine\ORM\EntityManagerInterface;

class ExerciseService
{
    public function __construct(
        private ExerciseRepository $exerciseRepository,
        private UserExerciseProgressRepository $progressRepository,
        private EntityManagerInterface $entityManager,
        private SandboxManager $sandboxManager,
    ) {
    }

    public function getAllExercises(): array
    {
        return $this->exerciseRepository->findAllOrdered();
    }

    public function getExercisesByDatabaseType(string $databaseType): array
    {
        return $this->exerciseRepository->findByDatabaseType($databaseType);
    }

    public function getExerciseById(int $id): ?Exercise
    {
        return $this->exerciseRepository->find($id);
    }

    public function getUserProgress(User $user): array
    {
        $progresses = $this->progressRepository->findUserProgress($user);
        $exercises = $this->exerciseRepository->findAllOrdered();

        $result = [];

        foreach ($exercises as $exercise) {
            $progress = $this->findProgressForExercise($progresses, $exercise);

            $result[] = [
                'exercise' => $this->formatExercise($exercise),
                'progress' => $progress ? $this->formatProgress($progress) : null,
            ];
        }

        return $result;
    }

    public function startExercise(User $user, Exercise $exercise): UserExerciseProgress
    {
        $progress = $this->progressRepository->findByUserAndExercise($user, $exercise->getId());

        if (!$progress) {
            $progress = new UserExerciseProgress();
            $progress->setUser($user);
            $progress->setExercise($exercise);
            $progress->setStatus('in_progress');
        } elseif ('not_started' === $progress->getStatus()) {
            $progress->setStatus('in_progress');
        }

        $this->entityManager->persist($progress);
        $this->entityManager->flush();

        return $progress;
    }

    public function submitSolution(
        User $user,
        Exercise $exercise,
        string $userQuery,
    ): array {
        $progress = $this->progressRepository->findByUserAndExercise($user, $exercise->getId());

        if (!$progress) {
            $progress = new UserExerciseProgress();

            $progress->setUser($user);
            $progress->setExercise($exercise);
        }

        $progress->incrementAttempts();
        $progress->setUserQuery($userQuery);

        // Setup sandbox with initial schema
        $databaseType = DatabaseType::tryFrom($exercise->getDatabaseType());

        if (!$databaseType) {
            throw new \RuntimeException('Invalid database type in exercise');
        }

        // Execute initial schema
        $this->sandboxManager->executeQuery($user, $databaseType, $exercise->getInitialSchema());

        // Execute user query
        $result = $this->sandboxManager->executeQuery($user, $databaseType, $userQuery);

        // Compare results
        $isCorrect = $this->compareResults($result, $exercise->getExpectedResult());

        if ($isCorrect) {
            $progress->setStatus('completed');
            $progress->setCompletedAt(new \DateTimeImmutable());
            $progress->setScore(100.0);
        } else {
            $progress->setStatus('in_progress');
        }

        $this->entityManager->persist($progress);
        $this->entityManager->flush();

        return [
            'success' => $isCorrect,
            'progress' => $progress,
            'query_result' => $result,
            'expected_result' => json_decode($exercise->getExpectedResult(), true),
        ];
    }

    public function resetProgress(User $user, ?int $exerciseId = null): array
    {
        if ($exerciseId) {
            $progress = $this->progressRepository->findByUserAndExercise($user, $exerciseId);

            if ($progress) {
                $this->entityManager->remove($progress);
                $this->entityManager->flush();

                return ['deleted' => 1];
            }

            return ['deleted' => 0];
        } else {
            $progresses = $this->progressRepository->findUserProgress($user);
            $count = count($progresses);

            foreach ($progresses as $progress) {
                $this->entityManager->remove($progress);
            }

            $this->entityManager->flush();

            return ['deleted' => $count];
        }
    }

    private function compareResults(SandboxQueryResult $actual, string $expectedJson): bool
    {
        if (!$actual->success) {
            return false;
        }

        $expected = json_decode($expectedJson, true);

        if (!is_array($expected) || !is_array($actual->data)) {
            return false;
        }

        // Normalize data for comparison
        $normalizedActual = $this->normalizeData($actual->data);
        $normalizedExpected = $this->normalizeData($expected);

        // Compare row count
        if (count($normalizedActual) !== count($normalizedExpected)) {
            return false;
        }

        // Compare each row (order matters for now)
        foreach ($normalizedExpected as $index => $expectedRow) {
            if (!isset($normalizedActual[$index])) {
                return false;
            }

            if (!$this->compareRows($expectedRow, $normalizedActual[$index])) {
                return false;
            }
        }

        return true;
    }

    private function normalizeData(array $data): array
    {
        $normalized = [];

        foreach ($data as $row) {
            $normalizedRow = [];

            foreach ($row as $key => $value) {
                // Convert numeric strings to numbers for comparison
                if (is_numeric($value)) {
                    $normalizedRow[$key] = (float) $value;
                } else {
                    $normalizedRow[$key] = $value;
                }
            }

            ksort($normalizedRow);
            $normalized[] = $normalizedRow;
        }

        // Sort rows by first column value for order-independent comparison
        usort($normalized, function ($a, $b) {
            $firstA = reset($a);
            $firstB = reset($b);

            if ($firstA == $firstB) {
                return 0;
            }

            return ($firstA < $firstB) ? -1 : 1;
        });

        return $normalized;
    }

    private function compareRows(array $expected, array $actual): bool
    {
        if (count($expected) !== count($actual)) {
            return false;
        }

        foreach ($expected as $key => $expectedValue) {
            if (!array_key_exists($key, $actual)) {
                return false;
            }

            // Loose comparison for numbers
            if (is_numeric($expectedValue) && is_numeric($actual[$key])) {
                if (abs((float) $expectedValue - (float) $actual[$key]) > 0.001) {
                    return false;
                }
            } elseif ($expectedValue != $actual[$key]) {
                return false;
            }
        }

        return true;
    }

    private function findProgressForExercise(array $progresses, Exercise $exercise): ?UserExerciseProgress
    {
        foreach ($progresses as $progress) {
            if ($progress->getExercise()->getId() === $exercise->getId()) {
                return $progress;
            }
        }

        return null;
    }

    private function formatExercise(Exercise $exercise): array
    {
        return [
            'id' => $exercise->getId(),
            'title' => $exercise->getTitle(),
            'description' => $exercise->getDescription(),
            'instructions' => $exercise->getInstructions(),
            'database_type' => $exercise->getDatabaseType(),
            'difficulty' => $exercise->getDifficulty(),
            'order_number' => $exercise->getOrderNumber(),
        ];
    }

    private function formatProgress(UserExerciseProgress $progress): array
    {
        return [
            'id' => $progress->getId(),
            'status' => $progress->getStatus(),
            'user_query' => $progress->getUserQuery(),
            'started_at' => $progress->getStartedAt()->format('Y-m-d H:i:s'),
            'completed_at' => $progress->getCompletedAt()?->format('Y-m-d H:i:s'),
            'attempts' => $progress->getAttempts(),
            'score' => $progress->getScore(),
        ];
    }
}
