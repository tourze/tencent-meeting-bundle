<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

#[ORM\Entity]
#[ORM\Table(name: 'tencent_meeting_webhook_event', options: ['comment' => 'Webhook事件表'])]
class WebhookEvent implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private int $id = 0;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '事件ID'])]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    private string $eventId;

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '事件类型'])]
    #[Assert\Length(max: 100)]
    #[Assert\NotBlank]
    private string $eventType;

    #[ORM\Column(type: Types::TEXT, options: ['comment' => '原始Payload'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 65535)]
    private string $payload;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '会议ID'])]
    #[Assert\Length(max: 255)]
    private ?string $meetingId = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '用户ID'])]
    #[Assert\Length(max: 255)]
    private ?string $userId = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '事件时间'])]
    #[Assert\NotNull]
    private \DateTimeImmutable $eventTime;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '处理状态'])]
    #[Assert\Length(max: 50)]
    #[Assert\Choice(choices: ['pending', 'processing', 'success', 'failed'])]
    private string $processStatus = 'pending';

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '处理结果'])]
    #[Assert\Length(max: 65535)]
    private ?string $processResult = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '处理时间'])]
    #[Assert\DateTime]
    private ?\DateTimeImmutable $processingTime = null;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '重试次数'])]
    #[Assert\PositiveOrZero]
    private int $retryCount = 0;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '下次重试时间'])]
    #[Assert\DateTime]
    private ?\DateTimeImmutable $nextRetryTime = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '错误信息'])]
    #[Assert\Length(max: 255)]
    private ?string $errorMessage = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否已验证签名'])]
    #[Assert\Type(type: 'bool')]
    #[Assert\NotNull]
    private bool $signatureVerified = false;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '来源IP'])]
    #[Assert\Length(max: 100)]
    private ?string $sourceIp = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => '用户代理'])]
    #[Assert\Length(max: 50)]
    private ?string $userAgent = null;

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

    public function getEventId(): string
    {
        return $this->eventId;
    }

    public function setEventId(string $eventId): void
    {
        $this->eventId = $eventId;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }

    public function setEventType(string $eventType): void
    {
        $this->eventType = $eventType;
    }

    public function getPayload(): string
    {
        return $this->payload;
    }

    public function setPayload(string $payload): void
    {
        $this->payload = $payload;
    }

    public function getMeetingId(): ?string
    {
        return $this->meetingId;
    }

    public function setMeetingId(?string $meetingId): void
    {
        $this->meetingId = $meetingId;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(?string $userId): void
    {
        $this->userId = $userId;
    }

    public function getEventTime(): \DateTimeImmutable
    {
        return $this->eventTime;
    }

    public function setEventTime(\DateTimeImmutable $eventTime): void
    {
        $this->eventTime = $eventTime;
    }

    public function getProcessStatus(): string
    {
        return $this->processStatus;
    }

    public function setProcessStatus(string $processStatus): void
    {
        $this->processStatus = $processStatus;
    }

    public function getProcessResult(): ?string
    {
        return $this->processResult;
    }

    public function setProcessResult(?string $processResult): void
    {
        $this->processResult = $processResult;
    }

    public function getProcessingTime(): ?\DateTimeImmutable
    {
        return $this->processingTime;
    }

    public function setProcessingTime(?\DateTimeImmutable $processingTime): void
    {
        $this->processingTime = $processingTime;
    }

    public function getRetryCount(): int
    {
        return $this->retryCount;
    }

    public function setRetryCount(int $retryCount): void
    {
        $this->retryCount = $retryCount;
    }

    public function getNextRetryTime(): ?\DateTimeImmutable
    {
        return $this->nextRetryTime;
    }

    public function setNextRetryTime(?\DateTimeImmutable $nextRetryTime): void
    {
        $this->nextRetryTime = $nextRetryTime;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): void
    {
        $this->errorMessage = $errorMessage;
    }

    public function isSignatureVerified(): bool
    {
        return $this->signatureVerified;
    }

    public function setSignatureVerified(bool $signatureVerified): void
    {
        $this->signatureVerified = $signatureVerified;
    }

    public function getSourceIp(): ?string
    {
        return $this->sourceIp;
    }

    public function setSourceIp(?string $sourceIp): void
    {
        $this->sourceIp = $sourceIp;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): void
    {
        $this->userAgent = $userAgent;
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
            'WebhookEvent[id=%d, type=%s, status=%s]',
            $this->id ?? 0,
            $this->eventType,
            $this->processStatus
        );
    }
}
