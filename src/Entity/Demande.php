<?php

namespace App\Entity;

use App\Repository\DemandeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert; 

#[ORM\Entity(repositoryClass: DemandeRepository::class)]
class Demande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: "La description ne peut pas être vide.")] 
    #[Assert\Length(
        min: 10,
        max: 255,
        minMessage: "La description doit faire au moins {{ limit }} caractères.",
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères."
    )]
    private $description;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank(message: "La localisation ne peut pas être vide.")] 
    #[Assert\Length(
        min: 3,
        max: 100,
        minMessage: "La localisation doit faire au moins {{ limit }} caractères.",
        maxMessage: "La localisation ne peut pas dépasser {{ limit }} caractères."
    )]
    private $localisation;

    #[ORM\Column(type: 'string', length: 50)]
    #[Assert\NotBlank(message: "La catégorie ne peut pas être vide.")] 
    private $categorie;

     #[ORM\Column(type: 'string', length: 50, options: ["default" => "En cours"])]
     private string $statut = 'En cours';


     #[ORM\ManyToOne(targetEntity: Organisation::class, inversedBy: 'demandes')]
    #[ORM\JoinColumn(nullable: true)] 
    private ?Organisation $organisation = null;

     

    // Getters et Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(string $localisation): self
    {
        $this->localisation = $localisation;

        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(string $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getStatut(): string
{
    return $this->statut;
}

public function setStatut(string $statut): self
{
    $this->statut = $statut;
    return $this;
}

public function getOrganisation(): ?Organisation
    {
        return $this->organisation;
    }

    public function setOrganisation(?Organisation $organisation): self
    {
        $this->organisation = $organisation;
        return $this;
    }

    

}