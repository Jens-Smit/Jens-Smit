<?php

namespace App\Entity;

use App\Repository\SpecialOpeningTimeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SpecialOpeningTimeRepository::class)]
class SpecialOpeningTime
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $day = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $start = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $end = null;

    #[ORM\Column]
    private ?bool $close = null;

    #[ORM\ManyToOne(inversedBy: 'specialOpeningTimes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Objekt $objket = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDay(): ?\DateTimeInterface
    {
        return $this->day;
    }

    public function setDay(\DateTimeInterface $day): self
    {
        $this->day = $day;

        return $this;
    }

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(?\DateTimeInterface $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(?\DateTimeInterface $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function isClose(): ?bool
    {
        return $this->close;
    }

    public function setClose(bool $close): self
    {
        $this->close = $close;

        return $this;
    }

    public function getObjket(): ?Objekt
    {
        return $this->objket;
    }

    public function setObjket(?Objekt $objket): self
    {
        $this->objket = $objket;

        return $this;
    }
}
