<?php

namespace App\Entity;

use App\Repository\CommandRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use Doctrine\DBAL\Types\ConversionException;
use App\Entity\Livraison;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

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

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $methodePaiement = null;

    // Change the column definition to allow null values temporarily
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    // Address-related fields
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $adresseLivraison = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $codePostalLivraison = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $villeLivraison = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $paysLivraison = null;

    public function __construct()
    {
        $this->livraisons = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->createdAt = new \DateTime(); // Initialize createdAt with current date/time
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

    public function getMethodePaiement(): ?string
    {
        return $this->methodePaiement;
    }

    public function setMethodePaiement(?string $methodePaiement): self
    {
        $this->methodePaiement = $methodePaiement;
        return $this;
    }

    public function getAdresseLivraison(): ?string
    {
        return $this->adresseLivraison;
    }

    public function setAdresseLivraison(?string $adresseLivraison): self
    {
        $this->adresseLivraison = $adresseLivraison;
        return $this;
    }

    public function getCodePostalLivraison(): ?string
    {
        return $this->codePostalLivraison;
    }

    public function setCodePostalLivraison(?string $codePostalLivraison): self
    {
        $this->codePostalLivraison = $codePostalLivraison;
        return $this;
    }

    public function getVilleLivraison(): ?string
    {
        return $this->villeLivraison;
    }

    public function setVilleLivraison(?string $villeLivraison): self
    {
        $this->villeLivraison = $villeLivraison;
        return $this;
    }

    public function getPaysLivraison(): ?string
    {
        return $this->paysLivraison;
    }

    public function setPaysLivraison(?string $paysLivraison): self
    {
        $this->paysLivraison = $paysLivraison;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Getter method for totalCommande used in invoice template
     */
    public function getTotalCommande(): float
    {
        return $this->calculateTotal();
    }

    /**
     * Calculate the total price of the order
     */
    public function calculateTotal(): float
    {
        $total = 0;
        foreach ($this->products as $product) {
            $total += $product->getPrixDeVente();
        }
        
        if ($this->livraisons->count() > 0) {
            $total += $this->livraisons->first()->getTarif();
        }
        
        return $total;
    }

    #[Route('/admin/fix-command-dates', name: 'app_admin_fix_command_dates')]
    public function fixCommandDates(EntityManagerInterface $entityManager): Response
    {
        $commands = $entityManager->getRepository(Command::class)->findAll();
        $now = new \DateTime();
        
        foreach ($commands as $command) {
            if ($command->getCreatedAt() === null) {
                $command->setCreatedAt($now);
                $entityManager->persist($command);
            }
        }
        
        $entityManager->flush();
        
        return $this->redirectToRoute('app_admin_dash_board', [
            'message' => 'Commands dates fixed successfully!'
        ]);
    }
}

