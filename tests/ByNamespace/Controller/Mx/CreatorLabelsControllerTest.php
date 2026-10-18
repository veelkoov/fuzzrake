<?php

declare(strict_types=1);

namespace App\Tests\ByNamespace\Controller\Mx;

use App\Data\LabelType;
use App\Entity\CreatorLabel;
use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use App\Tests\TestUtils\UserCreator;
use PHPUnit\Framework\Attributes\Medium;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

#[Medium]
class CreatorLabelsControllerTest extends FuzzrakeWebTestCase
{
    use ClockSensitiveTrait;

    public function testCantAccessOtherCreatorsLabel(): void
    {
        $creator1 = UserCreator::get(true);
        $creator2 = UserCreator::get(true);
        $label1 = new CreatorLabel($creator1->entity)->setType(LabelType::PRODUCT_VERIFIED);
        self::persistAndFlush($creator1, $label1, $creator2);

        self::haveAnAdminUser();
        self::loginAdminUser();

        self::$client->request('GET', self::getCreatorLabelEditPath($creator1, $label1));
        self::assertResponseStatusCodeIs(200);

        self::$client->request('GET', self::getCreatorLabelEditPath($creator2, $label1));
        self::assertResponseStatusCodeIs(404);
    }

    public function testAddingAndListWorks(): void
    {
        $creator = UserCreator::get(true);
        self::persistAndFlush($creator);

        self::haveAnAdminUser();
        self::loginAdminUser();

        $crawler = self::$client->request('GET', self::getCreatorLabelsPath($creator));
        self::assertResponseStatusCodeIs(200);
        self::assertCount(0, $crawler->filter('table tbody tr'));

        self::submitValidForm('Save', [
            'creator_label[type]' => 'PRODUCT_VERIFIED',
            'creator_label[value]' => 'Full plantigrade',
            'creator_label[comment]' => 'Some comment',
        ]);
        self::assertCount(1, self::$client->getCrawler()->filter('table tbody tr'));
    }

    public function testRemovalWorks(): void
    {
        $creator = UserCreator::get(true);
        $label = new CreatorLabel($creator->entity)->setType(LabelType::PRODUCT_VERIFIED);
        self::persistAndFlush($creator, $label);

        self::haveAnAdminUser();
        self::loginAdminUser();

        $crawler = self::$client->request('GET', self::getCreatorLabelsPath($creator));
        self::assertResponseStatusCodeIs(200);
        self::assertCount(1, $crawler->filter('table tbody tr'));

        self::$client->request('GET', self::getCreatorLabelEditPath($creator, $label));
        self::assertResponseStatusCodeIs(200);
        self::submitValidForm('Delete', []);
        self::assertCount(0, self::$client->getCrawler()->filter('table tbody tr'));
    }
}
