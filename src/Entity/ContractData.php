<?php

namespace App\Entity;

use App\Repository\ContractDataRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContractDataRepository::class)]
class ContractData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'contractData')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $singDate = null;

    #[ORM\Column]
    private ?float $lohn = null;

    #[ORM\Column(nullable: true)]
    private ?int $stunden = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(nullable: true)]
    private ?int $Urlaub = null;

    #[ORM\ManyToOne(inversedBy: 'contractData')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CompensationTypes $CompensationTypes = null;

    #[ORM\Column(length: 255)]
    private ?string $bezeichnung = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $status = null;
    
    

    public function getId(): ?int
    {
        return $this->id;
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

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getSingDate(): ?\DateTimeInterface
    {
        return $this->singDate;
    }

    public function setSingDate(\DateTimeInterface $singDate): self
    {
        $this->singDate = $singDate;

        return $this;
    }

    public function getLohn(): ?float
    {
        return $this->lohn;
    }

    public function setLohn(float $lohn): self
    {
        $this->lohn = $lohn;

        return $this;
    }

    public function getStunden(): ?int
    {
        return $this->stunden;
    }

    public function setStunden(?int $stunden): self
    {
        $this->stunden = $stunden;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getUrlaub(): ?int
    {
        return $this->Urlaub;
    }

    public function setUrlaub(?int $Urlaub): self
    {
        $this->Urlaub = $Urlaub;

        return $this;
    }

    public function getCompensationTypes(): ?CompensationTypes
    {
        return $this->CompensationTypes;
    }

    public function setCompensationTypes(?CompensationTypes $CompensationTypes): self
    {
        $this->CompensationTypes = $CompensationTypes;

        return $this;
    }
  

    public function getBezeichnung(): ?string
    {
        return $this->bezeichnung;
    }

    public function setBezeichnung(string $bezeichnung): self
    {
        $this->bezeichnung = $bezeichnung;

        return $this;
    }
    public function __toString()
    {
        return $this->bezeichnung;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }
}
