<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tourze\TencentMeetingBundle\TencentMeetingBundle;

final class TencentMeetingBundleTest extends TestCase
{
    public function testGetPath(): void
    {
        $bundle = new TencentMeetingBundle();
        $this->assertStringEndsWith('tencent-meeting-bundle/src', $bundle->getPath());
    }
}