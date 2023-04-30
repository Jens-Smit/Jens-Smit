<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ArbeitszeitRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArbeitszeitRepository::class)]
#[ApiResource]
class Arbeitszeit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $datum = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $Eintrittszeit = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $Austrittszeit = null;

    #[ORM\ManyToOne(inversedBy: 'arbeitszeits')]
    private ?Fehlzeiten $Fehlzeit = null;

    #[ORM\ManyToOne(inversedBy: 'arbeitszeits')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDatum(): ?\DateTimeInterface
    {
        return $this->datum;
    }

    public function setDatum(\DateTimeInterface $datum): self
    {
        $this->datum = $datum;

        return $this;
    }

    public function getEintrittszeit(): ?\DateTimeInterface
    {
        return $this->Eintrittszeit;
    }

    public function setEintrittszeit(\DateTimeInterface $Eintrittszeit): self
    {
        $this->Eintrittszeit = $Eintrittszeit;

        return $this;
    }

    public function getAustrittszeit(): ?\DateTimeInterface
    {
        return $this->Austrittszeit;
    }

    public function setAustrittszeit(?\DateTimeInterface $Austrittszeit): self
    {
        $this->Austrittszeit = $Austrittszeit;

        return $this;
    }

    public function getFehlzeit(): ?Fehlzeiten
    {
        return $this->Fehlzeit;
    }

    public function setFehlzeit(?Fehlzeiten $Fehlzeit): self
    {
        $this->Fehlzeit = $Fehlzeit;

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
}
