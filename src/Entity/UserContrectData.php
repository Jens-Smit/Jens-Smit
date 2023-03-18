<?PHP
namespace App\Entity;
use App\Entity\User;
use App\Entity\ContractData;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class UserContrectData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private $user;

    #[ORM\ManyToOne(targetEntity: ContractData::class)]
    #[ORM\JoinColumn(name: 'contract_data_id', referencedColumnName: 'id')]
    private $contractData;

    public function __construct(User $user, ContractData $contractData)
    {
        $this->user = $user;
        $this->contractData = $contractData;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getContractData(): ContractData
    {
        return $this->contractData;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function setContractData(?ContractData $contractData): self
    {
        $this->contractData = $contractData;

        return $this;
    }
    public function __toString()
    {
        return $this->user;
    }
}