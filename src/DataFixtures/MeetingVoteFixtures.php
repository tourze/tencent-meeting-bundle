<?php

namespace Tourze\TencentMeetingBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\TencentMeetingBundle\Entity\Meeting;
use Tourze\TencentMeetingBundle\Entity\MeetingVote;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;
use Tourze\TencentMeetingBundle\Enum\MeetingStatus;

class MeetingVoteFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // 使用已存在的配置实体
        /** @var TencentMeetingConfig $config */
        $config = $this->getReference(TencentMeetingConfigFixtures::CONFIG_REFERENCE_1, TencentMeetingConfig::class);

        // 创建测试用的会议实体
        $meeting = new Meeting();
        $meeting->setMeetingId('meeting_test_vote_001');
        $meeting->setMeetingCode('MVT001');
        $meeting->setSubject('测试会议投票');
        $meeting->setStartTime(new \DateTimeImmutable('2024-01-01 10:00:00'));
        $meeting->setEndTime(new \DateTimeImmutable('2024-01-01 11:00:00'));
        $meeting->setStatus(MeetingStatus::SCHEDULED);
        $meeting->setDuration(60);
        $meeting->setConfig($config);
        $meeting->setUserId('test_user_001');
        $manager->persist($meeting);

        // 创建测试用的会议投票数据
        $voteTypes = ['single_choice', 'multiple_choice', 'yes_no'];
        $voteStatuses = ['draft', 'active', 'closed'];

        for ($i = 0; $i < 3; ++$i) {
            $meetingVote = new MeetingVote();

            $meetingVote->setMeeting($meeting);
            $meetingVote->setSubject('测试投票 ' . ($i + 1));
            $meetingVote->setDescription('这是第 ' . ($i + 1) . ' 个测试投票');
            $meetingVote->setVoteType($voteTypes[$i]);
            $meetingVote->setStatus($voteStatuses[$i]);
            $meetingVote->setAnonymous(1 === $i);
            $meetingVote->setShowResult(true);
            $meetingVote->setStartTime(new \DateTimeImmutable('2024-01-01 10:00:00'));
            $meetingVote->setEndTime(new \DateTimeImmutable('2024-01-01 10:30:00'));
            $meetingVote->setTotalVotes($i * 10);
            $meetingVote->setOptions([
                ['id' => 1, 'text' => '选项A', 'votes' => $i * 3],
                ['id' => 2, 'text' => '选项B', 'votes' => $i * 2],
                ['id' => 3, 'text' => '选项C', 'votes' => $i * 5],
            ]);
            $meetingVote->setResults([
                'option_1' => $i * 3,
                'option_2' => $i * 2,
                'option_3' => $i * 5,
                'total_votes' => $i * 10,
            ]);
            $meetingVote->setCreatorUserId('user_' . ($i + 1));
            $meetingVote->setConfig($config);

            $manager->persist($meetingVote);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            TencentMeetingConfigFixtures::class,
        ];
    }
}
