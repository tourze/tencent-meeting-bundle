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
#[ORM\Table(name: 'tencent_meeting_meeting_room', options: ['comment' => '会议关联会议室表'])]
class MeetingRoom implements \Stringable
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

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '会议室描述'])]
    #[Assert\Length(max: 1000)]
    private ?string $description = null;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '会议室容量'])]
    #[Assert\Positive]
    private int $capacity = 1;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '会议室类型'])]
    #[Assert\Length(max: 50)]
    #[Assert\Choice(choices: ['huddle_room', 'conference_room', 'training_room', 'auditorium'])]
    private string $roomType = 'conference_room';

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '会议室位置'])]
    #[Assert\Length(max: 255)]
    private ?string $location = null;

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '设备状态'])]
    #[Assert\Length(max: 100)]
    #[Assert\Choice(choices: ['online', 'offline', 'maintenance'])]
    private string $deviceStatus = 'offline';

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '会议室状态'])]
    #[Assert\Length(max: 50)]
    #[Assert\Choice(choices: ['available', 'occupied', 'maintenance', 'disabled'])]
    private string $status = 'available';

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '设备列表'])]
    #[Assert\Length(max: 255)]
    private ?string $equipmentList = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否支持录制'])]
    #[Assert\Type(type: 'bool')]
    #[Assert\NotNull]
    private bool $supportRecording = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否支持直播'])]
    #[Assert\Type(type: 'bool')]
    #[Assert\NotNull]
    private bool $supportLive = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否支持屏幕共享'])]
    #[Assert\Type(type: 'bool')]
    #[Assert\NotNull]
    private bool $supportScreenShare = true;

    #[ORM\ManyToOne(targetEntity: TencentMeetingConfig::class)]
    #[ORM\JoinColumn(name: 'config_id', referencedColumnName: 'id')]
    private TencentMeetingConfig $config;

    /**
     * @var Collection<int, Device>
     */
    #[ORM\OneToMany(mappedBy: 'room', targetEntity: Device::class)]
    private Collection $devices;

    public function __construct()
    {
        $this->devices = new ArrayCollection();
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

    public function getCapacity(): int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): void
    {
        $this->capacity = $capacity;
    }

    public function getRoomType(): string
    {
        return $this->roomType;
    }

    public function setRoomType(string $roomType): void
    {
        $this->roomType = $roomType;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): void
    {
        $this->location = $location;
    }

    public function getDeviceStatus(): string
    {
        return $this->deviceStatus;
    }

    public function setDeviceStatus(string $deviceStatus): void
    {
        $this->deviceStatus = $deviceStatus;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getEquipmentList(): ?string
    {
        return $this->equipmentList;
    }

    public function setEquipmentList(?string $equipmentList): void
    {
        $this->equipmentList = $equipmentList;
    }

    public function isSupportRecording(): bool
    {
        return $this->supportRecording;
    }

    public function setSupportRecording(bool $supportRecording): void
    {
        $this->supportRecording = $supportRecording;
    }

    public function isSupportLive(): bool
    {
        return $this->supportLive;
    }

    public function setSupportLive(bool $supportLive): void
    {
        $this->supportLive = $supportLive;
    }

    public function isSupportScreenShare(): bool
    {
        return $this->supportScreenShare;
    }

    public function setSupportScreenShare(bool $supportScreenShare): void
    {
        $this->supportScreenShare = $supportScreenShare;
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
     * @return Collection<int, Device>
     */
    public function getDevices(): Collection
    {
        return $this->devices;
    }

    public function addDevice(Device $device): void
    {
        if (!$this->devices->contains($device)) {
            $this->devices->add($device);
            $device->setRoom($this);
        }
    }

    public function removeDevice(Device $device): void
    {
        if ($this->devices->removeElement($device)) {
            if ($device->getRoom() === $this) {
                $device->setRoom(null);
            }
        }
    }

    public function __toString(): string
    {
        return sprintf(
            'MeetingRoom[id=%d, name=%s]',
            $this->id,
            $this->name ?? 'unknown'
        );
    }
}
