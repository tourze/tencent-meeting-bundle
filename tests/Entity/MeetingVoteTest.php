<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TencentMeetingBundle\Entity\Meeting;
use Tourze\TencentMeetingBundle\Entity\MeetingVote;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;

/**
 * @internal
 */
#[CoversClass(MeetingVote::class)]
final class MeetingVoteTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new MeetingVote();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'subject' => ['subject', 'Test Vote Subject'];
        yield 'description' => ['description', 'Test Vote Description'];
        yield 'voteType' => ['voteType', 'single_choice'];
        yield 'status' => ['status', 'draft'];
        yield 'anonymous' => ['anonymous', false];
        yield 'showResult' => ['showResult', true];
        yield 'options' => ['options', [['id' => 1, 'text' => 'Option A']]];
    }

    public function testMeetingVoteCreation(): void
    {
        $config = new TencentMeetingConfig();
        $config->setAppId('test_app');
        $config->setSecretId('test_secret_id');
        $config->setSecretKey('test_secret_key');
        $config->setAuthType('JWT');

        $meeting = new Meeting();
        $meeting->setMeetingId('test_meeting_id');
        $meeting->setMeetingCode('TMT001');
        $meeting->setSubject('Test Meeting');
        $meeting->setStartTime(new \DateTimeImmutable());
        $meeting->setEndTime(new \DateTimeImmutable('+1 hour'));
        $meeting->setConfig($config);

        $vote = new MeetingVote();
        $vote->setMeeting($meeting);
        $vote->setSubject('Test Vote');
        $vote->setDescription('Test Description');
        $vote->setVoteType('single_choice');
        $vote->setStatus('draft');
        $vote->setAnonymous(false);
        $vote->setShowResult(true);
        $vote->setOptions([
            ['id' => 1, 'text' => 'Option A', 'votes' => 0],
            ['id' => 2, 'text' => 'Option B', 'votes' => 0],
        ]);
        $vote->setConfig($config);

        $this->assertInstanceOf(MeetingVote::class, $vote);
        $this->assertSame('Test Vote', $vote->getSubject());
        $this->assertSame('single_choice', $vote->getVoteType());
        $this->assertFalse($vote->isAnonymous());
        $this->assertTrue($vote->isShowResult());
        $this->assertCount(2, $vote->getOptions() ?? []);
    }
}
