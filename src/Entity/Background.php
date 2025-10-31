<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

#[ORM\Entity]
#[ORM\Table(name: 'tencent_meeting_background', options: ['comment' => '会议背景表'])]
class Background implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private int $id = 0;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true, options: ['comment' => '背景ID'])]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    private string $backgroundId = '';

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '背景名称'])]
    #[Assert\Length(max: 100)]
    #[Assert\NotBlank]
    private string $name = '';

    #[ORM\Column(type: Types::STRING, length: 500, nullable: true, options: ['comment' => '背景描述'])]
    #[Assert\Length(max: 500)]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, length: 1000, options: ['comment' => '背景图片URL'])]
    #[Assert\Length(max: 1000)]
    #[Assert\NotBlank]
    #[Assert\Url]
    private string $imageUrl = '';

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '缩略图URL'])]
    #[Assert\Length(max: 255)]
    #[Assert\Url]
    private ?string $thumbnailUrl = null;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '背景类型'])]
    #[Assert\Length(max: 50)]
    #[Assert\Choice(choices: ['image', 'color', 'gradient', 'blur', 'custom'])]
    private string $backgroundType = 'image';

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '背景状态'])]
    #[Assert\Length(max: 50)]
    #[Assert\Choice(choices: ['active', 'inactive', 'deleted'])]
    private string $status = 'active';

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否为默认背景'])]
    #[Assert\NotNull]
    private bool $isDefault = false;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '适用范围'])]
    #[Assert\Length(max: 255)]
    private ?string $applicableScope = null;

    /**
     * @var array<string, mixed>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '背景配置'])]
    #[Assert\Count(max: 100)]
    private ?array $backgroundConfig = null;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '文件大小（字节）'])]
    #[Assert\Positive]
    private int $fileSize = 0;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => '图片格式'])]
    #[Assert\Length(max: 50)]
    private ?string $imageFormat = null;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '图片宽度'])]
    #[Assert\Positive]
    private int $imageWidth = 0;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '图片高度'])]
    #[Assert\Positive]
    private int $imageHeight = 0;

    #[ORM\Column(type: Types::STRING, length: 7, nullable: true, options: ['comment' => '主色调'])]
    #[Assert\Length(max: 7)]
    private ?string $primaryColor = null;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '排序权重'])]
    #[Assert\PositiveOrZero]
    private int $orderWeight = 0;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否为内置背景'])]
    #[Assert\NotNull]
    private bool $isBuiltIn = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '过期时间'])]
    #[Assert\GreaterThanOrEqual(propertyPath: 'today')]
    private ?\DateTimeImmutable $expirationTime = null;

    /**
     * @var Collection<int, MeetingBackground>
     */
    #[ORM\OneToMany(mappedBy: 'background', targetEntity: MeetingBackground::class)]
    private Collection $meetingBackgrounds;

    #[ORM\ManyToOne(targetEntity: TencentMeetingConfig::class)]
    #[ORM\JoinColumn(name: 'config_id', referencedColumnName: 'id')]
    private TencentMeetingConfig $config;

    public function __construct()
    {
        $this->meetingBackgrounds = new ArrayCollection();
    }

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBackgroundId(): string
    {
        return $this->backgroundId;
    }

    public function setBackgroundId(string $backgroundId): void
    {
        $this->backgroundId = $backgroundId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(string $imageUrl): void
    {
        $this->imageUrl = $imageUrl;
    }

    public function getThumbnailUrl(): ?string
    {
        return $this->thumbnailUrl;
    }

    public function setThumbnailUrl(?string $thumbnailUrl): void
    {
        $this->thumbnailUrl = $thumbnailUrl;
    }

    public function getBackgroundType(): string
    {
        return $this->backgroundType;
    }

    public function setBackgroundType(string $backgroundType): void
    {
        $this->backgroundType = $backgroundType;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function setDefault(bool $isDefault): void
    {
        $this->isDefault = $isDefault;
    }

    public function getApplicableScope(): ?string
    {
        return $this->applicableScope;
    }

    public function setApplicableScope(?string $applicableScope): void
    {
        $this->applicableScope = $applicableScope;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getBackgroundConfig(): ?array
    {
        return $this->backgroundConfig;
    }

    /**
     * @param array<string, mixed>|null $backgroundConfig
     */
    public function setBackgroundConfig(?array $backgroundConfig): void
    {
        $this->backgroundConfig = $backgroundConfig;
    }

    public function getFileSize(): int
    {
        return $this->fileSize;
    }

    public function setFileSize(int $fileSize): void
    {
        $this->fileSize = $fileSize;
    }

    public function getImageFormat(): ?string
    {
        return $this->imageFormat;
    }

    public function setImageFormat(?string $imageFormat): void
    {
        $this->imageFormat = $imageFormat;
    }

    public function getImageWidth(): int
    {
        return $this->imageWidth;
    }

    public function setImageWidth(int $imageWidth): void
    {
        $this->imageWidth = $imageWidth;
    }

    public function getImageHeight(): int
    {
        return $this->imageHeight;
    }

    public function setImageHeight(int $imageHeight): void
    {
        $this->imageHeight = $imageHeight;
    }

    public function getPrimaryColor(): ?string
    {
        return $this->primaryColor;
    }

    public function setPrimaryColor(?string $primaryColor): void
    {
        $this->primaryColor = $primaryColor;
    }

    public function getOrderWeight(): int
    {
        return $this->orderWeight;
    }

    public function setOrderWeight(int $orderWeight): void
    {
        $this->orderWeight = $orderWeight;
    }

    public function isBuiltIn(): bool
    {
        return $this->isBuiltIn;
    }

    public function setBuiltIn(bool $isBuiltIn): void
    {
        $this->isBuiltIn = $isBuiltIn;
    }

    public function getExpirationTime(): ?\DateTimeImmutable
    {
        return $this->expirationTime;
    }

    public function setExpirationTime(?\DateTimeImmutable $expirationTime): void
    {
        $this->expirationTime = $expirationTime;
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
            $meetingBackground->setBackground($this);
        }
    }

    public function removeMeetingBackground(MeetingBackground $meetingBackground): void
    {
        $this->meetingBackgrounds->removeElement($meetingBackground);
    }

    public function getConfig(): TencentMeetingConfig
    {
        return $this->config;
    }

    public function setConfig(TencentMeetingConfig $config): void
    {
        $this->config = $config;
    }

    public function getConfigEntity(): TencentMeetingConfig
    {
        return $this->config;
    }

    public function setConfigEntity(TencentMeetingConfig $config): void
    {
        $this->config = $config;
    }

    public function __toString(): string
    {
        return '' !== $this->name ? $this->name : $this->backgroundId;
    }
}
