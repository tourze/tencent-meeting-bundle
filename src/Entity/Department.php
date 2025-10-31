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
#[ORM\Table(name: 'tencent_meeting_department', options: ['comment' => '部门结构表'])]
class Department implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private int $id = 0;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true, options: ['comment' => '部门ID'])]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    private string $departmentId = '';

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '部门名称'])]
    #[Assert\Length(max: 100)]
    #[Assert\NotBlank]
    private string $name = '';

    #[ORM\Column(type: Types::STRING, length: 500, nullable: true, options: ['comment' => '部门描述'])]
    #[Assert\Length(max: 500)]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: Department::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id')]
    private ?Department $parent = null;

    /**
     * @var Collection<int, Department>
     */
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: Department::class)]
    private Collection $children;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(mappedBy: 'department', targetEntity: User::class)]
    private Collection $users;

    #[ORM\Column(type: Types::STRING, length: 1000, nullable: true, options: ['comment' => '层级路径'])]
    #[Assert\Length(max: 1000)]
    private ?string $path = null;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '层级深度'])]
    #[Assert\PositiveOrZero]
    private int $level = 0;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '排序权重'])]
    #[Assert\PositiveOrZero]
    private int $orderWeight = 0;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '部门状态'])]
    #[Assert\Length(max: 50)]
    #[Assert\Choice(choices: ['active', 'inactive'])]
    private string $status = 'active';

    #[ORM\ManyToOne(targetEntity: TencentMeetingConfig::class)]
    #[ORM\JoinColumn(name: 'config_id', referencedColumnName: 'id')]
    private TencentMeetingConfig $config;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDepartmentId(): string
    {
        return $this->departmentId;
    }

    public function setDepartmentId(string $departmentId): void
    {
        $this->departmentId = $departmentId;
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

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return Collection<int, self>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): void
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }
    }

    public function removeChild(self $child): void
    {
        if ($this->children->removeElement($child)) {
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): void
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setDepartment($this);
        }
    }

    public function removeUser(User $user): void
    {
        if ($this->users->removeElement($user)) {
            if ($user->getDepartment() === $this) {
                $user->setDepartment(null);
            }
        }
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): void
    {
        $this->path = $path;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    public function getOrderWeight(): int
    {
        return $this->orderWeight;
    }

    public function setOrderWeight(int $orderWeight): void
    {
        $this->orderWeight = $orderWeight;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
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
        return '' !== $this->name ? $this->name : $this->departmentId;
    }
}
