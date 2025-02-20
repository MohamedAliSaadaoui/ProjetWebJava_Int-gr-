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
    private ?int $id;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $category = null;

    #[ORM\Column(length: 50)]
    private ?string $productCondition = null;

    #[ORM\Column(length: 50)]
    private ?string $color = null;

    #[ORM\Column(type: 'integer')]
    private ?int $stock = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $returnPolicy = null;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Panier::class)]
    private Collection $panierItems;

    public function __construct()
    {
        $this->panierItems = new ArrayCollection();
    }

    // Getters and Setters
    public function getId(): ?int { return $this->id; }

    public function getName(): ?string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getCategory(): ?string { return $this->category; }
    public function setCategory(string $category): self { $this->category = $category; return $this; }

    public function getProductCondition(): ?string { return $this->productCondition; }
    public function setProductCondition(string $productCondition): self { $this->productCondition = $productCondition; return $this; }

    public function getColor(): ?string { return $this->color; }
    public function setColor(string $color): self { $this->color = $color; return $this; }

    public function getStock(): ?int { return $this->stock; }
    public function setStock(int $stock): self { $this->stock = $stock; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }

    public function getReturnPolicy(): ?string { return $this->returnPolicy; }
    public function setReturnPolicy(?string $returnPolicy): self { $this->returnPolicy = $returnPolicy; return $this; }

    /**
     * @return Collection<int, Panier>
     */
    public function getPanierItems(): Collection
    {
        return $this->panierItems;
    }

    public function addPanierItem(Panier $panierItem): self
    {
        if (!$this->panierItems->contains($panierItem)) {
            $this->panierItems->add($panierItem);
            $panierItem->setProduct($this);
        }
        return $this;
    }

    public function removePanierItem(Panier $panierItem): self
    {
        if ($this->panierItems->removeElement($panierItem)) {
            // set the owning side to null (unless already changed)
            if ($panierItem->getProduct() === $this) {
                $panierItem->setProduct(null);
            }
        }
        return $this;
    }
}

