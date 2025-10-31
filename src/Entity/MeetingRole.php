<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

#[ORM\Entity]
#[ORM\Table(name: 'tencent_meeting_meeting_role', options: ['comment' => '会议角色关联表'])]
class MeetingRole implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private int $id = 0;

    #[ORM\ManyToOne(targetEntity: Meeting::class)]
    #[ORM\JoinColumn(name: 'meeting_id', referencedColumnName: 'id', nullable: false)]
    private Meeting $meeting;

    #[ORM\ManyToOne(targetEntity: Role::class, inversedBy: 'meetingRoles')]
    #[ORM\JoinColumn(name: 'role_id', referencedColumnName: 'id', nullable: true)]
    private ?Role $role;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '用户ID'])]
    #[Assert\Length(max: 255)]
    private ?string $userId = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '分配时间'])]
    #[Assert\DateTime]
    private ?\DateTimeImmutable $assignmentTime = null;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '状态'])]
    #[Assert\Length(max: 50)]
    #[Assert\Choice(choices: ['active', 'revoked'])]
    private string $status = 'active';

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '分配者'])]
    #[Assert\Length(max: 255)]
    private ?string $assignedBy = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注'])]
    #[Assert\Length(max: 65535)]
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

    public function getMeeting(): Meeting
    {
        return $this->meeting;
    }

    public function setMeeting(Meeting $meeting): void
    {
        $this->meeting = $meeting;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): void
    {
        $this->role = $role;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(?string $userId): void
    {
        $this->userId = $userId;
    }

    public function getAssignmentTime(): ?\DateTimeImmutable
    {
        return $this->assignmentTime;
    }

    public function setAssignmentTime(?\DateTimeImmutable $assignmentTime): void
    {
        $this->assignmentTime = $assignmentTime;
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
        $roleName = isset($this->role) ? $this->role->getName() : 'unknown';

        return sprintf(
            'MeetingRole[id=%d, role=%s, status=%s]',
            $this->id,
            $roleName,
            $this->status
        );
    }
}
