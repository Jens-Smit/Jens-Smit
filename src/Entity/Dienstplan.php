<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\DienstplanRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DienstplanRepository::class)]
#[ApiResource]
class Dienstplan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $bezeichnung = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $start = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $ende = null;

    #[ORM\ManyToOne(inversedBy: 'dienstplans')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Objekt $Objket = null;

    #[ORM\OneToMany(mappedBy: 'Dienstplan', targetEntity: Dienste::class)]
    private Collection $dienste;

    public function __construct()
    {
        $this->dienste = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(\DateTimeInterface $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getEnde(): ?\DateTimeInterface
    {
        return $this->ende;
    }

    public function setEnde(?\DateTimeInterface $ende): self
    {
        $this->ende = $ende;

        return $this;
    }

    public function getObjket(): ?Objekt
    {
        return $this->Objket;
    }

    public function setObjket(?Objekt $Objket): self
    {
        $this->Objket = $Objket;

        return $this;
    }

    /**
     * @return Collection<int, Dienste>
     */
    public function getDienste(): Collection
    {
        return $this->dienste;
    }

    public function addDienste(Dienste $dienste): self
    {
        if (!$this->dienste->contains($dienste)) {
            $this->dienste->add($dienste);
            $dienste->setDienstplan($this);
        }

        return $this;
    }

    public function removeDienste(Dienste $dienste): self
    {
        if ($this->dienste->removeElement($dienste)) {
            // set the owning side to null (unless already changed)
            if ($dienste->getDienstplan() === $this) {
                $dienste->setDienstplan(null);
            }
        }

        return $this;
    }
}
