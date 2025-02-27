<?php

namespace App\Entity;

use App\Repository\OrganisationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrganisationRepository::class)]
class Organisation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom de l'organisation ne peut pas être vide.")] // 👈 Contrainte
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Le nom doit faire au moins {{ limit }} caractères.",
        maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'email ne peut pas être vide.")] // 👈 Contrainte
    #[Assert\Email(message: "L'email '{{ value }}' n'est pas valide.")] // 👈 Contrainte
    private ?string $email = null;

    #[ORM\Column(type: 'text', nullable: true)]
     private ?string $description = null;

        #[ORM\Column(length: 20, nullable: true)]
        private ?string $telephone = null;

        #[ORM\Column(length: 255, nullable: true)]
        private ?string $adresse = null;


        #[ORM\OneToMany(targetEntity: Demande::class, mappedBy: 'organisation')]
        private Collection $demandes;

    public function __construct()
    {
        $this->demandes = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getDescription(): ?string
{
    return $this->description;
}

public function setDescription(?string $description): self
{
    $this->description = $description;
    return $this;
}

public function getTelephone(): ?string
{
    return $this->telephone;
}

public function setTelephone(?string $telephone): self
{
    $this->telephone = $telephone;
    return $this;
}

public function getAdresse(): ?string
{
    return $this->adresse;
}

public function setAdresse(?string $adresse): self
{
    $this->adresse = $adresse;
    return $this;
}

public function getDemandes(): Collection
    {
        return $this->demandes;
    }

    public function addDemande(Demande $demande): self
    {
        if (!$this->demandes->contains($demande)) {
            $this->demandes[] = $demande;
            $demande->setOrganisation($this);
        }
        return $this;
    }

    public function removeDemande(Demande $demande): self
    {
        if ($this->demandes->removeElement($demande)) {
            if ($demande->getOrganisation() === $this) {
                $demande->setOrganisation(null);
            }
        }
        return $this;
    }

}
