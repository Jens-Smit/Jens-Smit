<?php

namespace App\Entity;

use App\Repository\RecomonsationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecomonsationRepository::class)]
class Recomonsation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Reservation $reservation = null;

    #[ORM\Column(nullable: true)]
    private ?int $points = null;

    public function getId(): ?int
    {
        return $this->id;
    }
    public function __toString()
    {
        return $this->reservation;
    }
    public function getReservation(): ?Reservation
    {
        return $this->reservation;
    }

    public function setReservation(?Reservation $reservation): self
    {
        $this->reservation = $reservation;

        return $this;
    }

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(?int $points): self
    {
        $this->points = $points;

        return $this;
    }
}
