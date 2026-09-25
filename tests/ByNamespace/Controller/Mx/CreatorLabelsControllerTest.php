<?php

declare(strict_types=1);

namespace App\Tests\ByNamespace\Controller\Mx;

use App\Data\LabelType;
use App\Entity\Label;
use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use App\Tests\TestUtils\UserCreator;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
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
        $label1 = new Label($creator1->entity)->setType(LabelType::PRODUCT_VERIFIED);
        self::persistAndFlush($creator1, $label1, $creator2);

        self::haveAnAdminUser();
        self::loginAdminUser();

        self::$client->request('GET', self::getLabelEditPath($creator1, $label1));
        self::assertResponseStatusCodeIs(200);

        self::$client->request('GET', self::getLabelEditPath($creator2, $label1));
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
            'label[type]' => 'PRODUCT_VERIFIED',
            'label[value]' => 'Full plantigrade',
            'label[comment]' => 'Some comment',
            'label[active]' => false,
        ]);
        self::assertCount(1, self::$client->getCrawler()->filter('table tbody tr'));
    }

    public function testRemovalWorks(): void
    {
        $creator = UserCreator::get(true);
        $label = new Label($creator->entity)->setType(LabelType::PRODUCT_VERIFIED);
        self::persistAndFlush($creator, $label);

        self::haveAnAdminUser();
        self::loginAdminUser();

        $crawler = self::$client->request('GET', self::getCreatorLabelsPath($creator));
        self::assertResponseStatusCodeIs(200);
        self::assertCount(1, $crawler->filter('table tbody tr'));

        self::$client->request('GET', self::getLabelEditPath($creator, $label));
        self::assertResponseStatusCodeIs(200);
        self::submitValidForm('Delete', []);
        self::assertCount(0, self::$client->getCrawler()->filter('table tbody tr'));
    }

    /** @throws DateTimeException */
    public function testActivatingDeactivatingLabel(): void
    {
        $nowText = '2026-09-25 05:05 UTC';
        $now = UtcClock::at('2026-09-25 05:05:05');
        self::mockTime($now);

        $creator = UserCreator::get(true);
        $label = new Label($creator->entity)->setType(LabelType::PRODUCT_VERIFIED);
        self::persistAndFlush($creator, $label);

        self::haveAnAdminUser();
        self::loginAdminUser();

        $activationTimeSelector = 'table tbody tr td:nth-child(5)';
        $editLinkSelector = 'table tbody tr td:last-child a';

        $crawler = self::$client->request('GET', self::getCreatorLabelsPath($creator));
        self::assertResponseStatusCodeIs(200);
        self::assertSelectorTextSame($activationTimeSelector, 'Not active');

        self::$client->click($crawler->filter($editLinkSelector)->link());
        self::assertResponseStatusCodeIs(200);
        self::submitValidForm('Save', ['label[active]' => true]);
        self::assertSelectorTextSame($activationTimeSelector, $nowText);

        self::$client->click($crawler->filter($editLinkSelector)->link());
        self::assertResponseStatusCodeIs(200);
        self::submitValidForm('Save', ['label[active]' => false]);
        self::assertSelectorTextSame($activationTimeSelector, 'Not active');
    }
}
