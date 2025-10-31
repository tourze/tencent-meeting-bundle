<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

#[ORM\Entity]
#[ORM\Table(name: 'tencent_meeting_document', options: ['comment' => '会议文档表'])]
class MeetingDocument implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private int $id = 0;

    #[ORM\ManyToOne(targetEntity: Meeting::class, inversedBy: 'documents')]
    #[ORM\JoinColumn(name: 'meeting_id', referencedColumnName: 'id', nullable: false)]
    private Meeting $meeting;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '文档名称'])]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    private string $documentName;

    #[ORM\Column(type: Types::STRING, length: 1000, options: ['comment' => '文档URL'])]
    #[Assert\Length(max: 1000)]
    #[Assert\NotBlank]
    #[Assert\Url]
    private string $documentUrl;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '文档类型'])]
    #[Assert\Length(max: 50)]
    #[Assert\Choice(choices: ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'image', 'video', 'other'])]
    private string $documentType = 'other';

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '文件大小（字节）'])]
    #[Assert\Positive]
    private int $fileSize = 0;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => 'MIME类型'])]
    #[Assert\Length(max: 100)]
    private ?string $mimeType = null;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '文档状态'])]
    #[Assert\Length(max: 50)]
    #[Assert\Choice(choices: ['uploading', 'available', 'processing', 'deleted'])]
    private string $status = 'available';

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '文件路径'])]
    #[Assert\Length(max: 255)]
    private ?string $filePath = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '存储路径'])]
    #[Assert\Length(max: 255)]
    private ?string $storagePath = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '缩略图URL'])]
    #[Assert\Length(max: 255)]
    #[Assert\Url]
    private ?string $thumbnailUrl = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => '上传者用户ID'])]
    #[Assert\Length(max: 50)]
    private ?string $uploaderUserId = null;

    /** @var array<string, string|int|bool|float|null>|null */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '权限设置'])]
    #[Assert\Type(type: 'array')]
    private ?array $permissions = null;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '下载次数'])]
    #[Assert\PositiveOrZero]
    private int $downloadCount = 0;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '查看次数'])]
    #[Assert\PositiveOrZero]
    private int $viewCount = 0;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '过期时间'])]
    #[Assert\GreaterThanOrEqual(value: 'now', message: '过期时间不能早于当前时间')]
    private ?\DateTimeImmutable $expirationTime = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '文档描述'])]
    #[Assert\Length(max: 65535)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注'])]
    #[Assert\Length(max: 65535)]
    private ?string $remark = null;

    #[ORM\ManyToOne(targetEntity: TencentMeetingConfig::class)]
    #[ORM\JoinColumn(name: 'config_id', referencedColumnName: 'id')]
    private TencentMeetingConfig $config;

    public function __construct()
    {
        $this->documentName = '';
        $this->documentUrl = '';
        // 注意：meeting 和 config 字段需要在对象创建后通过 setter 设置
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

    public function getDocumentName(): string
    {
        return $this->documentName;
    }

    public function setDocumentName(string $documentName): void
    {
        $this->documentName = $documentName;
    }

    public function getDocumentUrl(): string
    {
        return $this->documentUrl;
    }

    public function setDocumentUrl(string $documentUrl): void
    {
        $this->documentUrl = $documentUrl;
    }

    public function getDocumentType(): string
    {
        return $this->documentType;
    }

    public function setDocumentType(string $documentType): void
    {
        $this->documentType = $documentType;
    }

    public function getFileSize(): int
    {
        return $this->fileSize;
    }

    public function setFileSize(int $fileSize): void
    {
        $this->fileSize = $fileSize;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): void
    {
        $this->mimeType = $mimeType;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): void
    {
        $this->filePath = $filePath;
    }

    public function getStoragePath(): ?string
    {
        return $this->storagePath;
    }

    public function setStoragePath(?string $storagePath): void
    {
        $this->storagePath = $storagePath;
    }

    public function getThumbnailUrl(): ?string
    {
        return $this->thumbnailUrl;
    }

    public function setThumbnailUrl(?string $thumbnailUrl): void
    {
        $this->thumbnailUrl = $thumbnailUrl;
    }

    public function getUploaderUserId(): ?string
    {
        return $this->uploaderUserId;
    }

    public function setUploaderUserId(?string $uploaderUserId): void
    {
        $this->uploaderUserId = $uploaderUserId;
    }

    /**
     * @return array<string, mixed>|null
     */
    /**
     * @return array<string, string|int|bool|float|null>|null
     */
    public function getPermissions(): ?array
    {
        return $this->permissions;
    }

    /**
     * @param array<string, string|int|bool|float|null>|null $permissions
     */
    public function setPermissions(?array $permissions): void
    {
        $this->permissions = $permissions;
    }

    public function getDownloadCount(): int
    {
        return $this->downloadCount;
    }

    public function setDownloadCount(int $downloadCount): void
    {
        $this->downloadCount = $downloadCount;
    }

    public function getViewCount(): int
    {
        return $this->viewCount;
    }

    public function setViewCount(int $viewCount): void
    {
        $this->viewCount = $viewCount;
    }

    public function getExpirationTime(): ?\DateTimeImmutable
    {
        return $this->expirationTime;
    }

    public function setExpirationTime(?\DateTimeImmutable $expirationTime): void
    {
        $this->expirationTime = $expirationTime;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
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
        return sprintf('MeetingDocument[id=%s, name=%s]', 0 === $this->id ? 'new' : $this->id, $this->documentName);
    }
}
