<?php

namespace App\Entity;

use App\Repository\ArbeitsbereicheRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArbeitsbereicheRepository::class)]
class Arbeitsbereiche
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Bezeichnung = null;

    #[ORM\ManyToOne(inversedBy: 'arbeitsbereiche')]
    private ?Objekt $objekt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBezeichnung(): ?string
    {
        return $this->Bezeichnung;
    }

    public function setBezeichnung(string $Bezeichnung): self
    {
        $this->Bezeichnung = $Bezeichnung;

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
}
