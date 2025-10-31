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
#[ORM\Table(name: 'tencent_meeting_role', options: ['comment' => '角色信息表'])]
class Role implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private int $id = 0;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true, options: ['comment' => '角色ID'])]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    private string $roleId;

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '角色名称'])]
    #[Assert\Length(max: 100)]
    #[Assert\NotBlank]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 500, nullable: true, options: ['comment' => '角色描述'])]
    #[Assert\Length(max: 500)]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '角色类型'])]
    #[Assert\Length(max: 50)]
    #[Assert\Choice(choices: ['system', 'custom', 'meeting', 'department'])]
    private string $roleType = 'custom';

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '角色状态'])]
    #[Assert\Length(max: 50)]
    #[Assert\Choice(choices: ['active', 'inactive', 'deleted'])]
    private string $status = 'active';

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '排序权重'])]
    #[Assert\PositiveOrZero]
    private int $orderWeight = 0;

    /** @var array<string, string|int|bool|float|null>|null */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '权限列表'])]
    #[Assert\Type(type: 'array')]
    private ?array $permissions = null;

    /** @var array<string, string|int|bool|float|null>|null */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '角色属性'])]
    #[Assert\Type(type: 'array')]
    private ?array $attributes = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '父角色ID'])]
    #[Assert\Length(max: 255)]
    private ?string $parentRoleId = null;

    /**
     * @var Collection<int, UserRole>
     */
    #[ORM\OneToMany(mappedBy: 'role', targetEntity: UserRole::class)]
    private Collection $userRoles;

    /**
     * @var Collection<int, MeetingRole>
     */
    #[ORM\OneToMany(mappedBy: 'role', targetEntity: MeetingRole::class)]
    private Collection $meetingRoles;

    #[ORM\ManyToOne(targetEntity: TencentMeetingConfig::class)]
    #[ORM\JoinColumn(name: 'config_id', referencedColumnName: 'id')]
    private TencentMeetingConfig $config;

    public function __construct()
    {
        $this->userRoles = new ArrayCollection();
        $this->meetingRoles = new ArrayCollection();
    }

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoleId(): string
    {
        return $this->roleId;
    }

    public function setRoleId(string $roleId): void
    {
        $this->roleId = $roleId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getRoleType(): string
    {
        return $this->roleType;
    }

    public function setRoleType(string $roleType): void
    {
        $this->roleType = $roleType;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getOrderWeight(): int
    {
        return $this->orderWeight;
    }

    public function setOrderWeight(int $orderWeight): void
    {
        $this->orderWeight = $orderWeight;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getPermissions(): ?array
    {
        return $this->permissions;
    }

    /**
     * @param array<string, string|int|bool|float|null>|null $permissions
     */
    public function setPermissions(?array $permissions): void
    {
        $this->permissions = $permissions;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getAttributes(): ?array
    {
        return $this->attributes;
    }

    /**
     * @param array<string, string|int|bool|float|null>|null $attributes
     */
    public function setAttributes(?array $attributes): void
    {
        $this->attributes = $attributes;
    }

    public function getParentRoleId(): ?string
    {
        return $this->parentRoleId;
    }

    public function setParentRoleId(?string $parentRoleId): void
    {
        $this->parentRoleId = $parentRoleId;
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
            $userRole->setRole($this);
        }
    }

    public function removeUserRole(UserRole $userRole): void
    {
        if ($this->userRoles->removeElement($userRole)) {
            if ($userRole->getRole() === $this) {
                $userRole->setRole(null);
            }
        }
    }

    /**
     * @return Collection<int, MeetingRole>
     */
    public function getMeetingRoles(): Collection
    {
        return $this->meetingRoles;
    }

    public function addMeetingRole(MeetingRole $meetingRole): void
    {
        if (!$this->meetingRoles->contains($meetingRole)) {
            $this->meetingRoles->add($meetingRole);
            $meetingRole->setRole($this);
        }
    }

    public function removeMeetingRole(MeetingRole $meetingRole): void
    {
        if ($this->meetingRoles->removeElement($meetingRole)) {
            if ($meetingRole->getRole() === $this) {
                $meetingRole->setRole(null);
            }
        }
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getConfig(): TencentMeetingConfig
    {
        return $this->config;
    }

    public function setConfig(TencentMeetingConfig $config): void
    {
        $this->config = $config;
    }
}
