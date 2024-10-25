<?php

namespace App\Entity;

use App\Repository\ObjektCategoriesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ObjektCategoriesRepository::class)]
class ObjektCategories
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'categories', targetEntity: Objekt::class)]
    private Collection $objekts;

    #[ORM\OneToMany(mappedBy: 'ObjektCategories', targetEntity: ObjektSubCategories::class)]
    private Collection $objektSubCategories;

    public function __construct()
    {
        
        $this->objekts = new ArrayCollection();
        $this->objektSubCategories = new ArrayCollection();
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

    /**
     * @return Collection<int, Objekt>
     */
    public function getObjekts(): Collection
    {
        return $this->objkets;
    }

    public function addObjekt(Objekt $objekt): self
    {
        if (!$this->objkets->contains($objekt)) {
            $this->objkets->add($objekt);
            $objekt->setCategories($this);
        }

        return $this;
    }
    public function __toString()
    {
        return $this->name;
    }


    public function removeObjekt(Objekt $objekt): self
    {
        if ($this->objekts->removeElement($objekt)) {
            // set the owning side to null (unless already changed)
            if ($objekt->getCategories() === $this) {
                $objekt->setCategories(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ObjektSubCategories>
     */
    public function getObjektSubCategories(): Collection
    {
        return $this->objektSubCategories;
    }

    public function addObjektSubCategory(ObjektSubCategories $objektSubCategory): self
    {
        if (!$this->objektSubCategories->contains($objektSubCategory)) {
            $this->objektSubCategories->add($objektSubCategory);
            $objektSubCategory->setObjektCategories($this);
        }

        return $this;
    }

    public function removeObjektSubCategory(ObjektSubCategories $objektSubCategory): self
    {
        if ($this->objektSubCategories->removeElement($objektSubCategory)) {
            // set the owning side to null (unless already changed)
            if ($objektSubCategory->getObjektCategories() === $this) {
                $objektSubCategory->setObjektCategories(null);
            }
        }

        return $this;
    }

}   