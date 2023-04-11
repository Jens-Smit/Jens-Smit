<?php

namespace App\Entity;

use App\Repository\ObjektRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ObjektRepository::class)]
class Objekt
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'objekt', targetEntity: User::class)]
    private Collection $users;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(length: 255)]
    private ?string $ort = null;

    #[ORM\Column(length: 255)]
    private ?string $plz = null;

    #[ORM\Column(length: 255)]
    private ?string $main_mail = null;

    #[ORM\Column(length: 255)]
    private ?string $website = null;

    #[ORM\Column(length: 255)]
    private ?string $telefon = null;

    #[ORM\Column(length: 255)]
    private ?string $fax = null;

    #[ORM\Column(length: 255)]
    private ?string $bestellung_mail = null;

    #[ORM\Column(length: 255)]
    private ?string $fibi_mail = null;

    #[ORM\Column(length: 255)]
    private ?string $ust_id = null;

    #[ORM\Column(length: 255)]
    private ?string $Handelsregister = null;

    #[ORM\Column(length: 255)]
    private ?string $Amtsgericht = null;

    #[ORM\ManyToOne(inversedBy: 'objekts')]
    private ?Company $company = null;

    #[ORM\OneToMany(mappedBy: 'objekt', targetEntity: RentItems::class)]
    private Collection $rentItems;
    #[ORM\ManyToOne(inversedBy: 'objekt')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ObjektCategories $categories = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $bild = null;

    #[ORM\OneToMany(mappedBy: 'objekt', targetEntity: OpeningTime::class)]
    private Collection $openingTimes;

    #[ORM\OneToMany(mappedBy: 'objket', targetEntity: SpecialOpeningTime::class)]
    private Collection $specialOpeningTimes;

    #[ORM\Column(nullable: true)]
    private ?int $staytime = null;

    #[ORM\OneToMany(mappedBy: 'objekt', targetEntity: Area::class, orphanRemoval: true)]
    private Collection $areas;

    #[ORM\OneToMany(mappedBy: 'objekt', targetEntity: Vertrag::class, orphanRemoval: true)]
    private Collection $vertrags;

  
    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->rentItems = new ArrayCollection();
        $this->openingTimes = new ArrayCollection();
        $this->specialOpeningTimes = new ArrayCollection();
        $this->areas = new ArrayCollection();
        $this->vertrags = new ArrayCollection();
       
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
            $user->setObjekt($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getObjekt() === $this) {
                $user->setObjekt(null);
            }
        }

        return $this;
    }
    public function __toString()
    {
        return $this->name;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getOrt(): ?string
    {
        return $this->ort;
    }

    public function setOrt(string $ort): self
    {
        $this->ort = $ort;

        return $this;
    }

    public function getPlz(): ?string
    {
        return $this->plz;
    }

    public function setPlz(string $plz): self
    {
        $this->plz = $plz;

        return $this;
    }

    public function getMainMail(): ?string
    {
        return $this->main_mail;
    }

    public function setMainMail(string $main_mail): self
    {
        $this->main_mail = $main_mail;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(string $website): self
    {
        $this->website = $website;

        return $this;
    }

    public function getTelefon(): ?string
    {
        return $this->telefon;
    }

    public function setTelefon(string $telefon): self
    {
        $this->telefon = $telefon;

        return $this;
    }

    public function getFax(): ?string
    {
        return $this->fax;
    }

    public function setFax(string $fax): self
    {
        $this->fax = $fax;

        return $this;
    }

    public function getBestellungMail(): ?string
    {
        return $this->bestellung_mail;
    }

    public function setBestellungMail(string $bestellung_mail): self
    {
        $this->bestellung_mail = $bestellung_mail;

        return $this;
    }

    public function getFibiMail(): ?string
    {
        return $this->fibi_mail;
    }

    public function setFibiMail(string $fibi_mail): self
    {
        $this->fibi_mail = $fibi_mail;

        return $this;
    }

    public function getUstId(): ?string
    {
        return $this->ust_id;
    }

    public function setUstId(string $ust_id): self
    {
        $this->ust_id = $ust_id;

        return $this;
    }

    public function getHandelsregister(): ?string
    {
        return $this->Handelsregister;
    }

    public function setHandelsregister(string $Handelsregister): self
    {
        $this->Handelsregister = $Handelsregister;

        return $this;
    }

    public function getAmtsgericht(): ?string
    {
        return $this->Amtsgericht;
    }

    public function setAmtsgericht(string $Amtsgericht): self
    {
        $this->Amtsgericht = $Amtsgericht;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

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
            $rentItem->setObjekt($this);
        }

        return $this;
    }

    public function removeRentItem(RentItems $rentItem): self
    {
        if ($this->rentItems->removeElement($rentItem)) {
            // set the owning side to null (unless already changed)
            if ($rentItem->getObjekt() === $this) {
                $rentItem->setObjekt(null);
            }
        }

        return $this;
    }

    public function getCategories(): ?ObjektCategories
    {
        return $this->categories;
    }

    public function setCategories(?ObjektCategories $categories): self
    {
        $this->categories = $categories;

        return $this;
    }

    public function getBild(): ?string
    {
        return $this->bild;
    }

    public function setBild(?string $bild): self
    {
        $this->bild = $bild;

        return $this;
    }

    /**
     * @return Collection<int, OpeningTime>
     */
    public function getOpeningTimes(): Collection
    {
        return $this->openingTimes;
    }

    public function addOpeningTime(OpeningTime $openingTime): self
    {
        if (!$this->openingTimes->contains($openingTime)) {
            $this->openingTimes->add($openingTime);
            $openingTime->setObjekt($this);
        }

        return $this;
    }

    public function removeOpeningTime(OpeningTime $openingTime): self
    {
        if ($this->openingTimes->removeElement($openingTime)) {
            // set the owning side to null (unless already changed)
            if ($openingTime->getObjekt() === $this) {
                $openingTime->setObjekt(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SpecialOpeningTime>
     */
    public function getSpecialOpeningTimes(): Collection
    {
        return $this->specialOpeningTimes;
    }

    public function addSpecialOpeningTime(SpecialOpeningTime $specialOpeningTime): self
    {
        if (!$this->specialOpeningTimes->contains($specialOpeningTime)) {
            $this->specialOpeningTimes->add($specialOpeningTime);
            $specialOpeningTime->setObjket($this);
        }

        return $this;
    }

    public function removeSpecialOpeningTime(SpecialOpeningTime $specialOpeningTime): self
    {
        if ($this->specialOpeningTimes->removeElement($specialOpeningTime)) {
            // set the owning side to null (unless already changed)
            if ($specialOpeningTime->getObjket() === $this) {
                $specialOpeningTime->setObjket(null);
            }
        }

        return $this;
    }

    public function getStaytime(): ?int
    {
        return $this->staytime;
    }

    public function setStaytime(?int $staytime): self
    {
        $this->staytime = $staytime;

        return $this;
    }

    /**
     * @return Collection<int, Area>
     */
    public function getAreas(): Collection
    {
        return $this->areas;
    }

    public function addArea(Area $area): self
    {
        if (!$this->areas->contains($area)) {
            $this->areas->add($area);
            $area->setObjekt($this);
        }

        return $this;
    }

    public function removeArea(Area $area): self
    {
        if ($this->areas->removeElement($area)) {
            // set the owning side to null (unless already changed)
            if ($area->getObjekt() === $this) {
                $area->setObjekt(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Vertrag>
     */
    public function getVertrags(): Collection
    {
        return $this->vertrags;
    }

    public function addVertrag(Vertrag $vertrag): self
    {
        if (!$this->vertrags->contains($vertrag)) {
            $this->vertrags->add($vertrag);
            $vertrag->setObjekt($this);
        }

        return $this;
    }

    public function removeVertrag(Vertrag $vertrag): self
    {
        if ($this->vertrags->removeElement($vertrag)) {
            // set the owning side to null (unless already changed)
            if ($vertrag->getObjekt() === $this) {
                $vertrag->setObjekt(null);
            }
        }

        return $this;
    }


   
}
