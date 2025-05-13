<?php

namespace App\Entity;

use App\Repository\UserRepository;
use App\Enum\RoleEnum;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[Vich\Uploadable]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    /**
     * @Vich\UploadableField(mapping="user_photo", fileNameProperty="photo")
     * @var File|null
     */
    private ?File $photoFile = null;

    #[ORM\Column(length: 20)]
    private ?string $num_tel = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_naiss = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(type: Types::INTEGER)]
    private int $nb_article_achetes = 0;

    #[ORM\Column(type: Types::INTEGER)]
    private int $nb_article_vendus = 0;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    // Ajout de la propriété pour le token de réinitialisation
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $resetPasswordToken = null;

    // Constructeur pour initialiser les rôles
    public function __construct()
    {
        $this->roles = ['ROLE_USER'];
    }

    // Méthodes de récupération et d'attribution du token de réinitialisation
    public function getResetPasswordToken(): ?string
    {
        return $this->resetPasswordToken;
    }

    public function setResetPasswordToken(?string $resetPasswordToken): self
    {
        $this->resetPasswordToken = $resetPasswordToken;
        return $this;
    }

    // Autres méthodes de la classe User

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
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

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo ?? 'default.jpg';
        return $this;
    }

    public function setPhotoFile(?File $photoFile = null): void
    {
        $this->photoFile = $photoFile;
        if ($photoFile) {
            $this->updatedAt = new \DateTime();
        }
    }

    public function getPhotoFile(): ?File
    {
        return $this->photoFile;
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

    public function getNbArticleAchetes(): int
    {
        return $this->nb_article_achetes;
    }

    public function setNbArticleAchetes(int $nb_article_achetes): static
    {
        $this->nb_article_achetes = $nb_article_achetes;
        return $this;
    }

    public function getNbArticleVendus(): int
    {
        return $this->nb_article_vendus;
    }

    public function setNbArticleVendus(int $nb_article_vendus): static
    {
        $this->nb_article_vendus = $nb_article_vendus;
        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        if (!in_array('ROLE_USER', $roles)) {
            $roles[] = 'ROLE_USER'; // Ajouter ROLE_USER si ce n'est pas déjà présent
        }
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        foreach ($roles as $role) {
            if (!in_array($role, RoleEnum::getRoles())) {
                throw new \InvalidArgumentException("Rôle invalide : $role");
            }
        }
        $this->roles = $roles;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
    }

    // Ajouter la colonne pour l'expiration du token
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $resetPasswordTokenExpiresAt = null;
    
     

// Getter et setter pour resetPasswordTokenExpiresAt
public function getResetPasswordTokenExpiresAt(): ?\DateTimeInterface
{
    return $this->resetPasswordTokenExpiresAt;
}

public function setResetPasswordTokenExpiresAt(?\DateTimeInterface $resetPasswordTokenExpiresAt): self
{
    $this->resetPasswordTokenExpiresAt = $resetPasswordTokenExpiresAt;
    return $this;
}

#[ORM\Column(type: 'string', length: 255, nullable: true)]
private ?string $googleId = null;
#[ORM\OneToMany(mappedBy: 'user', targetEntity: Participe::class, cascade: ['persist', 'remove'])]
        private Collection $participations;
        
#[ORM\OneToMany(mappedBy: 'creator', targetEntity: Event::class, cascade: ['persist'])]
        private Collection $eventsCreated;
        private function initializeCollections(): void
        {
            $this->participations = new ArrayCollection();
            $this->eventsCreated = new ArrayCollection();
        }
public function getGoogleId(): ?string
{
    return $this->googleId;
}

public function setGoogleId(?string $googleId): self
{
    $this->googleId = $googleId;
    return $this;
}
  /**
    * @return Collection<int, Participe>
    */
    public function getParticipations(): Collection
    {
        return $this->participations;
    }
    
    public function addParticipation(Participe $participation): static
    {
        if (!$this->participations->contains($participation)) {
        $this->participations->add($participation);
        $participation->setUser($this);
    }
    
        return $this;
    }
    
    public function removeParticipation(Participe $participation): static
    {
        if ($this->participations->removeElement($participation)) {
            // set the owning side to null (unless already changed)
            if ($participation->getUser() === $this) {
            $participation->setUser(null);
            }
        }
    
         return $this;
     }
     
     /**
      * @return Collection<int, Event>
      */
     public function getEventsCreated(): Collection
     {
        return $this->eventsCreated;
    }
    
    public function addEventCreated(Event $event): static
    {
        if (!$this->eventsCreated->contains($event)) {
            $this->eventsCreated->add($event);
            $event->setCreator($this);
        }
    
        return $this;
    }
    
     public function removeEventCreated(Event $event): static
     {
         if ($this->eventsCreated->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getCreator() === $this) {
                $event->setCreator(null);
            }
        }
    
        return $this;
     }

}
