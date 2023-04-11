<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\DiensteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DiensteRepository::class)]
#[ApiResource]
class Dienste
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'dienste')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Dienstplan $Dienstplan = null;

    #[ORM\ManyToOne(inversedBy: 'dienste')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'dienste')]
    private ?Fehlzeiten $status = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $kommen = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $gehen = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDienstplan(): ?Dienstplan
    {
        return $this->Dienstplan;
    }

    public function setDienstplan(?Dienstplan $Dienstplan): self
    {
        $this->Dienstplan = $Dienstplan;

        return $this;
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

    public function getStatus(): ?Fehlzeiten
    {
        return $this->status;
    }

    public function setStatus(?Fehlzeiten $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getKommen(): ?\DateTimeInterface
    {
        return $this->kommen;
    }

    public function setKommen(\DateTimeInterface $kommen): self
    {
        $this->kommen = $kommen;

        return $this;
    }

    public function getGehen(): ?\DateTimeInterface
    {
        return $this->gehen;
    }

    public function setGehen(?\DateTimeInterface $gehen): self
    {
        $this->gehen = $gehen;

        return $this;
    }
}
