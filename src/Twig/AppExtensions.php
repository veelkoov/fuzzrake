<?php

namespace App\Twig;

use App\Repository\ArtisanRepository;
use DateTime;
use DateTimeZone;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtensions extends AbstractExtension
{
    const MONTHS = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    /**
     * @var ArtisanRepository
     */
    private $artisanRepository;

    public function __construct(ArtisanRepository $artisanRepository)
    {
        $this->artisanRepository = $artisanRepository;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('since', [$this, 'sinceFilter']),
            new TwigFilter('other', [$this, 'otherFilter']),
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('getLastSystemUpdateTime', [$this, 'getLastSystemUpdateTimeFunction']),
            new TwigFunction('getLastDataUpdateTime', [$this, 'getLastDataUpdateTimeFunction']),
        ];
    }

    public function getLastDataUpdateTimeFunction()
    {
        return $this->artisanRepository->getLastCstUpdateTime();
    }

    public function getLastSystemUpdateTimeFunction()
    {
        return new DateTime(`TZ=UTC git log -n1 --format=%cd --date=local`, new DateTimeZone('UTC'));
    }

    public function otherFilter($primaryList, $otherList)
    {
        if ('' !== $otherList) {
            if ('' !== $primaryList) {
                return "$primaryList, Other";
            } else {
                return 'Other';
            }
        } else {
            return $primaryList;
        }
    }

    public function sinceFilter($input)
    {
        if ('' === $input) {
            return '';
        }

        if (!preg_match('#^(?<year>\d{4})-(?<month>\d{2})$#', $input, $zapałki)) {
            throw new TplDataException("Invalid 'since' data: '$input''");
        }

        return self::MONTHS[(int) $zapałki['month']].' '.$zapałki['year'];
    }
}
