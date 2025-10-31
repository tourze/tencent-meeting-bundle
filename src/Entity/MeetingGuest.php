<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

#[ORM\Entity]
#[ORM\Table(name: 'tencent_meeting_guest', options: ['comment' => '会议嘉宾表'])]
class MeetingGuest implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private int $id = 0;

    #[ORM\ManyToOne(targetEntity: Meeting::class, inversedBy: 'guests')]
    #[ORM\JoinColumn(name: 'meeting_id', referencedColumnName: 'id', nullable: false)]
    private Meeting $meeting;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '嘉宾姓名'])]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    private string $guestName;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '嘉宾邮箱'])]
    #[Assert\Length(max: 255)]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: true, options: ['comment' => '嘉宾手机'])]
    #[Assert\Length(max: 20)]
    private ?string $phone = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '嘉宾公司'])]
    #[Assert\Length(max: 255)]
    private ?string $company = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '嘉宾职位'])]
    #[Assert\Length(max: 100)]
    private ?string $position = null;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '嘉宾类型'])]
    #[Assert\Length(max: 50)]
    #[Assert\Choice(choices: ['internal', 'external', 'vip'])]
    private string $guestType = 'external';

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '邀请状态'])]
    #[Assert\Length(max: 50)]
    #[Assert\Choice(choices: ['invited', 'accepted', 'declined', 'tentative'])]
    private string $inviteStatus = 'invited';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '邀请发送时间'])]
    #[Assert\DateTime]
    private ?\DateTimeImmutable $invitationTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '回复时间'])]
    #[Assert\DateTime]
    private ?\DateTimeImmutable $responseTime = null;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '参会状态'])]
    #[Assert\Length(max: 50)]
    #[Assert\Choice(choices: ['expected', 'joined', 'left', 'no_show'])]
    private string $attendanceStatus = 'expected';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '入会时间'])]
    #[Assert\DateTime]
    private ?\DateTimeImmutable $joinTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '离会时间'])]
    #[Assert\DateTime]
    private ?\DateTimeImmutable $leaveTime = null;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '参会时长（秒）'])]
    #[Assert\PositiveOrZero]
    private int $attendDuration = 0;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否需要提醒'])]
    #[Assert\Type(type: 'bool')]
    private bool $needReminder = true;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '嘉宾备注'])]
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

    public function getGuestName(): string
    {
        return $this->guestName;
    }

    public function setGuestName(string $guestName): void
    {
        $this->guestName = $guestName;
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

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(?string $company): void
    {
        $this->company = $company;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(?string $position): void
    {
        $this->position = $position;
    }

    public function getGuestType(): string
    {
        return $this->guestType;
    }

    public function setGuestType(string $guestType): void
    {
        $this->guestType = $guestType;
    }

    public function getInviteStatus(): string
    {
        return $this->inviteStatus;
    }

    public function setInviteStatus(string $inviteStatus): void
    {
        $this->inviteStatus = $inviteStatus;
    }

    public function getInvitationTime(): ?\DateTimeImmutable
    {
        return $this->invitationTime;
    }

    public function setInvitationTime(?\DateTimeImmutable $invitationTime): void
    {
        $this->invitationTime = $invitationTime;
    }

    public function getResponseTime(): ?\DateTimeImmutable
    {
        return $this->responseTime;
    }

    public function setResponseTime(?\DateTimeImmutable $responseTime): void
    {
        $this->responseTime = $responseTime;
    }

    public function getAttendanceStatus(): string
    {
        return $this->attendanceStatus;
    }

    public function setAttendanceStatus(string $attendanceStatus): void
    {
        $this->attendanceStatus = $attendanceStatus;
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

    public function isNeedReminder(): bool
    {
        return $this->needReminder;
    }

    public function setNeedReminder(bool $needReminder): void
    {
        $this->needReminder = $needReminder;
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
        return sprintf('MeetingGuest[id=%s, name=%s]', 0 === $this->id ? 'new' : $this->id, $this->guestName);
    }
}
