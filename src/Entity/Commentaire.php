<?php

namespace App\Entity;

use App\Repository\CommentaireRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentaireRepository::class)]
class Commentaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateComm = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contentComm = null;

    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: "commentaires")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Article $article = null;

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

    public function getContentComm(): ?string
    {
        return $this->contentComm;
    }

    public function setContentComm(?string $contentComm): static
    {
        $this->contentComm = $contentComm;
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
