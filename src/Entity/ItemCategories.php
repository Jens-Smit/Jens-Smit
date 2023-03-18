<?php

namespace App\Entity;

use App\Repository\ItemCategoriesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ItemCategoriesRepository::class)]
class ItemCategories
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $booktime = null;

    #[ORM\OneToMany(mappedBy: 'Category', targetEntity: RentItems::class, orphanRemoval: true)]
    private Collection $rentItems;

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

    public function getBooktime(): ?int
    {
        return $this->booktime;
    }

    public function setBooktime(int $booktime): self
    {
        $this->booktime = $booktime;

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
            $rentItem->setCategory($this);
        }

        return $this;
    }

    public function removeRentItem(RentItems $rentItem): self
    {
        if ($this->rentItems->removeElement($rentItem)) {
            // set the owning side to null (unless already changed)
            if ($rentItem->getCategory() === $this) {
                $rentItem->setCategory(null);
            }
        }

        return $this;
    }
}
