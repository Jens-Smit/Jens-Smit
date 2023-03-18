<?php

namespace App\Entity;

use App\Repository\OpeningTimeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OpeningTimeRepository::class)]
class OpeningTime
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $day = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $start = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $end = null;

    #[ORM\ManyToOne(inversedBy: 'openingTimes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Objekt $objekt = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $effective_date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDay(): ?int
    {
        return $this->day;
    }

    public function setDay(int $day): self
    {
        $this->day = $day;

        return $this;
    }

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(\DateTimeInterface $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(\DateTimeInterface $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function getObjekt(): ?Objekt
    {
        return $this->objekt;
    }

    public function setObjekt(?Objekt $objekt): self
    {
        $this->objekt = $objekt;

        return $this;
    }
    public function __toString()
    {
        return $this->id;
    }
    public function getEffectiveDate(): ?\DateTimeInterface
    {
        return $this->effective_date;
    }

    public function setEffectiveDate(\DateTimeInterface $effective_date): self
    {
        $this->effective_date = $effective_date;

        return $this;
    }
}
