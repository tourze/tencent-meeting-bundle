<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Entity;

use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TencentMeetingBundle\Entity\Background;

/**
 * @internal
 */
#[CoversClass(Background::class)]
final class BackgroundTest extends AbstractEntityTestCase
{
    protected function createEntity(): Background
    {
        return new Background();
    }

    public function testBackgroundCreation(): void
    {
        $background = new Background();

        $this->assertInstanceOf(Background::class, $background);
        $this->assertSame(0, $background->getId()); // New entities have ID = 0 before persistence
        $this->assertEquals('', $background->getBackgroundId());
        $this->assertEquals('', $background->getName());
        $this->assertEquals('image', $background->getBackgroundType());
        $this->assertEquals('active', $background->getStatus());
        $this->assertFalse($background->isDefault());
        $this->assertFalse($background->isBuiltIn());
        $this->assertEquals(0, $background->getFileSize());
        $this->assertEquals(0, $background->getImageWidth());
        $this->assertEquals(0, $background->getImageHeight());
        $this->assertEquals(0, $background->getOrderWeight());
        $this->assertNull($background->getExpirationTime());
        $this->assertNull($background->getDescription());
        $this->assertNull($background->getThumbnailUrl());
        $this->assertNull($background->getApplicableScope());
        $this->assertNull($background->getBackgroundConfig());
        $this->assertNull($background->getImageFormat());
        $this->assertNull($background->getPrimaryColor());
        $this->assertNull($background->getUpdateTime());
    }

    public function testBackgroundSettersAndGetters(): void
    {
        $background = new Background();

        $background->setBackgroundId('bg_123');
        $background->setName('测试背景');
        $background->setDescription('这是一个测试背景');
        $background->setImageUrl('https://example.com/image.jpg');
        $background->setThumbnailUrl('https://example.com/thumb.jpg');
        $background->setBackgroundType('color');
        $background->setStatus('inactive');
        $background->setDefault(true);
        $background->setApplicableScope('all');
        $background->setBackgroundConfig(['opacity' => 0.8]);
        $background->setFileSize(1024);
        $background->setImageFormat('jpg');
        $background->setImageWidth(1920);
        $background->setImageHeight(1080);
        $background->setPrimaryColor('#ffffff');
        $background->setOrderWeight(10);
        $background->setBuiltIn(true);
        $background->setExpirationTime(new \DateTimeImmutable('2024-12-31'));

        $this->assertEquals('bg_123', $background->getBackgroundId());
        $this->assertEquals('测试背景', $background->getName());
        $this->assertEquals('这是一个测试背景', $background->getDescription());
        $this->assertEquals('https://example.com/image.jpg', $background->getImageUrl());
        $this->assertEquals('https://example.com/thumb.jpg', $background->getThumbnailUrl());
        $this->assertEquals('color', $background->getBackgroundType());
        $this->assertEquals('inactive', $background->getStatus());
        $this->assertTrue($background->isDefault());
        $this->assertEquals('all', $background->getApplicableScope());
        $this->assertEquals(['opacity' => 0.8], $background->getBackgroundConfig());
        $this->assertEquals(1024, $background->getFileSize());
        $this->assertEquals('jpg', $background->getImageFormat());
        $this->assertEquals(1920, $background->getImageWidth());
        $this->assertEquals(1080, $background->getImageHeight());
        $this->assertEquals('#ffffff', $background->getPrimaryColor());
        $this->assertEquals(10, $background->getOrderWeight());
        $this->assertTrue($background->isBuiltIn());
        $this->assertInstanceOf(\DateTimeImmutable::class, $background->getExpirationTime());
    }

    public function testBackgroundToString(): void
    {
        $background = new Background();
        $background->setName('测试背景');

        $this->assertEquals('测试背景', (string) $background);

        $background2 = new Background();
        $background2->setBackgroundId('bg_456');
        // 不设置 name，让它保持默认的空字符串

        // 调试信息
        $name = $background2->getName();
        $backgroundId = $background2->getBackgroundId();
        $result = (string) $background2;
        $this->assertEquals('bg_456', $result, "Expected 'bg_456' but got '{$result}'. Name: '{$name}', BackgroundId: '{$backgroundId}'");

        $background3 = new Background();
        $this->assertEquals('', (string) $background3);
    }

    public function testBackgroundCollectionMethods(): void
    {
        $background = new Background();

        $this->assertInstanceOf(Collection::class, $background->getMeetingBackgrounds());
        $this->assertCount(0, $background->getMeetingBackgrounds());

        // 测试集合操作会在需要实际MeetingBackground对象时进行
    }

    public function testBackgroundTimeMethods(): void
    {
        $background = new Background();

        // Initially, times should be null as they are set automatically by the system
        $this->assertNull($background->getCreateTime());
        $this->assertNull($background->getUpdateTime());

        $newTime = new \DateTimeImmutable('2024-01-01');
        $background->setCreateTime($newTime);
        $this->assertEquals($newTime, $background->getCreateTime());

        $background->setUpdateTime($newTime);
        $this->assertEquals($newTime, $background->getUpdateTime());
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'backgroundId' => ['backgroundId', 'bg_123'],
            'name' => ['name', 'Test Background'],
            'description' => ['description', 'Test background description'],
            'imageUrl' => ['imageUrl', 'https://example.com/image.jpg'],
            'thumbnailUrl' => ['thumbnailUrl', 'https://example.com/thumb.jpg'],
            'backgroundType' => ['backgroundType', 'image'],
            'status' => ['status', 'active'],
            'default' => ['default', false],
            'applicableScope' => ['applicableScope', 'all'],
            'backgroundConfig' => ['backgroundConfig', ['opacity' => 0.8, 'blur' => true]],
            'fileSize' => ['fileSize', 1024],
            'imageFormat' => ['imageFormat', 'jpg'],
            'imageWidth' => ['imageWidth', 1920],
            'imageHeight' => ['imageHeight', 1080],
            'primaryColor' => ['primaryColor', '#ffffff'],
            'orderWeight' => ['orderWeight', 0],
            'builtIn' => ['builtIn', false],
            'expirationTime' => ['expirationTime', new \DateTimeImmutable('2024-12-31 23:59:59')],
            'createTime' => ['createTime', new \DateTimeImmutable('2024-01-01 09:00:00')],
        ];
    }
}
