<?php

namespace App\Entity;

use App\Repository\ParticipeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParticipeRepository::class)]
class Participe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_participation = null;

    #[ORM\Column(nullable: true)]
    private ?int $nbr_place = null;

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
}
