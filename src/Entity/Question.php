<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
#[ORM\HasLifecycleCallbacks] // Required for PrePersist to work
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $idQ = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $question_text = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt;

    #[ORM\OneToMany(mappedBy: 'question', targetEntity: Feedback::class, cascade: ['persist', 'remove'])]
    private Collection $feedbacks;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->feedbacks = new ArrayCollection();
    }

    public function getIdQ(): ?int
    {
        return $this->idQ;
    }

    public function getQuestionText(): ?string
    {
        return $this->question_text;
    }

    public function setQuestionText(string $question_text): static
    {
        $this->question_text = $question_text;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist] // Automatically set createdAt before inserting into DB
    public function setCreatedAtValue(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTime();
        }
    }

    public function getFeedbacks(): Collection
    {
        return $this->feedbacks;
    }

    public function addFeedback(Feedback $feedback): static
    {
        if (!$this->feedbacks->contains($feedback)) {
            $this->feedbacks->add($feedback);
            $feedback->setQuestion($this);
        }
        return $this;
    }

    public function removeFeedback(Feedback $feedback): static
    {
        if ($this->feedbacks->removeElement($feedback)) {
            if ($feedback->getQuestion() === $this) {
                $feedback->setQuestion(null);
            }
        }
        return $this;
    }
}
