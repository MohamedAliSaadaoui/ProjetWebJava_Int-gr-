<?php

namespace App\Entity;

use App\Repository\FeedbackRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FeedbackRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Feedback
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $idF = null;

    #[ORM\ManyToOne(targetEntity: Question::class, inversedBy: 'feedbacks')]
    #[ORM\JoinColumn(name: "idQ", referencedColumnName: "idQ", nullable: false)]
    private ?Question $question = null;

    #[ORM\Column(type: Types::TEXT, nullable: false)]
    private string $feedback_text = '';

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $answeredAt;

    public function __construct()
    {
        $this->answeredAt = new \DateTime();
        $this->feedback_text = ''; // Initialize to avoid null errors
    }

    public function getIdF(): ?int
    {
        return $this->idF;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): static
    {
        $this->question = $question;
        return $this;
    }

    public function getFeedbackText(): ?string
    {
        return $this->feedback_text;
    }

    public function setFeedbackText(string $feedback_text): static
    {
        $this->feedback_text = $feedback_text;
        return $this;
    }

    public function getAnsweredAt(): ?\DateTimeInterface
    {
        return $this->answeredAt;
    }

    public function setAnsweredAt(\DateTimeInterface $answeredAt): self
    {
        $this->answeredAt = $answeredAt;
        return $this;
    }

    #[ORM\PrePersist]
    public function setAnsweredAtValue(): void
    {
        if ($this->answeredAt === null) {
            $this->answeredAt = new \DateTime();
        }
    }
}
