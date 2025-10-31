<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\TencentMeetingBundle\Repository\TencentMeetingConfigRepository;

#[ORM\Entity(repositoryClass: TencentMeetingConfigRepository::class)]
#[ORM\Table(name: 'tencent_meeting_config', options: ['comment' => '腾讯会议配置表'])]
class TencentMeetingConfig implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private int $id = 0;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true, options: ['comment' => '应用ID'])]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    private string $appId;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '密钥ID'])]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    private string $secretId;

    #[ORM\Column(type: Types::TEXT, options: ['comment' => '密钥'])]
    #[Assert\Length(max: 2000)]
    #[Assert\NotBlank]
    private string $secretKey;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '认证类型'])]
    #[Assert\Length(max: 50)]
    #[Assert\Choice(choices: ['JWT', 'OAuth2'])]
    private string $authType = 'JWT';

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => 'Webhook令牌'])]
    #[Assert\Length(max: 255)]
    private ?string $webhookToken = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否启用'])]
    #[Assert\NotNull]
    private bool $enabled = true;

    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAppId(): string
    {
        return $this->appId;
    }

    public function setAppId(string $appId): void
    {
        $this->appId = $appId;
    }

    public function getSecretId(): string
    {
        return $this->secretId;
    }

    public function setSecretId(string $secretId): void
    {
        $this->secretId = $secretId;
    }

    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    public function setSecretKey(string $secretKey): void
    {
        $this->secretKey = $secretKey;
    }

    public function getAuthType(): string
    {
        return $this->authType;
    }

    public function setAuthType(string $authType): void
    {
        $this->authType = $authType;
    }

    public function getWebhookToken(): ?string
    {
        return $this->webhookToken;
    }

    public function setWebhookToken(?string $webhookToken): void
    {
        $this->webhookToken = $webhookToken;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function __toString(): string
    {
        return sprintf('TencentMeetingConfig[id=%s, appId=%s]', 0 === $this->id ? 'new' : $this->id, $this->appId);
    }
}
