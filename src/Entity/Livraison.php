<?php

namespace App\Entity;

use App\Repository\LivraisonRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LivraisonRepository::class)]
class Livraison
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Livreur::class, inversedBy: 'livraisons')]
#[ORM\JoinColumn(name: 'livreur_id', referencedColumnName: 'id', nullable: false)]
private ?Livreur $livreur = null;

#[ORM\ManyToOne(targetEntity: Command::class, inversedBy: 'livraisons')]
#[ORM\JoinColumn(name: 'command_id', referencedColumnName: 'id', nullable: false)]
private ?Command $command = null;

    

    #[ORM\Column(type: "string")]
    private ?string $etat = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLivreur(): ?Livreur
    {
        return $this->livreur;
    }

    public function setLivreur(Livreur $livreur): self
    {
        $this->livreur = $livreur;
        return $this;
    }

    public function getCommand(): ?Command
    {
        return $this->command;
    }

    public function setCommand(Command $command): self
    {
        $this->command = $command;
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
}
