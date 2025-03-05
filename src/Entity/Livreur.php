<?php
namespace App\Entity;

use App\Repository\LivreurRepository;
use App\Entity\Command;
use App\Entity\Livraison;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: LivreurRepository::class)]
class Livreur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\OneToMany(targetEntity: Livraison::class, mappedBy: 'livreur', orphanRemoval: true)]
private Collection $livraisons; 

    #[ORM\Column(type: "string")]
    private ?string $nom = null;

    #[ORM\Column(type: "string")]
    private ?string $prenom = null;

    #[ORM\Column(type: "string", unique: true)]
    private ?string $email = null;

    #[ORM\Column(type: "string")]
    private ?string $tel = null;

    #[ORM\Column(type: "string")]
    private ?string $etatDispo = null;



    public function __construct()
    {
        $this->livraisons = new ArrayCollection(); // Initialize the collection
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
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

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(string $tel): self
    {
        $this->tel = $tel;
        return $this;
    }

    public function getEtatDispo(): ?string
    {
        return $this->etatDispo;
    }

    public function setEtatDispo(string $etatDispo): self
    {
        $this->etatDispo = $etatDispo;
        return $this;
    }

    /**
     * @return Collection<int, Livraison>
     */
    public function getLivraisons(): Collection
    {
        return $this->livraisons;
    }

    public function addLivraison(Livraison $livraison): self
    {
        if (!$this->livraisons->contains($livraison)) {
            $this->livraisons[] = $livraison;
            $livraison->setLivreur($this); // Ensure bi-directional relationship is maintained
        }

        return $this;
    }

  
}
