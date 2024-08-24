<?php

namespace App\Tests\DoctrineBehavior;

use App\Entity\Artisan;
use App\Tests\TestUtils\Cases\KernelTestCaseWithEM;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class DoctrineBehaviorTest extends KernelTestCaseWithEM
{
    public function testGetSingleScalarResult(): void
    {
        $creator1 = (new Artisan())->setName('Name 1');
        $creator2 = (new Artisan())->setName('Name 2');

        self::persistAndFlush($creator1, $creator2);

        $query = $this->getEM()->createQuery('
            SELECT a.name
            FROM \App\Entity\Artisan a
            WHERE a.name LIKE :name
        ');

        try {
            $query
                ->setParameter('name', 'No result')
                ->getSingleScalarResult();

            self::fail('Should have thrown an exception');
        } catch (NoResultException $expected) {
            self::assertStringStartsWith('No result was found for query', $expected->getMessage());
        }

        $result = $query
                ->setParameter('name', 'Name 1')
                ->getSingleScalarResult();
        self::assertEquals('Name 1', $result);

        try {
            $query
                ->setParameter('name', 'Name %')
                ->getSingleScalarResult();

            self::fail('Should have thrown an exception');
        } catch (NonUniqueResultException $expected) {
            self::assertStringStartsWith('The query returned multiple rows', $expected->getMessage());
        }
    }
}
