<?php

namespace App\Entity;

use App\Repository\FehlzeitenRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FehlzeitenRepository::class)]
class Fehlzeiten
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $bezeichnung = null;

    #[ORM\OneToMany(mappedBy: 'status', targetEntity: Dienste::class)]
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
            $dienste->setStatus($this);
        }

        return $this;
    }

    public function removeDienste(Dienste $dienste): self
    {
        if ($this->dienste->removeElement($dienste)) {
            // set the owning side to null (unless already changed)
            if ($dienste->getStatus() === $this) {
                $dienste->setStatus(null);
            }
        }

        return $this;
    }
}
