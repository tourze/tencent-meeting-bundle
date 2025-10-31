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
#[ORM\Table(name: 'tencent_meeting_layout', options: ['comment' => '会议布局表'])]
class Layout implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private int $id = 0;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true, options: ['comment' => '布局ID'])]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    private string $layoutId;

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '布局名称'])]
    #[Assert\Length(max: 100)]
    #[Assert\NotBlank]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 500, nullable: true, options: ['comment' => '布局描述'])]
    #[Assert\Length(max: 500)]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '布局类型'])]
    #[Assert\Length(max: 50)]
    #[Assert\Choice(choices: ['gallery', 'speaker', 'active_speaker', 'grid', 'focus', 'custom'])]
    private string $layoutType = 'gallery';

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '布局状态'])]
    #[Assert\Length(max: 50)]
    #[Assert\Choice(choices: ['active', 'inactive', 'deleted'])]
    private string $status = 'active';

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否为默认布局'])]
    #[Assert\NotNull]
    private bool $isDefault = false;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '最大参与者数量'])]
    #[Assert\Positive]
    private int $maxParticipants = 25;

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '布局配置'])]
    #[Assert\Type(type: 'array')]
    private ?array $layoutConfig = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '缩略图URL'])]
    #[Assert\Length(max: 255)]
    #[Assert\Url]
    private ?string $thumbnailUrl = null;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '排序权重'])]
    #[Assert\PositiveOrZero]
    private int $orderWeight = 0;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否为内置布局'])]
    #[Assert\NotNull]
    private bool $isBuiltIn = false;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '适用场景'])]
    #[Assert\Length(max: 255)]
    private ?string $applicableScope = null;

    /**
     * @var Collection<int, MeetingLayout>
     */
    #[ORM\OneToMany(mappedBy: 'layout', targetEntity: MeetingLayout::class)]
    private Collection $meetingLayouts;

    #[ORM\ManyToOne(targetEntity: TencentMeetingConfig::class)]
    #[ORM\JoinColumn(name: 'config_id', referencedColumnName: 'id')]
    private TencentMeetingConfig $config;

    public function __construct()
    {
        $this->meetingLayouts = new ArrayCollection();
    }

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLayoutId(): string
    {
        return $this->layoutId;
    }

    public function setLayoutId(string $layoutId): void
    {
        $this->layoutId = $layoutId;
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

    public function getLayoutType(): string
    {
        return $this->layoutType;
    }

    public function setLayoutType(string $layoutType): void
    {
        $this->layoutType = $layoutType;
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

    public function getMaxParticipants(): int
    {
        return $this->maxParticipants;
    }

    public function setMaxParticipants(int $maxParticipants): void
    {
        $this->maxParticipants = $maxParticipants;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getLayoutConfig(): ?array
    {
        return $this->layoutConfig;
    }

    /**
     * @param array<string, mixed>|null $layoutConfig
     */
    public function setLayoutConfig(?array $layoutConfig): void
    {
        $this->layoutConfig = $layoutConfig;
    }

    public function getThumbnailUrl(): ?string
    {
        return $this->thumbnailUrl;
    }

    public function setThumbnailUrl(?string $thumbnailUrl): void
    {
        $this->thumbnailUrl = $thumbnailUrl;
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

    public function getApplicableScope(): ?string
    {
        return $this->applicableScope;
    }

    public function setApplicableScope(?string $applicableScope): void
    {
        $this->applicableScope = $applicableScope;
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
            $meetingLayout->setLayout($this);
        }
    }

    public function removeMeetingLayout(MeetingLayout $meetingLayout): void
    {
        $this->meetingLayouts->removeElement($meetingLayout);
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
        return sprintf('Layout[id=%s, name=%s]', 0 === $this->id ? 'new' : $this->id, $this->name ?? 'unnamed');
    }
}
