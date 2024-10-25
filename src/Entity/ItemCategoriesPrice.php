<?php

namespace App\Entity;

use App\Repository\ItemCategoriesPriceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ItemCategoriesPriceRepository::class)]
class ItemCategoriesPrice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'itemCategoriesPrices')]
    private ?ItemCategories $ItemCategory = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $start = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $end = null;

    #[ORM\Column(length: 255)]
    private ?string $price = null;

    #[ORM\OneToMany(mappedBy: 'itemCategoriePrice', targetEntity: Discount::class, orphanRemoval: true)]
    private Collection $discounts;

    public function __construct()
    {
        $this->discounts = new ArrayCollection();
    }
    public function __toString(): string
    {
        // Rückgabe eines Strings, der diese Entität repräsentiert.
        // Sie können hier jedes Feld oder eine Kombination von Feldern verwenden.
        return $this->start->format('Y-m-d') . '-' . $this->end->format('Y-m-d') . ' -> ' . $this->price . '€';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItemCategory(): ?ItemCategories
    {
        return $this->ItemCategory;
    }

    public function setItemCategory(?ItemCategories $ItemCategory): self
    {
        $this->ItemCategory = $ItemCategory;

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

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return Collection<int, Discount>
     */
    public function getDiscounts(): Collection
    {
        return $this->discounts;
    }

    public function addDiscount(Discount $discount): self
    {
        if (!$this->discounts->contains($discount)) {
            $this->discounts->add($discount);
            $discount->setItemCategoriePrice($this);
        }

        return $this;
    }

    public function removeDiscount(Discount $discount): self
    {
        if ($this->discounts->removeElement($discount)) {
            // set the owning side to null (unless already changed)
            if ($discount->getItemCategoriePrice() === $this) {
                $discount->setItemCategoriePrice(null);
            }
        }

        return $this;
    }
}
