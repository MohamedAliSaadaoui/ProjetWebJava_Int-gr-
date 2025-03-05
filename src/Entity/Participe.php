<?php

namespace App\Entity;

use App\Repository\ParticipeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: ParticipeRepository::class)]
class Participe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "Veuillez sélectionner une date de participation.")]
    private ?\DateTimeInterface $date_participation = null;

    #[ORM\Column(nullable: true)]
    private ?int $nbr_place = null;

    #[ORM\ManyToOne(inversedBy: 'participations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $id_event = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Gedmo\Timestampable(on: "create")]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Gedmo\Timestampable(on: "update")]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", nullable: true)]
    private ?User $user = null;

    public function __construct() {
        $this->nbr_place = 0; // Assurer une valeur initiale
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateParticipation(): ?\DateTimeInterface
    {
        return $this->date_participation;
    }

    public function setDateParticipation(?\DateTimeInterface $date_participation): static
    {
        $this->date_participation = $date_participation;

        return $this;
    }

    public function getNbrPlace(): ?int
    {
        return $this->nbr_place;
    }

    public function setNbrPlace(?int $nbr_place): static
    {
        $this->nbr_place = $nbr_place;

        return $this;
    }

    public function getIdEvent(): ?Event
    {
        return $this->id_event;
    }

    public function setIdEvent(?Event $id_event): static
    {
        $this->id_event = $id_event;

        return $this;
    }

    #[Assert\Callback]
    public function validateDateParticipation(ExecutionContextInterface $context): void
    {
        if ($this->date_participation && $this->id_event) {
            $dateDebut = $this->id_event->getDateDebut();
            $dateFin = $this->id_event->getDateFin();

            if ($this->date_participation < $dateDebut || $this->date_participation > $dateFin) {
                $context->buildViolation("La date de participation doit être entre le {$dateDebut->format('d/m/Y')} et le {$dateFin->format('d/m/Y')}.")
                    ->atPath('date_participation')
                    ->addViolation();
            }
        }
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        
        return $this;
    }
}