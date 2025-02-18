<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $photo = null;

    #[ORM\Column(length: 255)]
    private ?string $num_tel = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_naiss = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(length: 255)]
    private ?string $nb_article_achetes = null;

    #[ORM\Column(length: 255)]
    private ?string $nb_article_vendus = null;

    #[ORM\Column(length: 255)]
    private ?string $role = null;

    // Propriété $roles ajoutée pour stocker les rôles
    #[ORM\Column(length: 255)]
    private ?string $roles = null; 

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
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

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(string $photo): static
    {
        $this->photo = $photo;
        return $this;
    }

    public function getNumTel(): ?string
    {
        return $this->num_tel;
    }

    public function setNumTel(string $num_tel): static
    {
        $this->num_tel = $num_tel;
        return $this;
    }

    public function getDateNaiss(): ?\DateTimeInterface
    {
        return $this->date_naiss;
    }

    public function setDateNaiss(\DateTimeInterface $date_naiss): static
    {
        $this->date_naiss = $date_naiss;
        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;
        return $this;
    }

    public function getNbArticleAchetes(): ?string
    {
        return $this->nb_article_achetes;
    }

    public function setNbArticleAchetes(string $nb_article_achetes): static
    {
        $this->nb_article_achetes = $nb_article_achetes;
        return $this;
    }

    public function getNbArticleVendus(): ?string
    {
        return $this->nb_article_vendus;
    }

    public function setNbArticleVendus(string $nb_article_vendus): static
    {
        $this->nb_article_vendus = $nb_article_vendus;
        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;
        return $this;
    }

    // Utilisation de la nouvelle propriété $roles
    public function getRoles(): array
    {
        // Retourne les rôles sous forme de tableau
        return explode(',', $this->roles); // Conversion de la chaîne de caractères en tableau
    }

    public function setRoles(array $roles): static
    {
        // Conversion du tableau en chaîne de caractères pour le stockage
        $this->roles = implode(',', $roles);
        return $this;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials()
    {
        // Si tu utilises des données sensibles supplémentaires, tu peux les effacer ici
    }
}
