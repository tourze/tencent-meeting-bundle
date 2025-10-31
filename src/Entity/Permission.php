<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

#[ORM\Entity]
#[ORM\Table(name: 'tencent_meeting_permission', options: ['comment' => '权限配置表'])]
class Permission implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private int $id = 0;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true, options: ['comment' => '权限ID'])]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    private string $permissionId;

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '权限名称'])]
    #[Assert\Length(max: 100)]
    #[Assert\NotBlank]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 500, nullable: true, options: ['comment' => '权限描述'])]
    #[Assert\Length(max: 500)]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '权限类型'])]
    #[Assert\Length(max: 50)]
    #[Assert\Choice(choices: ['system', 'user', 'meeting', 'recording', 'document', 'room'])]
    private string $permissionType = 'system';

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '权限代码'])]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    private string $permissionCode;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '权限状态'])]
    #[Assert\Length(max: 50)]
    #[Assert\Choice(choices: ['active', 'inactive'])]
    private string $status = 'active';

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '权限配置'])]
    #[Assert\Type(type: 'array')]
    private ?array $permissionConfig = null;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '排序权重'])]
    #[Assert\PositiveOrZero]
    private int $orderWeight = 0;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否为内置权限'])]
    #[Assert\NotNull]
    private bool $isBuiltIn = false;

    #[ORM\ManyToOne(targetEntity: TencentMeetingConfig::class)]
    #[ORM\JoinColumn(name: 'config_id', referencedColumnName: 'id', nullable: true)]
    private ?TencentMeetingConfig $config = null;

    public function __construct()
    {
    }

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPermissionId(): string
    {
        return $this->permissionId;
    }

    public function setPermissionId(string $permissionId): void
    {
        $this->permissionId = $permissionId;
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

    public function getPermissionType(): string
    {
        return $this->permissionType;
    }

    public function setPermissionType(string $permissionType): void
    {
        $this->permissionType = $permissionType;
    }

    public function getPermissionCode(): string
    {
        return $this->permissionCode;
    }

    public function setPermissionCode(string $permissionCode): void
    {
        $this->permissionCode = $permissionCode;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getPermissionConfig(): ?array
    {
        return $this->permissionConfig;
    }

    /**
     * @param array<string, mixed>|null $permissionConfig
     */
    public function setPermissionConfig(?array $permissionConfig): void
    {
        $this->permissionConfig = $permissionConfig;
    }

    public function getOrderWeight(): int
    {
        return $this->orderWeight;
    }

    public function setOrderWeight(int $orderWeight): void
    {
        $this->orderWeight = $orderWeight;
    }

    public function isBuiltIn(): bool
    {
        return $this->isBuiltIn;
    }

    public function setBuiltIn(bool $isBuiltIn): void
    {
        $this->isBuiltIn = $isBuiltIn;
    }

    public function getConfig(): ?TencentMeetingConfig
    {
        return $this->config;
    }

    public function setConfig(?TencentMeetingConfig $config): void
    {
        $this->config = $config;
    }

    public function getConfigEntity(): ?TencentMeetingConfig
    {
        return $this->config;
    }

    public function setConfigEntity(?TencentMeetingConfig $config): void
    {
        $this->config = $config;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
