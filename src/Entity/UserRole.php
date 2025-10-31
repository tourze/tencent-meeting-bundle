<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

#[ORM\Entity]
#[ORM\Table(name: 'tencent_meeting_user_role', options: ['comment' => '用户角色关联表'])]
class UserRole implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private int $id = 0;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'userRoles')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user;

    #[ORM\ManyToOne(targetEntity: Role::class, inversedBy: 'userRoles')]
    #[ORM\JoinColumn(name: 'role_id', referencedColumnName: 'id', nullable: true)]
    private ?Role $role;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '分配时间'])]
    #[Assert\NotNull(message: '分配时间不能为空')]
    private ?\DateTimeImmutable $assignmentTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '过期时间'])]
    #[Assert\GreaterThan(propertyPath: 'assignmentTime', message: '过期时间必须大于分配时间')]
    private ?\DateTimeImmutable $expirationTime = null;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '状态'])]
    #[Assert\Length(max: 50)]
    #[Assert\Choice(choices: ['active', 'expired', 'revoked'])]
    private string $status = 'active';

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '分配者'])]
    #[Assert\Length(max: 255)]
    private ?string $assignedBy = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注'])]
    #[Assert\Length(max: 1000, maxMessage: '备注长度不能超过 {{ limit }} 个字符')]
    private ?string $remark = null;

    #[ORM\ManyToOne(targetEntity: TencentMeetingConfig::class)]
    #[ORM\JoinColumn(name: 'config_id', referencedColumnName: 'id')]
    private TencentMeetingConfig $config;

    public function __construct()
    {
    }

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): void
    {
        $this->role = $role;
    }

    public function getAssignmentTime(): ?\DateTimeImmutable
    {
        return $this->assignmentTime;
    }

    public function setAssignmentTime(?\DateTimeImmutable $assignmentTime): void
    {
        $this->assignmentTime = $assignmentTime;
    }

    public function getExpirationTime(): ?\DateTimeImmutable
    {
        return $this->expirationTime;
    }

    public function setExpirationTime(?\DateTimeImmutable $expirationTime): void
    {
        $this->expirationTime = $expirationTime;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getAssignedBy(): ?string
    {
        return $this->assignedBy;
    }

    public function setAssignedBy(?string $assignedBy): void
    {
        $this->assignedBy = $assignedBy;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): void
    {
        $this->remark = $remark;
    }

    public function getConfig(): TencentMeetingConfig
    {
        return $this->config;
    }

    public function setConfig(TencentMeetingConfig $config): void
    {
        $this->config = $config;
    }

    public function __toString(): string
    {
        return sprintf(
            'UserRole[user=%s, role=%s]',
            $this->user?->getUsername() ?? 'N/A',
            $this->role?->getName() ?? 'N/A'
        );
    }
}
