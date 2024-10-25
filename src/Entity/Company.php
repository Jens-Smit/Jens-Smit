<?php

namespace App\Entity;

use App\Repository\CompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;
    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Objekt::class)]
    private $objekts;

    #[ORM\ManyToOne(inversedBy: 'companies')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $onjekt_admin = null;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: User::class)]
    private Collection $users;

    #[ORM\Column(length: 5, nullable: true)]
    private ?string $sign = null;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->objekts = new ArrayCollection();
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

    public function getOnjektAdmin(): ?User
    {
        return $this->onjekt_admin;
    }

    public function setOnjektAdmin(?User $onjekt_admin): self
    {
        $this->onjekt_admin = $onjekt_admin;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setCompany($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getCompany() === $this) {
                $user->setCompany(null);
            }
        }

        return $this;
    }
    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return Collection<int, Objekt>
     */
    public function getObjekts(): Collection
    {
        return $this->objekts;
    }

    public function addObjekt(Objekt $objekt): self
    {
        if (!$this->objekts->contains($objekt)) {
            $this->objekts->add($objekt);
            $objekt->setCompany($this);
        }

        return $this;
    }

    public function removeObjekt(Objekt $objekt): self
    {
        if ($this->objekts->removeElement($objekt)) {
            // set the owning side to null (unless already changed)
            if ($objekt->getCompany() === $this) {
                $objekt->setCompany(null);
            }
        }

        return $this;
    }

    public function getSign(): ?string
    {
        return $this->sign;
    }

    public function setSign(?string $sign): self
    {
        $this->sign = $sign;

        return $this;
    }
}
