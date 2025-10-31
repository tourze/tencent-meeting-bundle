<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\TencentMeetingBundle\Enum\MeetingStatus;

#[ORM\Entity]
#[ORM\Table(name: 'tencent_meeting_meeting', options: ['comment' => '腾讯会议信息表'])]
class Meeting implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private int $id = 0;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true, options: ['comment' => '会议ID'])]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    private string $meetingId;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '会议Code'])]
    #[Assert\Length(max: 255)]
    private string $meetingCode;

    #[ORM\Column(type: Types::STRING, length: 500, options: ['comment' => '会议主题'])]
    #[Assert\Length(max: 500)]
    #[Assert\NotBlank]
    private string $subject;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '开始时间'])]
    #[Assert\NotNull]
    private \DateTimeImmutable $startTime;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '结束时间'])]
    #[Assert\Expression(expression: 'this.getEndTime() === null or this.getEndTime() > this.getStartTime()', message: '结束时间必须晚于开始时间')]
    private ?\DateTimeImmutable $endTime = null;

    #[ORM\Column(type: Types::STRING, length: 50, enumType: MeetingStatus::class, options: ['comment' => '会议状态'])]
    #[Assert\Length(max: 50)]
    #[Assert\Choice(choices: ['scheduled', 'in_progress', 'ended', 'cancelled'])]
    private MeetingStatus $status = MeetingStatus::SCHEDULED;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '会议时长（分钟）'])]
    #[Assert\Positive]
    private int $duration;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '主持人用户ID'])]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    private string $userId;

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '时区'])]
    #[Assert\Length(max: 100)]
    private string $timezone = 'Asia/Shanghai';

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '会议链接'])]
    #[Assert\Length(max: 255)]
    #[Assert\Url]
    private ?string $meetingUrl = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '会议密码'])]
    #[Assert\Length(max: 255)]
    private ?string $password = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否已发送提醒'])]
    #[Assert\Type(type: 'bool')]
    private bool $reminderSent = false;

    #[ORM\ManyToOne(targetEntity: TencentMeetingConfig::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'config_id', referencedColumnName: 'id')]
    private TencentMeetingConfig $config;

    /**
     * @var Collection<int, MeetingUser>
     */
    #[ORM\OneToMany(mappedBy: 'meeting', targetEntity: MeetingUser::class)]
    private Collection $attendees;

    /**
     * @var Collection<int, Recording>
     */
    #[ORM\OneToMany(mappedBy: 'meeting', targetEntity: Recording::class)]
    private Collection $recordings;

    /**
     * @var Collection<int, MeetingGuest>
     */
    #[ORM\OneToMany(mappedBy: 'meeting', targetEntity: MeetingGuest::class)]
    private Collection $guests;

    /**
     * @var Collection<int, MeetingVote>
     */
    #[ORM\OneToMany(mappedBy: 'meeting', targetEntity: MeetingVote::class)]
    private Collection $votes;

    /**
     * @var Collection<int, MeetingDocument>
     */
    #[ORM\OneToMany(mappedBy: 'meeting', targetEntity: MeetingDocument::class)]
    private Collection $documents;

    /**
     * @var Collection<int, MeetingRole>
     */
    #[ORM\OneToMany(mappedBy: 'meeting', targetEntity: MeetingRole::class)]
    private Collection $meetingRoles;

    /**
     * @var Collection<int, MeetingLayout>
     */
    #[ORM\OneToMany(mappedBy: 'meeting', targetEntity: MeetingLayout::class)]
    private Collection $meetingLayouts;

    /**
     * @var Collection<int, MeetingBackground>
     */
    #[ORM\OneToMany(mappedBy: 'meeting', targetEntity: MeetingBackground::class)]
    private Collection $meetingBackgrounds;

    public function __construct()
    {
        $this->attendees = new ArrayCollection();
        $this->recordings = new ArrayCollection();
        $this->guests = new ArrayCollection();
        $this->votes = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->meetingRoles = new ArrayCollection();
        $this->meetingLayouts = new ArrayCollection();
        $this->meetingBackgrounds = new ArrayCollection();
    }

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMeetingId(): string
    {
        return $this->meetingId;
    }

    public function setMeetingId(string $meetingId): void
    {
        $this->meetingId = $meetingId;
    }

    public function getMeetingCode(): string
    {
        return $this->meetingCode;
    }

    public function setMeetingCode(string $meetingCode): void
    {
        $this->meetingCode = $meetingCode;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    public function getStartTime(): \DateTimeImmutable
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeImmutable $startTime): void
    {
        $this->startTime = $startTime;
    }

    public function getEndTime(): ?\DateTimeImmutable
    {
        return $this->endTime;
    }

    public function setEndTime(?\DateTimeImmutable $endTime): void
    {
        $this->endTime = $endTime;
    }

    public function getStatus(): MeetingStatus
    {
        return $this->status;
    }

    public function setStatus(MeetingStatus $status): void
    {
        $this->status = $status;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): void
    {
        $this->duration = $duration;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function getTimezone(): string
    {
        return $this->timezone;
    }

    public function setTimezone(string $timezone): void
    {
        $this->timezone = $timezone;
    }

    public function getMeetingUrl(): ?string
    {
        return $this->meetingUrl;
    }

    public function setMeetingUrl(?string $meetingUrl): void
    {
        $this->meetingUrl = $meetingUrl;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    public function isReminderSent(): bool
    {
        return $this->reminderSent;
    }

    public function setReminderSent(bool $reminderSent): void
    {
        $this->reminderSent = $reminderSent;
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
     * @return Collection<int, MeetingUser>
     */
    public function getAttendees(): Collection
    {
        return $this->attendees;
    }

    public function addAttendee(MeetingUser $attendee): void
    {
        if (!$this->attendees->contains($attendee)) {
            $this->attendees->add($attendee);
            $attendee->setMeeting($this);
        }
    }

    public function removeAttendee(MeetingUser $attendee): void
    {
        if ($this->attendees->removeElement($attendee)) {
            // 设置为null需要先修改MeetingUser的setMeeting方法允许null
        }
    }

    /**
     * @return Collection<int, Recording>
     */
    public function getRecordings(): Collection
    {
        return $this->recordings;
    }

    public function addRecording(Recording $recording): void
    {
        if (!$this->recordings->contains($recording)) {
            $this->recordings->add($recording);
            $recording->setMeeting($this);
        }
    }

    public function removeRecording(Recording $recording): void
    {
        if ($this->recordings->removeElement($recording)) {
            // 设置为null需要先修改Recording的setMeeting方法允许null
        }
    }

    /**
     * @return Collection<int, MeetingGuest>
     */
    public function getGuests(): Collection
    {
        return $this->guests;
    }

    public function addGuest(MeetingGuest $guest): void
    {
        if (!$this->guests->contains($guest)) {
            $this->guests->add($guest);
            $guest->setMeeting($this);
        }
    }

    public function removeGuest(MeetingGuest $guest): void
    {
        if ($this->guests->removeElement($guest)) {
            // 设置为null需要先修改MeetingGuest的setMeeting方法允许null
        }
    }

    /**
     * @return Collection<int, MeetingVote>
     */
    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(MeetingVote $vote): void
    {
        if (!$this->votes->contains($vote)) {
            $this->votes->add($vote);
            $vote->setMeeting($this);
        }
    }

    public function removeVote(MeetingVote $vote): void
    {
        if ($this->votes->removeElement($vote)) {
            // 设置为null需要先修改MeetingVote的setMeeting方法允许null
        }
    }

    /**
     * @return Collection<int, MeetingDocument>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(MeetingDocument $document): void
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->setMeeting($this);
        }
    }

    public function removeDocument(MeetingDocument $document): void
    {
        if ($this->documents->removeElement($document)) {
            // 设置为null需要先修改MeetingDocument的setMeeting方法允许null
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
            $meetingRole->setMeeting($this);
        }
    }

    public function removeMeetingRole(MeetingRole $meetingRole): void
    {
        if ($this->meetingRoles->removeElement($meetingRole)) {
            // 设置为null需要先修改MeetingRole的setMeeting方法允许null
        }
    }

    /**
     * @return Collection<int, MeetingLayout>
     */
    public function getMeetingLayouts(): Collection
    {
        return $this->meetingLayouts;
    }

    public function addMeetingLayout(MeetingLayout $meetingLayout): void
    {
        if (!$this->meetingLayouts->contains($meetingLayout)) {
            $this->meetingLayouts->add($meetingLayout);
            $meetingLayout->setMeeting($this);
        }
    }

    public function removeMeetingLayout(MeetingLayout $meetingLayout): void
    {
        if ($this->meetingLayouts->removeElement($meetingLayout)) {
            // 设置为null需要先修改MeetingLayout的setMeeting方法允许null
        }
    }

    /**
     * @return Collection<int, MeetingBackground>
     */
    public function getMeetingBackgrounds(): Collection
    {
        return $this->meetingBackgrounds;
    }

    public function addMeetingBackground(MeetingBackground $meetingBackground): void
    {
        if (!$this->meetingBackgrounds->contains($meetingBackground)) {
            $this->meetingBackgrounds->add($meetingBackground);
            $meetingBackground->setMeeting($this);
        }
    }

    public function removeMeetingBackground(MeetingBackground $meetingBackground): void
    {
        if ($this->meetingBackgrounds->removeElement($meetingBackground)) {
            // 设置为null需要先修改MeetingBackground的setMeeting方法允许null
        }
    }

    public function __toString(): string
    {
        return sprintf('Meeting[id=%s, subject=%s]', 0 === $this->id ? 'new' : $this->id, $this->subject);
    }
}
