<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: "Le titre est obligatoire.")]
    private ?string $titre = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\NotBlank(message: "La date de début est obligatoire.")]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\NotBlank(message: "La date de fin est obligatoire.")]
    #[Assert\Expression(
        "this.getDateFin() > this.getDateDebut()",
        message: "The end date must be later than the start date."
    )]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: "Le Lieu est obligatoire.")]
    private ?string $lieu = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: "Le statut est obligatoire.")]
    private ?string $statut = null;
    public const STATUTS = [
        'Upcoming' => 'upcoming',
        'Ongoing' => 'ongoing',
        'Cancelled' => 'cancelled',
    ];

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: "La catégorie est obligatoire.")]
    private ?string $categorie = null;
    public const CATEGORIES = [
        'Clothing Exchange' => 'Clothing Exchange',
        'Recycling and Upcycling' => 'Recycling and Upcycling',
        'Second-Hand Market' => 'Second-Hand Market',
        'Repair Workshops' => 'Repair Workshops',
        'Community Clean-Up' => 'Community Clean-Up',
        'Ecology Conferences' => 'Ecology Conferences',
        'Solidarity Donation' => 'Solidarity Donation',
        'Local Sustainable Products' => 'Local Sustainable Products',
        'Book Exchange' => 'Book Exchange',
        'Eco-Friendly Gardening Workshops' => 'Eco-Friendly Gardening Workshops',
    ];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(?string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTimeInterface $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeInterface $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(?string $lieu): static
    {
        $this->lieu = $lieu;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(?string $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }
}
