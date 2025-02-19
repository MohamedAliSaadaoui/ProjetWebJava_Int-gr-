<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $category = null;

    #[ORM\Column(length: 50)]
    private ?string $condition = null;

    #[ORM\Column(length: 50)]
    private ?string $color = null;

    #[ORM\Column(type: 'integer')]
    private ?int $stock = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $returnPolicy = null;

    // Getters and Setters
    public function getId(): ?int { return $this->id; }

    public function getName(): ?string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getCategory(): ?string { return $this->category; }
    public function setCategory(string $category): self { $this->category = $category; return $this; }

    public function getCondition(): ?string { return $this->condition; }
    public function setCondition(string $condition): self { $this->condition = $condition; return $this; }

    public function getColor(): ?string { return $this->color; }
    public function setColor(string $color): self { $this->color = $color; return $this; }

    public function getStock(): ?int { return $this->stock; }
    public function setStock(int $stock): self { $this->stock = $stock; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }

    public function getReturnPolicy(): ?string { return $this->returnPolicy; }
    public function setReturnPolicy(?string $returnPolicy): self { $this->returnPolicy = $returnPolicy; return $this; }
}

