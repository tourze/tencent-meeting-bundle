<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

#[ORM\Entity]
#[ORM\Table(name: 'tencent_meeting_meeting_background', options: ['comment' => '会议背景关联表'])]
class MeetingBackground implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private int $id = 0;

    #[ORM\ManyToOne(targetEntity: Meeting::class)]
    #[ORM\JoinColumn(name: 'meeting_id', referencedColumnName: 'id', nullable: false)]
    private Meeting $meeting;

    #[ORM\ManyToOne(targetEntity: Background::class, inversedBy: 'meetingBackgrounds')]
    #[ORM\JoinColumn(name: 'background_id', referencedColumnName: 'id', nullable: false)]
    private Background $background;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '应用时间'])]
    #[Assert\GreaterThanOrEqual(propertyPath: 'meeting.startTime', message: '应用时间不能早于会议开始时间')]
    private ?\DateTimeImmutable $applicationTime = null;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '状态'])]
    #[Assert\Length(max: 50)]
    #[Assert\Choice(choices: ['active', 'inactive'])]
    private string $status = 'active';

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '应用者'])]
    #[Assert\Length(max: 255)]
    private ?string $appliedBy = null;

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '自定义配置'])]
    #[Assert\Type(type: 'array')]
    private ?array $customConfig = null;

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

    public function getBackground(): Background
    {
        return $this->background;
    }

    public function setBackground(Background $background): void
    {
        $this->background = $background;
    }

    public function getApplicationTime(): ?\DateTimeImmutable
    {
        return $this->applicationTime;
    }

    public function setApplicationTime(?\DateTimeImmutable $applicationTime): void
    {
        $this->applicationTime = $applicationTime;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getAppliedBy(): ?string
    {
        return $this->appliedBy;
    }

    public function setAppliedBy(?string $appliedBy): void
    {
        $this->appliedBy = $appliedBy;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getCustomConfig(): ?array
    {
        return $this->customConfig;
    }

    /**
     * @param array<string, mixed>|null $customConfig
     */
    public function setCustomConfig(?array $customConfig): void
    {
        $this->customConfig = $customConfig;
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
        $backgroundName = isset($this->background) ? $this->background->getName() : 'unknown';

        return sprintf(
            'MeetingBackground[id=%s, background=%s]',
            0 === $this->id ? 'new' : $this->id,
            $backgroundName
        );
    }
}
