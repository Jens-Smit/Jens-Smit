<?php

namespace App\Entity;

use App\Repository\AreaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AreaRepository::class)]
class Area
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $map = null;
    #[ORM\Column(nullable: true)]
    private array $size = [];
    
    #[ORM\OneToMany(mappedBy: 'area', targetEntity: RentItems::class, orphanRemoval: true)]
    private Collection $rentItems;

    #[ORM\ManyToOne(targetEntity: Objekt::class, inversedBy: 'areas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Objekt $objekt = null;

    private ?int $objektId = null;
    public function getObjektId(): ?int
    {
        return $this->objektId;
    }
    public function __construct()
    {
        $this->rentItems = new ArrayCollection();
    }
    public function __toString()
    {
        return $this->name;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getMap(): ?string
    {
        return $this->map;
    }

    public function setMap(?string $map): self
    {
        $this->map = $map;

        return $this;
    }

    /**
     * @return Collection<int, RentItems>
     */
    public function getRentItems(): Collection
    {
        return $this->rentItems;
    }

    public function addRentItem(RentItems $rentItem): self
    {
        if (!$this->rentItems->contains($rentItem)) {
            $this->rentItems->add($rentItem);
            $rentItem->setArea($this);
        }

        return $this;
    }

    public function removeRentItem(RentItems $rentItem): self
    {
        if ($this->rentItems->removeElement($rentItem)) {
            // set the owning side to null (unless already changed)
            if ($rentItem->getArea() === $this) {
                $rentItem->setArea(null);
            }
        }

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

    public function getSize(): array
    {
        return $this->size;
    }

    public function setSize(?array $size): self
    {
        $this->size = $size;

        return $this;
    }
}
