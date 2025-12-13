<?php

namespace App\Service;

use App\Entity\AiConversation;
use App\Entity\User;
use App\Enum\DatabaseType;
use App\Repository\AiConversationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AiAssistantService
{
    private const PROVIDERS = ['lmstudio', 'openai', 'ollama'];
    private const DEFAULT_MODELS = [
        'lmstudio' => 'gpt-4',
        'openai' => 'gpt-4-turbo-preview',
        'ollama' => 'llama2',
    ];

    public function __construct(
        private AiConversationRepository $aiConversationRepository,
        private EntityManagerInterface $entityManager,
        private HttpClientInterface $httpClient,
        private string $aiEnabled,
        private string $aiProvider,
        private string $aiApiKey,
        private string $aiBaseUrl,
        private string $aiModel,
        private int $aiMaxTokens,
        private float $aiTemperature,
    ) {
    }

    public function isEnabled(): bool
    {
        return filter_var($this->aiEnabled, FILTER_VALIDATE_BOOL);
    }

    public function getSupportedProviders(): array
    {
        return self::PROVIDERS;
    }

    public function askQuestion(
        User $user,
        string $question,
        ?string $contextType = 'general',
        ?int $exerciseId = null,
        ?DatabaseType $databaseType = null,
        ?string $sqlQuery = null,
        ?string $errorMessage = null,
    ): array {
        if (!$this->isEnabled()) {
            throw new \RuntimeException('AI assistant is disabled');
        }

        if (!in_array($this->aiProvider, self::PROVIDERS, true)) {
            throw new \RuntimeException('Unsupported AI provider');
        }

        // Prepare the prompt based on context
        $prompt = $this->preparePrompt($question, $contextType, $exerciseId, $databaseType, $sqlQuery, $errorMessage);

        // Prepare messages for the AI
        $messages = $this->prepareMessages($prompt);

        // Call the AI API
        $response = $this->callAiApi($messages);

        // Extract the response text
        $aiResponse = $this->extractResponse($response);

        // Log the conversation
        $conversation = $this->logConversation(
            $user,
            $contextType,
            $exerciseId,
            $databaseType?->value,
            $question,
            $aiResponse,
            [
                'provider' => $this->aiProvider,
                'model' => $this->aiModel,
                'prompt_tokens' => $response['usage']['prompt_tokens'] ?? null,
                'completion_tokens' => $response['usage']['completion_tokens'] ?? null,
                'total_tokens' => $response['usage']['total_tokens'] ?? null,
            ],
        );

        return [
            'success' => true,
            'response' => $aiResponse,
            'conversation_id' => $conversation->getId(),
            'metadata' => [
                'provider' => $this->aiProvider,
                'model' => $this->aiModel,
                'context' => $contextType,
            ],
        ];
    }

    public function explainSqlError(
        User $user,
        string $sqlQuery,
        string $errorMessage,
        DatabaseType $databaseType,
    ): array {
        $question = "I got this error when executing SQL query in {$databaseType->value}: $errorMessage\n\nQuery: $sqlQuery\n\nCan you explain what's wrong and how to fix it?";

        return $this->askQuestion(
            $user,
            $question,
            'sql_error',
            null,
            $databaseType,
            $sqlQuery,
            $errorMessage,
        );
    }

    public function optimizeSqlQuery(
        User $user,
        string $sqlQuery,
        DatabaseType $databaseType,
        ?string $schema = null,
    ): array {
        $question = "Can you optimize this SQL query for {$databaseType->value}?".
                   ($schema ? "\n\nSchema:\n$schema" : '').
                   "\n\nQuery:\n$sqlQuery";

        return $this->askQuestion(
            $user,
            $question,
            'sql_optimization',
            null,
            $databaseType,
            $sqlQuery,
        );
    }

    public function getExerciseHelp(
        User $user,
        int $exerciseId,
        string $question,
    ): array {
        return $this->askQuestion(
            $user,
            $question,
            'exercise_help',
            $exerciseId,
        );
    }

    public function getConversationHistory(User $user, int $limit = 50): array
    {
        return $this->aiConversationRepository->findByUser($user, $limit);
    }

    private function preparePrompt(
        string $question,
        string $contextType,
        ?int $exerciseId,
        ?DatabaseType $databaseType,
        ?string $sqlQuery,
        ?string $errorMessage,
    ): string {
        $prompt = 'You are a SQL expert and database tutor. ';

        switch ($contextType) {
            case 'sql_error':
                $prompt .= "A user encountered an error while executing a SQL query in {$databaseType->value}. ";
                $prompt .= 'Explain what the error means and how to fix it. ';
                $prompt .= "Be specific about the {$databaseType->value} syntax. ";
                $prompt .= "Error: $errorMessage\n";
                $prompt .= "Query: $sqlQuery\n";
                $prompt .= "Question: $question";

                break;

            case 'sql_optimization':
                $prompt .= "A user wants to optimize a SQL query for {$databaseType->value}. ";
                $prompt .= "Provide specific optimization suggestions for {$databaseType->value}. ";
                $prompt .= 'Explain why your suggestions improve performance. ';
                $prompt .= "Query: $sqlQuery\n";
                $prompt .= "Question: $question";

                break;

            case 'exercise_help':
                $prompt .= "A user needs help with exercise ID: $exerciseId. ";
                $prompt .= 'Provide helpful guidance without giving away the complete solution. ';
                $prompt .= 'Encourage learning and understanding. ';
                $prompt .= "Question: $question";

                break;

            case 'general':
            default:
                $prompt .= "Answer the user's question about databases and SQL. ";
                $prompt .= 'Be clear, concise, and educational. ';

                if ($databaseType) {
                    $prompt .= "Focus on {$databaseType->value} specifically. ";
                }

                $prompt .= "Question: $question";

                break;
        }

        return $prompt;
    }

    private function prepareMessages(string $prompt): array
    {
        return [
            [
                'role' => 'system',
                'content' => 'You are a helpful SQL expert and database tutor. Provide accurate, educational responses.',
            ],
            [
                'role' => 'user',
                'content' => $prompt,
            ],
        ];
    }

    private function callAiApi(array $messages): array
    {
        $url = $this->getApiUrl();
        $headers = $this->getApiHeaders();
        $body = $this->getApiBody($messages);

        try {
            $response = $this->httpClient->request('POST', $url, [
                'headers' => $headers,
                'json' => $body,
                'timeout' => 30,
            ]);

            if (Response::HTTP_OK !== $response->getStatusCode()) {
                throw new \RuntimeException('AI API returned error: '.$response->getContent(false));
            }

            return $response->toArray();
        } catch (ExceptionInterface $e) {
            throw new \RuntimeException('Failed to call AI API: '.$e->getMessage());
        }
    }

    private function getApiUrl(): string
    {
        if ('openai' === $this->aiProvider) {
            return 'https://api.openai.com/v1/chat/completions';
        }

        return rtrim($this->aiBaseUrl, '/').'/chat/completions';
    }

    private function getApiHeaders(): array
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];

        if ('openai' === $this->aiProvider) {
            $headers['Authorization'] = 'Bearer '.$this->aiApiKey;
        } elseif ('ollama' === $this->aiProvider) {
            // Ollama doesn't usually require auth
        } elseif ('lmstudio' === $this->aiProvider) {
            // LM Studio doesn't usually require auth, but can accept API key
            if (!empty($this->aiApiKey)) {
                $headers['Authorization'] = 'Bearer '.$this->aiApiKey;
            }
        }

        return $headers;
    }

    private function getApiBody(array $messages): array
    {
        $model = $this->aiModel ?: self::DEFAULT_MODELS[$this->aiProvider];

        return [
            'model' => $model,
            'messages' => $messages,
            'max_tokens' => $this->aiMaxTokens,
            'temperature' => $this->aiTemperature,
            'stream' => false,
        ];
    }

    private function extractResponse(array $apiResponse): string
    {
        if ('openai' === $this->aiProvider || 'lmstudio' === $this->aiProvider) {
            return $apiResponse['choices'][0]['message']['content'] ?? 'No response from AI';
        } elseif ('ollama' === $this->aiProvider) {
            return $apiResponse['response'] ?? $apiResponse['message']['content'] ?? 'No response from AI';
        }

        return 'No response from AI';
    }

    private function logConversation(
        User $user,
        string $contextType,
        ?int $exerciseId,
        ?string $databaseType,
        string $userMessage,
        string $aiResponse,
        array $metadata = [],
    ): AiConversation {
        $conversation = new AiConversation();

        $conversation->setUser($user);
        $conversation->setContextType($contextType);
        $conversation->setExerciseId($exerciseId);
        $conversation->setDatabaseType($databaseType);
        $conversation->setUserMessage($userMessage);
        $conversation->setAiResponse($aiResponse);
        $conversation->setMetadata($metadata);

        $this->entityManager->persist($conversation);
        $this->entityManager->flush();

        return $conversation;
    }
}
