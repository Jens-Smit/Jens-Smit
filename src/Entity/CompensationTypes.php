<?php

namespace App\Entity;

use App\Repository\CompensationTypesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompensationTypesRepository::class)]
class CompensationTypes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'CompensationTypes', targetEntity: ContractData::class, orphanRemoval: true)]
    private Collection $contractData;

    public function __construct()
    {
        $this->contractData = new ArrayCollection();
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
     * @return Collection<int, ContractData>
     */
    public function getContractData(): Collection
    {
        return $this->contractData;
    }

    public function addContractData(ContractData $contractData): self
    {
        if (!$this->contractData->contains($contractData)) {
            $this->contractData->add($contractData);
            $contractData->setCompensationTypes($this);
        }

        return $this;
    }

    public function removeContractData(ContractData $contractData): self
    {
        if ($this->contractData->removeElement($contractData)) {
            // set the owning side to null (unless already changed)
            if ($contractData->getCompensationTypes() === $this) {
                $contractData->setCompensationTypes(null);
            }
        }

        return $this;
    }
}
