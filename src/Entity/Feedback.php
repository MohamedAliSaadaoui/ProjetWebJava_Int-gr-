<?php

namespace App\Entity;

use App\Repository\FeedbackRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FeedbackRepository::class)]
class Feedback
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $idF = null;

    #[ORM\ManyToOne(targetEntity: Question::class, inversedBy: 'feedbacks')]
    #[ORM\JoinColumn(name: 'question_id', referencedColumnName: 'id_q', nullable: false)]
    private ?Question $question = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: "Le feedback ne peut pas être vide.")]
    private ?string $feedback_text = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $answeredAt = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: "Le nom d'utilisateur est obligatoire.")]
    private ?string $userName = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotNull(message: "Le statut d'approbation est obligatoire.")]
    private ?int $approved = 0; // Par défaut, le feedback n'est pas approuvé

    public function __construct()
    {
        $this->answeredAt = new \DateTime();
        $this->feedback_text = ''; // Initialisation pour éviter les erreurs
    }

    public function incrementApproved(): void
    {
        $this->approved++;
    }

    public function decrementApproved(): void
    {
        $this->approved--;
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

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function setUserName(string $userName): static
    {
        $this->userName = $userName;
        return $this;
    }

    public function getApproved(): ?int
    {
        return $this->approved;
    }

    public function setApproved(int $approved): static
    {
        $this->approved = $approved;
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