<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

#[ORM\Entity]
#[ORM\Table(name: 'tencent_meeting_device', options: ['comment' => '设备信息表'])]
class Device implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private int $id = 0;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true, options: ['comment' => '设备ID'])]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    private string $deviceId = '';

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '设备名称'])]
    #[Assert\Length(max: 100)]
    #[Assert\NotBlank]
    private string $name = '';

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '设备类型'])]
    #[Assert\Length(max: 50)]
    #[Assert\Choice(choices: ['camera', 'microphone', 'speaker', 'display', 'touch_screen', 'whiteboard', 'other'])]
    private string $deviceType = 'other';

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '设备品牌'])]
    #[Assert\Length(max: 100)]
    private ?string $brand = null;

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '设备型号'])]
    #[Assert\Length(max: 100)]
    private ?string $model = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '设备序列号'])]
    #[Assert\Length(max: 255)]
    private ?string $serialNumber = null;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '设备状态'])]
    #[Assert\Length(max: 50)]
    #[Assert\Choice(choices: ['online', 'offline', 'maintenance', 'error'])]
    private string $status = 'offline';

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '激活码'])]
    #[Assert\Length(max: 255)]
    private ?string $activationCode = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '激活时间'])]
    #[Assert\GreaterThanOrEqual(propertyPath: 'today')]
    private ?\DateTimeImmutable $activationTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '过期时间'])]
    #[Assert\GreaterThanOrEqual(propertyPath: 'today')]
    private ?\DateTimeImmutable $expirationTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '最后在线时间'])]
    #[Assert\GreaterThanOrEqual(propertyPath: 'today')]
    private ?\DateTimeImmutable $lastOnlineTime = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => 'IP地址'])]
    #[Assert\Length(max: 255)]
    private ?string $ipAddress = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => 'MAC地址'])]
    #[Assert\Length(max: 100)]
    private ?string $macAddress = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => '固件版本'])]
    #[Assert\Length(max: 50)]
    private ?string $firmwareVersion = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => '软件版本'])]
    #[Assert\Length(max: 50)]
    private ?string $softwareVersion = null;

    /** @var array<string, string|int|bool|float|null>|null */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '设备配置'])]
    #[Assert\Count(max: 100)]
    private ?array $deviceConfig = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '设备备注'])]
    #[Assert\Length(max: 1000)]
    private ?string $remark = null;

    #[ORM\ManyToOne(targetEntity: MeetingRoom::class, inversedBy: 'devices')]
    #[ORM\JoinColumn(name: 'room_id', referencedColumnName: 'id')]
    private ?MeetingRoom $room = null;

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

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function setDeviceId(string $deviceId): void
    {
        $this->deviceId = $deviceId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDeviceType(): string
    {
        return $this->deviceType;
    }

    public function setDeviceType(string $deviceType): void
    {
        $this->deviceType = $deviceType;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(?string $brand): void
    {
        $this->brand = $brand;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): void
    {
        $this->model = $model;
    }

    public function getSerialNumber(): ?string
    {
        return $this->serialNumber;
    }

    public function setSerialNumber(?string $serialNumber): void
    {
        $this->serialNumber = $serialNumber;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getActivationCode(): ?string
    {
        return $this->activationCode;
    }

    public function setActivationCode(?string $activationCode): void
    {
        $this->activationCode = $activationCode;
    }

    public function getActivationTime(): ?\DateTimeImmutable
    {
        return $this->activationTime;
    }

    public function setActivationTime(?\DateTimeImmutable $activationTime): void
    {
        $this->activationTime = $activationTime;
    }

    public function getExpirationTime(): ?\DateTimeImmutable
    {
        return $this->expirationTime;
    }

    public function setExpirationTime(?\DateTimeImmutable $expirationTime): void
    {
        $this->expirationTime = $expirationTime;
    }

    public function getLastOnlineTime(): ?\DateTimeImmutable
    {
        return $this->lastOnlineTime;
    }

    public function setLastOnlineTime(?\DateTimeImmutable $lastOnlineTime): void
    {
        $this->lastOnlineTime = $lastOnlineTime;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): void
    {
        $this->ipAddress = $ipAddress;
    }

    public function getMacAddress(): ?string
    {
        return $this->macAddress;
    }

    public function setMacAddress(?string $macAddress): void
    {
        $this->macAddress = $macAddress;
    }

    public function getFirmwareVersion(): ?string
    {
        return $this->firmwareVersion;
    }

    public function setFirmwareVersion(?string $firmwareVersion): void
    {
        $this->firmwareVersion = $firmwareVersion;
    }

    public function getSoftwareVersion(): ?string
    {
        return $this->softwareVersion;
    }

    public function setSoftwareVersion(?string $softwareVersion): void
    {
        $this->softwareVersion = $softwareVersion;
    }

    /**
     * @return array<string, string|int|bool|float|null>|null
     */
    public function getDeviceConfig(): ?array
    {
        return $this->deviceConfig;
    }

    /**
     * @param array<string, string|int|bool|float|null>|null $deviceConfig
     */
    public function setDeviceConfig(?array $deviceConfig): void
    {
        $this->deviceConfig = $deviceConfig;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): void
    {
        $this->remark = $remark;
    }

    public function getRoom(): ?MeetingRoom
    {
        return $this->room;
    }

    public function setRoom(?MeetingRoom $room): void
    {
        $this->room = $room;
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
        return '' !== $this->name ? $this->name : $this->deviceId;
    }
}
