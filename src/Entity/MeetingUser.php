<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

#[ORM\Entity]
#[ORM\Table(name: 'tencent_meeting_meeting_user', options: ['comment' => '会议参会用户表'])]
class MeetingUser implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private int $id = 0;

    #[ORM\ManyToOne(targetEntity: Meeting::class, inversedBy: 'attendees')]
    #[ORM\JoinColumn(name: 'meeting_id', referencedColumnName: 'id', nullable: false)]
    private Meeting $meeting;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $user;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '用户角色'])]
    #[Assert\Length(max: 50)]
    #[Assert\Choice(choices: ['host', 'cohost', 'attendee', 'audience'])]
    private string $role = 'attendee';

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '参会状态'])]
    #[Assert\Length(max: 50)]
    #[Assert\Choice(choices: ['invited', 'joined', 'left', 'absent'])]
    private string $attendeeStatus = 'invited';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '入会时间'])]
    #[Assert\DateTime]
    private ?\DateTimeImmutable $joinTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '离会时间'])]
    #[Assert\DateTime]
    private ?\DateTimeImmutable $leaveTime = null;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '参会时长（秒）'])]
    #[Assert\PositiveOrZero]
    private int $attendDuration = 0;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '设备信息'])]
    #[Assert\Length(max: 255)]
    private ?string $deviceInfo = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => '网络类型'])]
    #[Assert\Length(max: 50)]
    private ?string $networkType = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否开启摄像头'])]
    #[Assert\Type(type: 'bool')]
    #[Assert\NotNull]
    private bool $cameraOn = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否开启麦克风'])]
    #[Assert\Type(type: 'bool')]
    #[Assert\NotNull]
    private bool $micOn = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否共享屏幕'])]
    #[Assert\Type(type: 'bool')]
    #[Assert\NotNull]
    private bool $screenShared = false;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '参会备注'])]
    #[Assert\Length(max: 65535)]
    private ?string $remark = null;

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

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function getAttendeeStatus(): string
    {
        return $this->attendeeStatus;
    }

    public function setAttendeeStatus(string $attendeeStatus): void
    {
        $this->attendeeStatus = $attendeeStatus;
    }

    public function getJoinTime(): ?\DateTimeImmutable
    {
        return $this->joinTime;
    }

    public function setJoinTime(?\DateTimeImmutable $joinTime): void
    {
        $this->joinTime = $joinTime;
    }

    public function getLeaveTime(): ?\DateTimeImmutable
    {
        return $this->leaveTime;
    }

    public function setLeaveTime(?\DateTimeImmutable $leaveTime): void
    {
        $this->leaveTime = $leaveTime;
    }

    public function getAttendDuration(): int
    {
        return $this->attendDuration;
    }

    public function setAttendDuration(int $attendDuration): void
    {
        $this->attendDuration = $attendDuration;
    }

    public function getDeviceInfo(): ?string
    {
        return $this->deviceInfo;
    }

    public function setDeviceInfo(?string $deviceInfo): void
    {
        $this->deviceInfo = $deviceInfo;
    }

    public function getNetworkType(): ?string
    {
        return $this->networkType;
    }

    public function setNetworkType(?string $networkType): void
    {
        $this->networkType = $networkType;
    }

    public function isCameraOn(): bool
    {
        return $this->cameraOn;
    }

    public function setCameraOn(bool $cameraOn): void
    {
        $this->cameraOn = $cameraOn;
    }

    public function isMicOn(): bool
    {
        return $this->micOn;
    }

    public function setMicOn(bool $micOn): void
    {
        $this->micOn = $micOn;
    }

    public function isScreenShared(): bool
    {
        return $this->screenShared;
    }

    public function setScreenShared(bool $screenShared): void
    {
        $this->screenShared = $screenShared;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): void
    {
        $this->remark = $remark;
    }

    public function __toString(): string
    {
        $userName = isset($this->user) ? $this->user->getUserid() : 'unknown';

        return sprintf(
            'MeetingUser[id=%d, user=%s, role=%s]',
            $this->id,
            $userName,
            $this->role
        );
    }
}
