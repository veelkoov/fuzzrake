<?php

declare(strict_types=1);

namespace App\Twig;

use App\Repository\ArtisanRepository;
use App\Utils\FilterItem;
use App\Utils\Regexp\RegexpFailure;
use App\Utils\Regexp\Utils as Regexp;
use App\Utils\Tracking\Status;
use App\Utils\Utils;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
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
            new TwigFilter('event_url', [Utils::class, 'shortPrintUrl']),
            new TwigFilter('status_text', [Status::class, 'text']),
            new TwigFilter('filterItemsMatching', [$this, 'filterItemsMatchingFilter']),
            new TwigFilter('humanFriendlyRegexp', [$this, 'filterHumanFriendlyRegexp']),
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('getLastSystemUpdateTime', [$this, 'getLastSystemUpdateTimeFunction']),
            new TwigFunction('getLastDataUpdateTime', [$this, 'getLastDataUpdateTimeFunction']),
        ];
    }

    /**
     * @return DateTime
     *
     * @throws NonUniqueResultException
     */
    public function getLastDataUpdateTimeFunction()
    {
        return $this->artisanRepository->getLastCstUpdateTime();
    }

    /**
     * @return DateTime
     *
     * @throws Exception
     */
    public function getLastSystemUpdateTimeFunction()
    {
        return new DateTime(`TZ=UTC git log -n1 --format=%cd --date=local`, new DateTimeZone('UTC'));
    }

    public function otherFilter($primaryList, $otherList)
    {
        $primaryList = str_replace("\n", ', ', $primaryList);

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

    /**
     * @param string $input
     *
     * @return string
     *
     * @throws TplDataException
     * @throws RegexpFailure
     */
    public function sinceFilter(string $input): string
    {
        if ('' === $input) {
            return '';
        }

        if (!Regexp::match('#^(?<year>\d{4})-(?<month>\d{2})$#', $input, $matches)) {
            throw new TplDataException("Invalid 'since' data: '$input''");
        }

        return self::MONTHS[(int) $matches['month']].' '.$matches['year'];
    }

    /**
     * @param FilterItem[] $items
     * @param string       $matchWord
     *
     * @return FilterItem[]
     */
    public function filterItemsMatchingFilter(array $items, string $matchWord): array
    {
        return array_filter($items, function (FilterItem $item) use ($matchWord) {
            return Regexp::match("#$matchWord#i", $item->getLabel());
        });
    }

    /**
     * @param string $input
     *
     * @return string
     *
     * @throws RegexpFailure
     */
    public function filterHumanFriendlyRegexp(string $input): string
    {
        $input = Regexp::replace('#\(\?<!.+?\)#', '', $input);
        $input = Regexp::replace('#\(\?!.+?\)#', '', $input);
        $input = Regexp::replace('#\([^a-z]+?\)#i', '', $input);
        $input = Regexp::replace('#[()?]#', '', $input);
        $input = Regexp::replace('#\[.+?\]#', '', $input);

        return strtoupper($input);
    }
}
