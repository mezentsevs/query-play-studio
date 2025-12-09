<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]
#[ORM\UniqueConstraint(name: 'uniq_email', columns: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private string $email;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'string')]
    private string $password;

    #[ORM\Column(type: 'string', length: 100)]
    private string $username;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(targetEntity: UserExerciseProgress::class, mappedBy: 'user')]
    private Collection $exerciseProgresses;

    #[ORM\OneToMany(targetEntity: SandboxLog::class, mappedBy: 'user')]
    private Collection $sandboxLogs;

    #[ORM\OneToMany(targetEntity: AiConversation::class, mappedBy: 'user')]
    private Collection $aiConversations;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->exerciseProgresses = new ArrayCollection();
        $this->sandboxLogs = new ArrayCollection();
        $this->aiConversations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function getExerciseProgresses(): Collection
    {
        return $this->exerciseProgresses;
    }

    public function addExerciseProgress(UserExerciseProgress $exerciseProgress): self
    {
        if (!$this->exerciseProgresses->contains($exerciseProgress)) {
            $this->exerciseProgresses[] = $exerciseProgress;
            $exerciseProgress->setUser($this);
        }

        return $this;
    }

    public function removeExerciseProgress(UserExerciseProgress $exerciseProgress): self
    {
        if ($this->exerciseProgresses->removeElement($exerciseProgress)) {
            if ($exerciseProgress->getUser() === $this) {
                $exerciseProgress->setUser(null);
            }
        }

        return $this;
    }

    public function getSandboxLogs(): Collection
    {
        return $this->sandboxLogs;
    }

    public function addSandboxLog(SandboxLog $sandboxLog): self
    {
        if (!$this->sandboxLogs->contains($sandboxLog)) {
            $this->sandboxLogs[] = $sandboxLog;
            $sandboxLog->setUser($this);
        }

        return $this;
    }

    public function removeSandboxLog(SandboxLog $sandboxLog): self
    {
        if ($this->sandboxLogs->removeElement($sandboxLog)) {
            if ($sandboxLog->getUser() === $this) {
                $sandboxLog->setUser(null);
            }
        }

        return $this;
    }

    public function getAiConversations(): Collection
    {
        return $this->aiConversations;
    }

    public function addAiConversation(AiConversation $aiConversation): self
    {
        if (!$this->aiConversations->contains($aiConversation)) {
            $this->aiConversations[] = $aiConversation;
            $aiConversation->setUser($this);
        }

        return $this;
    }

    public function removeAiConversation(AiConversation $aiConversation): self
    {
        if ($this->aiConversations->removeElement($aiConversation)) {
            if ($aiConversation->getUser() === $this) {
                $aiConversation->setUser(null);
            }
        }

        return $this;
    }
}
