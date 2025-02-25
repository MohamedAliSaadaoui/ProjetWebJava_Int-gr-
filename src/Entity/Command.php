<?php

namespace App\Entity;

use App\Repository\CommandRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use Doctrine\DBAL\Types\ConversionException;
use App\Entity\Livraison;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: CommandRepository::class)]
class Command
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'commands')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToMany(targetEntity: Product::class, inversedBy: 'commands')]
    #[ORM\JoinTable(name: 'command_products')]
    private Collection $products;

    #[ORM\OneToMany(targetEntity: Livraison::class, mappedBy: 'command', orphanRemoval: true)]
    private Collection $livraisons;

    #[ORM\Column(type: Types::STRING)]
    private ?string $etat = null;

    public function __construct()
    {
        $this->livraisons = new ArrayCollection();
        $this->products = new ArrayCollection();
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

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        $this->products->removeElement($product);
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
            $livraison->setCommand($this);
        }

        return $this;
    }
}

