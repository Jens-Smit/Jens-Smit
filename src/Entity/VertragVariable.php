<?php

namespace App\Entity;

use App\Repository\VertragVariableRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VertragVariableRepository::class)]
class VertragVariable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 36)]
    private ?string $Name = null;

    #[ORM\Column(length: 255)]
    private ?string $var = null;

    #[ORM\Column(length: 255)]
    private ?string $entity = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $Name): self
    {
        $this->Name = $Name;

        return $this;
    }

    public function getVar(): ?string
    {
        return $this->var;
    }

    public function setVar(string $var): self
    {
        $this->var = $var;

        return $this;
    }

    public function getEntity(): ?string
    {
        return $this->entity;
    }

    public function setEntity(string $entity): self
    {
        $this->entity = $entity;

        return $this;
    }
}
