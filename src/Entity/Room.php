<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

#[ORM\Entity]
#[ORM\Table(name: 'tencent_meeting_room', options: ['comment' => '会议室表'])]
class Room implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private int $id = 0;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true, options: ['comment' => '会议室ID'])]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    private string $roomId;

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '会议室名称'])]
    #[Assert\Length(max: 100)]
    #[Assert\NotBlank]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 500, nullable: true, options: ['comment' => '会议室描述'])]
    #[Assert\Length(max: 500)]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '会议室类型'])]
    #[Assert\Length(max: 50)]
    #[Assert\Choice(choices: ['physical', 'virtual', 'hybrid'])]
    private string $roomType = 'virtual';

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '会议室状态'])]
    #[Assert\Length(max: 50)]
    #[Assert\Choice(choices: ['available', 'occupied', 'maintenance', 'inactive'])]
    private string $status = 'available';

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '容量（人数）'])]
    #[Assert\Positive]
    private int $capacity = 10;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '位置'])]
    #[Assert\Length(max: 255)]
    private ?string $location = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '设备信息'])]
    #[Assert\Length(max: 255)]
    private ?string $equipment = null;

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '会议室配置'])]
    #[Assert\Type(type: 'array')]
    private ?array $roomConfig = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '预订规则'])]
    #[Assert\Length(max: 255)]
    private ?string $bookingRules = null;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '排序权重'])]
    #[Assert\PositiveOrZero]
    private int $orderWeight = 0;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否需要审批'])]
    #[Assert\NotNull]
    private bool $requiresApproval = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '过期时间'])]
    #[Assert\GreaterThanOrEqual(value: 'now', message: '过期时间不能早于当前时间')]
    private ?\DateTimeImmutable $expirationTime = null;

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

    public function getRoomId(): string
    {
        return $this->roomId;
    }

    public function setRoomId(string $roomId): void
    {
        $this->roomId = $roomId;
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

    public function getRoomType(): string
    {
        return $this->roomType;
    }

    public function setRoomType(string $roomType): void
    {
        $this->roomType = $roomType;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getCapacity(): int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): void
    {
        $this->capacity = $capacity;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): void
    {
        $this->location = $location;
    }

    public function getEquipment(): ?string
    {
        return $this->equipment;
    }

    public function setEquipment(?string $equipment): void
    {
        $this->equipment = $equipment;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getRoomConfig(): ?array
    {
        return $this->roomConfig;
    }

    /**
     * @param array<string, mixed>|null $roomConfig
     */
    public function setRoomConfig(?array $roomConfig): void
    {
        $this->roomConfig = $roomConfig;
    }

    public function getBookingRules(): ?string
    {
        return $this->bookingRules;
    }

    public function setBookingRules(?string $bookingRules): void
    {
        $this->bookingRules = $bookingRules;
    }

    public function getOrderWeight(): int
    {
        return $this->orderWeight;
    }

    public function setOrderWeight(int $orderWeight): void
    {
        $this->orderWeight = $orderWeight;
    }

    public function isRequiresApproval(): bool
    {
        return $this->requiresApproval;
    }

    public function setRequiresApproval(bool $requiresApproval): void
    {
        $this->requiresApproval = $requiresApproval;
    }

    public function getExpirationTime(): ?\DateTimeImmutable
    {
        return $this->expirationTime;
    }

    public function setExpirationTime(?\DateTimeImmutable $expirationTime): void
    {
        $this->expirationTime = $expirationTime;
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
        return $this->name;
    }
}
