<?php

namespace App\Entity;

use App\Repository\AiConversationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AiConversationRepository::class)]
#[ORM\Table(name: 'ai_conversation')]
class AiConversation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'aiConversations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: 'string', length: 20)]
    private string $contextType;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $exerciseId = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $databaseType = null;

    #[ORM\Column(type: 'text')]
    private string $userMessage;

    #[ORM\Column(type: 'text')]
    private string $aiResponse;

    #[ORM\Column(type: 'json')]
    private array $metadata = [];

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getContextType(): string
    {
        return $this->contextType;
    }

    public function setContextType(string $contextType): self
    {
        $this->contextType = $contextType;

        return $this;
    }

    public function getExerciseId(): ?int
    {
        return $this->exerciseId;
    }

    public function setExerciseId(?int $exerciseId): self
    {
        $this->exerciseId = $exerciseId;

        return $this;
    }

    public function getDatabaseType(): ?string
    {
        return $this->databaseType;
    }

    public function setDatabaseType(?string $databaseType): self
    {
        $this->databaseType = $databaseType;

        return $this;
    }

    public function getUserMessage(): string
    {
        return $this->userMessage;
    }

    public function setUserMessage(string $userMessage): self
    {
        $this->userMessage = $userMessage;

        return $this;
    }

    public function getAiResponse(): string
    {
        return $this->aiResponse;
    }

    public function setAiResponse(string $aiResponse): self
    {
        $this->aiResponse = $aiResponse;

        return $this;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
