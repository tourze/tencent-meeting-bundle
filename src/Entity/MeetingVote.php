<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

#[ORM\Entity]
#[ORM\Table(name: 'tencent_meeting_vote', options: ['comment' => '会议投票表'])]
class MeetingVote implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private int $id = 0;

    #[ORM\ManyToOne(targetEntity: Meeting::class, inversedBy: 'votes')]
    #[ORM\JoinColumn(name: 'meeting_id', referencedColumnName: 'id', nullable: false)]
    private Meeting $meeting;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '投票主题'])]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    private string $subject;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '投票描述'])]
    #[Assert\Length(max: 1000)]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '投票类型'])]
    #[Assert\Length(max: 50)]
    #[Assert\Choice(choices: ['single_choice', 'multiple_choice', 'yes_no'])]
    private string $voteType = 'single_choice';

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '投票状态'])]
    #[Assert\Length(max: 50)]
    #[Assert\Choice(choices: ['draft', 'active', 'closed', 'cancelled'])]
    private string $status = 'draft';

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否匿名投票'])]
    #[Assert\Type(type: 'bool')]
    private bool $anonymous = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否显示结果'])]
    #[Assert\Type(type: 'bool')]
    private bool $showResult = true;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '开始时间'])]
    #[Assert\DateTime]
    private ?\DateTimeImmutable $startTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '结束时间'])]
    #[Assert\DateTime]
    private ?\DateTimeImmutable $endTime = null;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '总投票数'])]
    #[Assert\PositiveOrZero]
    private int $totalVotes = 0;

    /** @var array<int, array<string, int|string>>|null */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '投票选项'])]
    #[Assert\Type(type: 'array')]
    #[Assert\Count(min: 1, minMessage: '投票选项至少需要一个')]
    private ?array $options = null;

    /** @var array<string, string|int|bool|float|null>|null */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '投票结果'])]
    #[Assert\Type(type: 'array')]
    private ?array $results = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => '创建者用户ID'])]
    #[Assert\Length(max: 50)]
    private ?string $creatorUserId = null;

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

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getVoteType(): string
    {
        return $this->voteType;
    }

    public function setVoteType(string $voteType): void
    {
        $this->voteType = $voteType;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function isAnonymous(): bool
    {
        return $this->anonymous;
    }

    public function setAnonymous(bool $anonymous): void
    {
        $this->anonymous = $anonymous;
    }

    public function isShowResult(): bool
    {
        return $this->showResult;
    }

    public function setShowResult(bool $showResult): void
    {
        $this->showResult = $showResult;
    }

    public function getStartTime(): ?\DateTimeImmutable
    {
        return $this->startTime;
    }

    public function setStartTime(?\DateTimeImmutable $startTime): void
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

    public function getTotalVotes(): int
    {
        return $this->totalVotes;
    }

    public function setTotalVotes(int $totalVotes): void
    {
        $this->totalVotes = $totalVotes;
    }

    /**
     * @return array<int, array<string, int|string>>|null
     */
    public function getOptions(): ?array
    {
        return $this->options;
    }

    /**
     * @param array<int, array<string, int|string>>|null $options
     */
    public function setOptions(?array $options): void
    {
        $this->options = $options;
    }

    /**
     * @return array<string, mixed>|null
     */
    /**
     * @return array<string, string|int|bool|float|null>|null
     */
    public function getResults(): ?array
    {
        return $this->results;
    }

    /**
     * @param array<string, string|int|bool|float|null>|null $results
     */
    public function setResults(?array $results): void
    {
        $this->results = $results;
    }

    public function getCreatorUserId(): ?string
    {
        return $this->creatorUserId;
    }

    public function setCreatorUserId(?string $creatorUserId): void
    {
        $this->creatorUserId = $creatorUserId;
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
            'MeetingVote[id=%d, meeting=%s]',
            $this->id,
            $this->meeting->getSubject()
        );
    }
}
