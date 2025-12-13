<?php

namespace App\Entity;

use App\Repository\ExerciseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExerciseRepository::class)]
#[ORM\Table(name: 'exercise')]
class Exercise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\Column(type: 'text')]
    private string $instructions;

    #[ORM\Column(type: 'string', length: 20)]
    private string $databaseType;

    #[ORM\Column(type: 'text')]
    private string $initialSchema;

    #[ORM\Column(type: 'text')]
    private string $expectedResult;

    #[ORM\Column(type: 'integer')]
    private int $difficulty;

    #[ORM\Column(type: 'integer')]
    private int $orderNumber;

    #[ORM\OneToMany(targetEntity: UserExerciseProgress::class, mappedBy: 'exercise')]
    private Collection $userProgresses;

    public function __construct()
    {
        $this->userProgresses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getInstructions(): string
    {
        return $this->instructions;
    }

    public function setInstructions(string $instructions): self
    {
        $this->instructions = $instructions;

        return $this;
    }

    public function getDatabaseType(): string
    {
        return $this->databaseType;
    }

    public function setDatabaseType(string $databaseType): self
    {
        $this->databaseType = $databaseType;

        return $this;
    }

    public function getInitialSchema(): string
    {
        return $this->initialSchema;
    }

    public function setInitialSchema(string $initialSchema): self
    {
        $this->initialSchema = $initialSchema;

        return $this;
    }

    public function getExpectedResult(): string
    {
        return $this->expectedResult;
    }

    public function setExpectedResult(string $expectedResult): self
    {
        $this->expectedResult = $expectedResult;

        return $this;
    }

    public function getDifficulty(): int
    {
        return $this->difficulty;
    }

    public function setDifficulty(int $difficulty): self
    {
        $this->difficulty = $difficulty;

        return $this;
    }

    public function getOrderNumber(): int
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(int $orderNumber): self
    {
        $this->orderNumber = $orderNumber;

        return $this;
    }

    public function getUserProgresses(): Collection
    {
        return $this->userProgresses;
    }

    public function addUserProgress(UserExerciseProgress $userProgress): self
    {
        if (!$this->userProgresses->contains($userProgress)) {
            $this->userProgresses[] = $userProgress;

            $userProgress->setExercise($this);
        }

        return $this;
    }

    public function removeUserProgress(UserExerciseProgress $userProgress): self
    {
        if ($this->userProgresses->removeElement($userProgress)) {
            if ($userProgress->getExercise() === $this) {
                $userProgress->setExercise(null);
            }
        }

        return $this;
    }
}
