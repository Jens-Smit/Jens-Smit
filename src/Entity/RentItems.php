<?php

namespace App\Entity;

use App\Repository\RentItemsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RentItemsRepository::class)]
class RentItems
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'RentItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Objekt $objekt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?int $pax = null;

    #[ORM\Column(nullable: true)]
    private ?bool $status = null;

    #[ORM\OneToMany(mappedBy: 'item', targetEntity: Reservation::class, orphanRemoval: true)]
    private Collection $reservations;

    #[ORM\ManyToOne(inversedBy: 'rentItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ItemCategories $Category = null;

    #[ORM\Column(nullable: true)]
    private ?int $usetime = null;

    #[ORM\Column(nullable: true)]
    private array $position = [];
    
    #[ORM\Column(nullable: true)]
    private array $size = [];

    #[ORM\ManyToOne(inversedBy: 'rentItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Area $area = null;

    public function __construct()
    {
        
        $this->reservations = new ArrayCollection();
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

    public function getObjekt(): ?Objekt
    {
        return $this->objekt;
    }

    public function setObjekt(?Objekt $objekt): self
    {
        $this->objekt = $objekt;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPax(): ?int
    {
        return $this->pax;
    }

    public function setPax(?int $pax): self
    {
        $this->pax = $pax;

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(?bool $status): self
    {
        $this->status = $status;

        return $this;
    }
   
    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setItem($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getItem() === $this) {
                $reservation->setItem(null);
            }
        }

        return $this;
    }
    public function __toString()
    {
        return $this->name;
    }

    public function getCategory(): ?ItemCategories
    {
        return $this->Category;
    }

    public function setCategory(?ItemCategories $Category): self
    {
        $this->Category = $Category;

        return $this;
    }

    public function getUsetime(): ?int
    {
        return $this->usetime;
    }

    public function setUsetime(?int $usetime): self
    {
        $this->usetime = $usetime;

        return $this;
    }

    public function getPosition(): array
    {
        return $this->position;
    }

    public function setPosition(?array $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getArea(): ?Area
    {
        return $this->area;
    }

    public function setArea(?Area $area): self
    {
        $this->area = $area;

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
