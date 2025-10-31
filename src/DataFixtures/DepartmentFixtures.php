<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Tourze\TencentMeetingBundle\Entity\Department;

class DepartmentFixtures extends Fixture
{
    public const DEPARTMENT_REFERENCE_1 = 'department-1';

    public function load(ObjectManager $manager): void
    {
        // 创建测试用的部门数据
        for ($i = 1; $i <= 3; ++$i) {
            $department = new Department();
            $department->setDepartmentId("dept_test_{$i}");
            $department->setName("测试部门{$i}");
            $department->setDescription("这是第{$i}个测试部门");

            // 为第一个部门添加引用
            if (1 === $i) {
                $this->addReference(self::DEPARTMENT_REFERENCE_1, $department);
            }

            $manager->persist($department);
        }

        $manager->flush();
    }
}
