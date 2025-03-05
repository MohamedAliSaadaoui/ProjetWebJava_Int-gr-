<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'products')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToMany(targetEntity: Command::class, mappedBy: 'products')]
    private Collection $commands;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $objetAVendre = null;

    #[ORM\Column(type: 'text')]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $genre = null;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $etat = null;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $taille = null;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $couleur = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?float $prixDeVente = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?float $prixOriginal = null;

    // Contact Info
    #[ORM\Column(type: 'string', length: 20)]
    private ?string $telephone = null;

    #[ORM\Column(type: 'string', length: 100)]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(type: 'string', length: 10)]
    private ?string $codePostal = null;

    #[ORM\Column(type: 'string', length: 100)]
    private ?string $ville = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $photo;

    public function __construct()
    {
        $this->commands = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getObjetAVendre(): ?string
    {
        return $this->objetAVendre;
    }

    public function setObjetAVendre(string $objetAVendre): self
    {
        $this->objetAVendre = $objetAVendre;
        return $this;
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

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(string $genre): self
    {
        $this->genre = $genre;
        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): self
    {
        $this->etat = $etat;
        return $this;
    }

    public function getTaille(): ?string
    {
        return $this->taille;
    }

    public function setTaille(string $taille): self
    {
        $this->taille = $taille;
        return $this;
    }

    public function getCouleur(): ?string
    {
        return $this->couleur;
    }

    public function setCouleur(string $couleur): self
    {
        $this->couleur = $couleur;
        return $this;
    }

    public function getPrixDeVente(): ?float
    {
        return $this->prixDeVente;
    }

    public function setPrixDeVente(float $prixDeVente): self
    {
        $this->prixDeVente = $prixDeVente;
        return $this;
    }

    public function getPrixOriginal(): ?float
    {
        return $this->prixOriginal;
    }

    public function setPrixOriginal(float $prixOriginal): self
    {
        $this->prixOriginal = $prixOriginal;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;
        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(string $codePostal): self
    {
        $this->codePostal = $codePostal;
        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): self
    {
        $this->ville = $ville;
        return $this;
    }

    /**
     * @return Collection<int, Command>
     */
    public function getCommands(): Collection
    {
        return $this->commands;
    }

    public function addCommand(Command $command): self
    {
        if (!$this->commands->contains($command)) {
            $this->commands[] = $command;
        }

        return $this;
    }

    public function removeCommand(Command $command): self
    {
        $this->commands->removeElement($command);
        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;
        
        return $this;
    }
}
