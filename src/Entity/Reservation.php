<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use ApiPlatform\Metadata\ApiResource;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;




#[ApiResource]
#[ORM\Entity(repositoryClass: ReservationRepository::class)]

class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    
    private ?string $user = null;

    /**
     * @var string A "Y-m-d H:i:s" formatted value
     */

    #[Assert\DateTime()]
    #[ORM\Column (type: 'datetime')]
  
    private  $kommen = null;
    #[Assert\DateTime()]
    #[ORM\Column(type: 'datetime')]
    #[Groups(['Reservation:list', 'conference:item'])]
    private  $gehen = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
   
    private ?RentItems $item = null;

    #[ORM\Column(length: 255)]
   
    private ?string $pax = null;

    #[ORM\Column(length: 255)]
   
    private ?string $fon = null;

    #[ORM\Column(length: 255)]
 
    private ?string $mail = null;
    
    #[ORM\Column(nullable: true)]

    private ?int $points = null;

    #[ORM\Column(length: 32, nullable: true)]
  
    private ?string $status = null;

    #[ORM\Column(length: 32, nullable: true)]
  
    private ?string $aktiv = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function setUser(string $user): self
    {
        $this->user = $user;

        return $this;
    }
   

    public function getItem(): ?RentItems
    {
        return $this->item;
    }

    public function setItem(?RentItems $item): self
    {
        $this->item = $item;

        return $this;
    }

    

    public function __toString()
    {
        return $this->id;
    }
    public function __construct()
    {
        return $this->kommen;
    }
   

  

    public function getPax(): ?string
    {
        return $this->pax;
    }

    public function setPax(string $pax): self
    {
        $this->pax = $pax;

        return $this;
    }

    public function getFon(): ?string
    {
        return $this->fon;
    }

    public function setFon(string $fon): self
    {
        $this->fon = $fon;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(?int $points): self
    {
        $this->points = $points;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getAktiv(): ?string
    {
        return $this->aktiv;
    }

    public function setAktiv(?string $aktiv): self
    {
        $this->aktiv = $aktiv;

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

    public function getEnde(): ?\DateTimeInterface
    {
        return $this->ende;
    }

    public function setEnde(\DateTimeInterface $ende): self
    {
        $this->ende = $ende;

        return $this;
    }

    public function getKommen(): ?\DateTimeInterface
    {
        return $this->kommen;
    }

    public function setKommen(\DateTimeInterface $kommen): self
    {
        $this->kommen = $kommen;

        return $this;
    }

    public function getGehen(): ?\DateTimeInterface
    {
        return $this->gehen;
    }

    public function setGehen(\DateTimeInterface $gehen): self
    {
        $this->gehen = $gehen;

        return $this;
    }
}
