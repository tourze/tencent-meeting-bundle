<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

#[ORM\Entity]
#[ORM\Table(name: 'tencent_meeting_user', options: ['comment' => '企业用户表'])]
class User implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private int $id = 0;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true, options: ['comment' => '用户ID'])]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    private string $userid;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '用户UUID'])]
    #[Assert\Length(max: 255)]
    private ?string $uuid = null;

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '用户姓名'])]
    #[Assert\Length(max: 100)]
    #[Assert\NotBlank]
    private string $username;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '邮箱地址'])]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    private ?string $email = null;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: true, options: ['comment' => '手机号码'])]
    #[Assert\Length(max: 20)]
    private ?string $phone = null;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '用户类型'])]
    #[Assert\Length(max: 50)]
    #[Assert\Choice(choices: ['enterprise', 'personal'])]
    private string $userType = 'enterprise';

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '用户状态'])]
    #[Assert\Length(max: 50)]
    #[Assert\Choice(choices: ['active', 'inactive', 'disabled'])]
    private string $status = 'active';

    #[ORM\ManyToOne(targetEntity: Department::class, inversedBy: 'users')]
    #[ORM\JoinColumn(name: 'department_id', referencedColumnName: 'id')]
    private ?Department $department = null;

    #[ORM\ManyToOne(targetEntity: TencentMeetingConfig::class)]
    #[ORM\JoinColumn(name: 'config_id', referencedColumnName: 'id')]
    private TencentMeetingConfig $config;

    /**
     * @var Collection<int, UserRole>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserRole::class)]
    private Collection $userRoles;

    public function __construct()
    {
        $this->userRoles = new ArrayCollection();
    }

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserid(): string
    {
        return $this->userid;
    }

    public function setUserid(string $userid): void
    {
        $this->userid = $userid;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(?string $uuid): void
    {
        $this->uuid = $uuid;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function getUserType(): string
    {
        return $this->userType;
    }

    public function setUserType(string $userType): void
    {
        $this->userType = $userType;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department): void
    {
        $this->department = $department;
    }

    public function getConfig(): TencentMeetingConfig
    {
        return $this->config;
    }

    public function setConfig(TencentMeetingConfig $config): void
    {
        $this->config = $config;
    }

    /**
     * @return Collection<int, UserRole>
     */
    public function getUserRoles(): Collection
    {
        return $this->userRoles;
    }

    public function addUserRole(UserRole $userRole): void
    {
        if (!$this->userRoles->contains($userRole)) {
            $this->userRoles->add($userRole);
            $userRole->setUser($this);
        }
    }

    public function removeUserRole(UserRole $userRole): void
    {
        if ($this->userRoles->removeElement($userRole)) {
            if ($userRole->getUser() === $this) {
                $userRole->setUser(null);
            }
        }
    }

    public function __toString(): string
    {
        return $this->username ?? 'User#' . $this->id;
    }
}
