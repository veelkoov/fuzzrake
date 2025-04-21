<?php

declare(strict_types=1);

namespace App\Tests\ByCodeAnalysis;

use App\Data\Definitions\Ages;
use App\Data\Definitions\Fields\Fields;
use App\Tests\TestUtils\Paths;
use App\Utils\Creator\CompletenessCalc;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use PHPUnit\Framework\TestCase;

use function Psl\File\read;

/**
 * @small
 */
class CompletenessCalcTest extends TestCase
{
    public function testAllFieldsCovered(): void
    {
        $contents = read(Paths::getCompletenessCalcClassPath());
        $wrongCount = [];

        foreach (Fields::all() as $field) {
            if (1 !== pattern('[ :]'.$field->value.'[,;).]')->count($contents)) {
                $wrongCount[] = $field->value;
            }
        }

        self::assertEmpty($wrongCount, 'Wrong number of appearances: '.implode(', ', $wrongCount));
    }

    public function testEmptyCreatorGetsZero(): void
    {
        $subject = new Creator();

        self::assertEquals(0, CompletenessCalc::count($subject));
    }

    /**
     * @dataProvider justRequiredGive50DataProvider
     */
    public function testJustRequiredGive50(Ages $ages, bool $nsfwWebsite, bool $nsfwSocial, ?bool $doesNsfw, ?bool $worksWithMinors): void
    {
        $subject = new Creator();
        $this->setRequired($subject, $ages, $nsfwWebsite, $nsfwSocial, $doesNsfw, $worksWithMinors);

        self::assertEquals(50, CompletenessCalc::count($subject));
    }

    public function justRequiredGive50DataProvider(): array // @phpstan-ignore-line
    {
        return [
            [Ages::ADULTS, false, false, false, false],
            [Ages::ADULTS, false, false, true,  null],
            [Ages::MINORS, false, false, null,  true],
            [Ages::MINORS, false, true,  null,  null],
        ];
    }

    public function testAllNonRequiredAndAllButOneRequiredCantGetPast50(): void
    {
        $subject = new Creator();
        $this->setAllNonRequired($subject);
        $this->setRequired($subject, Ages::ADULTS, false, false, false, null);

        self::assertLessThanOrEqual(50, CompletenessCalc::count($subject));
    }

    public function testAllButRequiredGive50(): void
    {
        $subject = new Creator();
        $this->setAllNonRequired($subject);

        self::assertEquals(50, CompletenessCalc::count($subject));
    }

    private function setAllNonRequired(Creator $subject): void
    {
        $subject
            ->setEtsyUrl('https://example.com/')
            ->setSince('2022-02')
            ->setProductionModels(['e'])
            ->setStyles(['e'])
            ->setOrderTypes(['e'])
            ->setFeatures(['e'])
            ->setPaymentPlans(['e'])
            ->setPaymentMethods(['e'])
            ->setCurrenciesAccepted(['e'])
            ->setSpeciesDoes(['e'])
            ->setFaqUrl('https://example.com/')
            ->setLanguages(['e'])
            ->setPhotoUrls(['https://example.com/'])
            ->setScritchUrl('https://example.com/')
            ->setFurtrackUrl('https://example.com/');
    }

    private function setRequired(Creator $subject, Ages $ages, bool $nsfwWebsite, bool $nsfwSocial, ?bool $doesNsfw, ?bool $worksWithMinors): void
    {
        $subject
            ->setCreatorId('TEST001')
            ->setCountry('FI')
            ->setAges($ages)
            ->setNsfwWebsite($nsfwWebsite)
            ->setNsfwSocial($nsfwSocial)
            ->setDoesNsfw($doesNsfw)
            ->setWorksWithMinors($worksWithMinors)
            ->setWebsiteUrl('https://example.com/');
    }
}
