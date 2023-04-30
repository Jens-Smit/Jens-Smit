<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;
    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?Objekt $objekt = null;

    #[ORM\OneToMany(mappedBy: 'onjekt_admin', targetEntity: Company::class)]
    private Collection $companies;
   
    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?Company $company = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $vorname = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nachname = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $strasse = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $plz = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ort = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $land = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telefon = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Steuernummer = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Rentenversicherungsnummer = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $IBAN = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Krankenkasse = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserDokumente::class, orphanRemoval: true)]
    private Collection $userDokumentes;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ContractData::class, orphanRemoval: true)]
    private Collection $contractData;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Dienste::class)]
    private Collection $dienste;

    #[ORM\ManyToMany(targetEntity: Dienstplan::class, mappedBy: 'user')]
    private Collection $dienstplans;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Arbeitszeit::class)]
    private Collection $arbeitszeits;

   

  
    public function __construct()
    {
        $this->companies = new ArrayCollection();
        $this->userDokumentes = new ArrayCollection();
        $this->contractData = new ArrayCollection();
        $this->dienste = new ArrayCollection();
        $this->dienstplans = new ArrayCollection();
        $this->arbeitszeits = new ArrayCollection();
    
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_GAST';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getVorname(): ?string
    {
        return $this->vorname;
    }

    public function setVorname(?string $vorname): self
    {
        $this->vorname = $vorname;

        return $this;
    }

    public function getNachname(): ?string
    {
        return $this->nachname;
    }

    public function setNachname(?string $nachname): self
    {
        $this->nachname = $nachname;

        return $this;
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

    public function getStrasse(): ?string
    {
        return $this->strasse;
    }

    public function setStrasse(string $strasse): self
    {
        $this->strasse = $strasse;

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

    public function getOrt(): ?string
    {
        return $this->ort;
    }

    public function setOrt(string $ort): self
    {
        $this->ort = $ort;

        return $this;
    }

    public function getLand(): ?string
    {
        return $this->land;
    }

    public function setLand(string $land): self
    {
        $this->land = $land;

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

    public function getSteuernummer(): ?string
    {
        return $this->Steuernummer;
    }

    public function setSteuernummer(string $Steuernummer): self
    {
        $this->Steuernummer = $Steuernummer;

        return $this;
    }

    public function getRentenversicherungsnummer(): ?string
    {
        return $this->Rentenversicherungsnummer;
    }

    public function setRentenversicherungsnummer(string $Rentenversicherungsnummer): self
    {
        $this->Rentenversicherungsnummer = $Rentenversicherungsnummer;

        return $this;
    }

    public function getIBAN(): ?string
    {
        return $this->IBAN;
    }

    public function setIBAN(string $IBAN): self
    {
        $this->IBAN = $IBAN;

        return $this;
    }

    public function getKrankenkasse(): ?string
    {
        return $this->Krankenkasse;
    }

    public function setKrankenkasse(string $Krankenkasse): self
    {
        $this->Krankenkasse = $Krankenkasse;

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
    public function __toString()
    {
        return $this->email;
        
    }

    /**
     * @return Collection<int, Company>
     */
    public function getCompanie(): ?Objekt
    {
        return $this->companie;
    }

    public function addCompany(Company $company): self
    {
        if (!$this->companies->contains($company)) {
            $this->companies->add($company);
            $company->setOnjektAdmin($this);
        }

        return $this;
    }

    public function removeCompany(Company $company): self
    {
        if ($this->companies->removeElement($company)) {
            // set the owning side to null (unless already changed)
            if ($company->getOnjektAdmin() === $this) {
                $company->setOnjektAdmin(null);
            }
        }

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
     * @return Collection<int, Company>
     */
    public function getCompanies(): Collection
    {
        return $this->companies;
    }

    /**
     * @return Collection<int, UserDokumente>
     */
    public function getUserDokumentes(): Collection
    {
        return $this->userDokumentes;
    }

    public function addUserDokumente(UserDokumente $userDokumente): self
    {
        if (!$this->userDokumentes->contains($userDokumente)) {
            $this->userDokumentes->add($userDokumente);
            $userDokumente->setUser($this);
        }

        return $this;
    }

    public function removeUserDokumente(UserDokumente $userDokumente): self
    {
        if ($this->userDokumentes->removeElement($userDokumente)) {
            // set the owning side to null (unless already changed)
            if ($userDokumente->getUser() === $this) {
                $userDokumente->setUser(null);
            }
        }

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
            $contractData->setUser($this);
        }

        return $this;
    }

    public function removeContractData(ContractData $contractData): self
    {
        if ($this->contractData->removeElement($contractData)) {
            // set the owning side to null (unless already changed)
            if ($contractData->getUser() === $this) {
                $contractData->setUser(null);
            }
        }

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
            $dienste->setUser($this);
        }

        return $this;
    }

    public function removeDienste(Dienste $dienste): self
    {
        if ($this->dienste->removeElement($dienste)) {
            // set the owning side to null (unless already changed)
            if ($dienste->getUser() === $this) {
                $dienste->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Dienstplan>
     */
    public function getDienstplans(): Collection
    {
        return $this->dienstplans;
    }

    public function addDienstplan(Dienstplan $dienstplan): self
    {
        if (!$this->dienstplans->contains($dienstplan)) {
            $this->dienstplans->add($dienstplan);
            $dienstplan->addUser($this);
        }

        return $this;
    }

    public function removeDienstplan(Dienstplan $dienstplan): self
    {
        if ($this->dienstplans->removeElement($dienstplan)) {
            $dienstplan->removeUser($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Arbeitszeit>
     */
    public function getArbeitszeits(): Collection
    {
        return $this->arbeitszeits;
    }

    public function addArbeitszeit(Arbeitszeit $arbeitszeit): self
    {
        if (!$this->arbeitszeits->contains($arbeitszeit)) {
            $this->arbeitszeits->add($arbeitszeit);
            $arbeitszeit->setUser($this);
        }

        return $this;
    }

    public function removeArbeitszeit(Arbeitszeit $arbeitszeit): self
    {
        if ($this->arbeitszeits->removeElement($arbeitszeit)) {
            // set the owning side to null (unless already changed)
            if ($arbeitszeit->getUser() === $this) {
                $arbeitszeit->setUser(null);
            }
        }

        return $this;
    }

   

   
}
