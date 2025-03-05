<?php

namespace App\Entity;

use App\Repository\CommentaireRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommentaireRepository::class)]
class Commentaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateComm = null;

    #[ORM\Column(type: Types::TEXT, nullable: false)]
    #[Assert\NotBlank(message: "Le contenu du commentaire ne peut pas être vide.")]
    private ?string $contenuComm = null;

    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: "commentaires")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Article $article = null;

 
    public function setCreatedAt(): void
    {
        if ($this->dateComm === null) {
            $this->dateComm = new \DateTime();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateComm(): ?\DateTimeInterface
    {
        return $this->dateComm;
    }

    public function setDateComm(?\DateTimeInterface $dateComm): static
    {
        $this->dateComm = $dateComm;
        return $this;
    }

    public function getContenuComm(): ?string
    {
        return $this->contenuComm;
    }
    
    public function setContenuComm(string $contenuComm): self
    {
        $this->contenuComm = $contenuComm;
        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): static
    {
        $this->article = $article;
        return $this;
    }
}