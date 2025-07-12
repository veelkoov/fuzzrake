<?php

declare(strict_types=1);

namespace App\Tests\ByCodeAnalysis;

use App\Data\Definitions\Ages;
use App\Data\Definitions\Fields\Fields;
use App\Tests\TestUtils\Paths;
use App\Utils\Creator\CompletenessCalc;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use Composer\Pcre\Regex;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

#[Small]
class CompletenessCalcTest extends TestCase
{
    public function testAllFieldsCovered(): void
    {
        $contents = new Filesystem()->readFile(Paths::getCompletenessCalcClassPath());
        $wrongCount = [];

        foreach (Fields::all() as $field) {
            if (1 !== Regex::matchAll('~[ :]'.$field->value.'[,;).]~', $contents)->count) {
                $wrongCount[] = $field->value;
            }
        }

        self::assertEmpty($wrongCount, 'Wrong number of appearances: '.implode(', ', $wrongCount));
    }

    public function testEmptyCreatorGetsZero(): void
    {
        $subject = new Creator();

        self::assertSame(0, CompletenessCalc::count($subject));
    }

    #[DataProvider('justRequiredGive50DataProvider')]
    public function testJustRequiredGive50(Ages $ages, bool $nsfwWebsite, bool $nsfwSocial, ?bool $doesNsfw, ?bool $worksWithMinors): void
    {
        $subject = new Creator();
        $this->setRequired($subject, $ages, $nsfwWebsite, $nsfwSocial, $doesNsfw, $worksWithMinors);

        self::assertSame(50, CompletenessCalc::count($subject));
    }

    /**
     * @return list<array{Ages, bool, bool, ?bool, ?bool}>
     */
    public static function justRequiredGive50DataProvider(): array
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

        self::assertSame(50, CompletenessCalc::count($subject));
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
