<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

#[ORM\Entity]
#[ORM\Table(name: 'tencent_meeting_recording', options: ['comment' => '会议录制文件表'])]
class Recording implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private int $id = 0;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true, options: ['comment' => '录制ID'])]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    private string $recordingId;

    #[ORM\ManyToOne(targetEntity: Meeting::class, inversedBy: 'recordings', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'meeting_id', referencedColumnName: 'id')]
    private Meeting $meeting;

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '录制类型'])]
    #[Assert\Length(max: 100)]
    #[Assert\Choice(choices: ['cloud', 'local'])]
    private string $recordingType = 'cloud';

    #[ORM\Column(type: Types::STRING, length: 1000, options: ['comment' => '文件URL'])]
    #[Assert\Length(max: 1000)]
    #[Assert\Url]
    #[Assert\NotBlank]
    private string $fileUrl;

    #[ORM\Column(type: Types::STRING, length: 500, nullable: true, options: ['comment' => '文件名称'])]
    #[Assert\Length(max: 500)]
    private ?string $fileName = null;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '文件大小（字节）'])]
    #[Assert\Positive]
    private int $fileSize = 0;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '录制时长（秒）'])]
    #[Assert\PositiveOrZero]
    private int $duration = 0;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '文件格式'])]
    #[Assert\Length(max: 50)]
    private string $fileFormat = 'mp4';

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '录制状态'])]
    #[Assert\Length(max: 50)]
    #[Assert\Choice(choices: ['recording', 'processing', 'available', 'failed', 'deleted'])]
    private string $status = 'recording';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '开始录制时间'])]
    #[Assert\NotNull]
    private \DateTimeImmutable $startTime;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '结束录制时间'])]
    #[Assert\GreaterThan(propertyPath: 'startTime', message: '结束时间必须晚于开始时间')]
    private ?\DateTimeImmutable $endTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '过期时间'])]
    #[Assert\GreaterThanOrEqual(value: 'now', message: '过期时间不能早于当前时间')]
    private ?\DateTimeImmutable $expirationTime = null;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '分享状态'])]
    #[Assert\Length(max: 50)]
    #[Assert\Choice(choices: ['private', 'internal', 'public'])]
    private string $shareStatus = 'private';

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '观看次数'])]
    #[Assert\PositiveOrZero]
    private int $viewCount = 0;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '下载次数'])]
    #[Assert\PositiveOrZero]
    private int $downloadCount = 0;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '录制备注'])]
    #[Assert\Length(max: 1000)]
    private ?string $remark = null;

    #[ORM\Column(type: Types::STRING, length: 500, nullable: true, options: ['comment' => '录制名称'])]
    #[Assert\Length(max: 500)]
    private ?string $recordingName = null;

    #[ORM\Column(type: Types::STRING, length: 1000, nullable: true, options: ['comment' => '录制播放URL'])]
    #[Assert\Length(max: 1000)]
    #[Assert\Url]
    private ?string $recordingUrl = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => '录制格式'])]
    #[Assert\Length(max: 50)]
    private ?string $recordingFormat = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => '分辨率'])]
    #[Assert\Length(max: 50)]
    private ?string $resolution = null;

    #[ORM\Column(type: Types::STRING, length: 1000, nullable: true, options: ['comment' => '下载URL'])]
    #[Assert\Length(max: 1000)]
    #[Assert\Url]
    private ?string $downloadUrl = null;

    #[ORM\Column(type: Types::STRING, length: 1000, nullable: true, options: ['comment' => '播放URL'])]
    #[Assert\Length(max: 1000)]
    #[Assert\Url]
    private ?string $playUrl = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '访问密码'])]
    #[Assert\Length(max: 255)]
    private ?string $password = null;

    #[ORM\ManyToOne(targetEntity: TencentMeetingConfig::class, cascade: ['persist'])]
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

    public function getRecordingId(): string
    {
        return $this->recordingId;
    }

    public function setRecordingId(string $recordingId): void
    {
        $this->recordingId = $recordingId;
    }

    public function getMeeting(): Meeting
    {
        return $this->meeting;
    }

    public function setMeeting(Meeting $meeting): void
    {
        $this->meeting = $meeting;
    }

    public function getRecordingType(): string
    {
        return $this->recordingType;
    }

    public function setRecordingType(string $recordingType): void
    {
        $this->recordingType = $recordingType;
    }

    public function getFileUrl(): string
    {
        return $this->fileUrl;
    }

    public function setFileUrl(string $fileUrl): void
    {
        $this->fileUrl = $fileUrl;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): void
    {
        $this->fileName = $fileName;
    }

    public function getFileSize(): int
    {
        return $this->fileSize;
    }

    public function setFileSize(int $fileSize): void
    {
        $this->fileSize = $fileSize;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): void
    {
        $this->duration = $duration;
    }

    public function getFileFormat(): string
    {
        return $this->fileFormat;
    }

    public function setFileFormat(string $fileFormat): void
    {
        $this->fileFormat = $fileFormat;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
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

    public function getExpirationTime(): ?\DateTimeImmutable
    {
        return $this->expirationTime;
    }

    public function setExpirationTime(?\DateTimeImmutable $expirationTime): void
    {
        $this->expirationTime = $expirationTime;
    }

    public function getShareStatus(): string
    {
        return $this->shareStatus;
    }

    public function setShareStatus(string $shareStatus): void
    {
        $this->shareStatus = $shareStatus;
    }

    public function getViewCount(): int
    {
        return $this->viewCount;
    }

    public function setViewCount(int $viewCount): void
    {
        $this->viewCount = $viewCount;
    }

    public function getDownloadCount(): int
    {
        return $this->downloadCount;
    }

    public function setDownloadCount(int $downloadCount): void
    {
        $this->downloadCount = $downloadCount;
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

    public function getRecordingName(): ?string
    {
        return $this->recordingName;
    }

    public function setRecordingName(?string $recordingName): void
    {
        $this->recordingName = $recordingName;
    }

    public function getRecordingUrl(): ?string
    {
        return $this->recordingUrl;
    }

    public function setRecordingUrl(?string $recordingUrl): void
    {
        $this->recordingUrl = $recordingUrl;
    }

    public function getRecordingFormat(): ?string
    {
        return $this->recordingFormat;
    }

    public function setRecordingFormat(?string $recordingFormat): void
    {
        $this->recordingFormat = $recordingFormat;
    }

    public function getResolution(): ?string
    {
        return $this->resolution;
    }

    public function setResolution(?string $resolution): void
    {
        $this->resolution = $resolution;
    }

    public function getDownloadUrl(): ?string
    {
        return $this->downloadUrl;
    }

    public function setDownloadUrl(?string $downloadUrl): void
    {
        $this->downloadUrl = $downloadUrl;
    }

    public function getPlayUrl(): ?string
    {
        return $this->playUrl;
    }

    public function setPlayUrl(?string $playUrl): void
    {
        $this->playUrl = $playUrl;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    public function __toString(): string
    {
        return $this->recordingName ?? $this->fileName ?? $this->recordingId;
    }
}
