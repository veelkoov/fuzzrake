<?php

declare(strict_types=1);

namespace App\Twig;

use App\Repository\ArtisanRepository;
use App\Utils\FilterItem;
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
            new TwigFilter('event_url', [$this, 'eventUrlFilter']),
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

    public function eventUrlFilter(string $originalUrl): string
    {
        $url = preg_replace('#^https?://(www\.)?#', '', $originalUrl);
        $url = preg_replace('/#profile/', '', $url);
        $url = str_replace('/user/', '/u/', $url);
        $url = str_replace('/journal/', '/j/', $url);

        if (strlen($url) > 50) {
            $url = substr($url, 0, 40).'...';
        }

        return $url;
    }

    /**
     * @param string $input
     *
     * @return string
     *
     * @throws TplDataException
     */
    public function sinceFilter(string $input): string
    {
        if ('' === $input) {
            return '';
        }

        if (!preg_match('#^(?<year>\d{4})-(?<month>\d{2})$#', $input, $matches)) {
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
            return 1 === preg_match("#$matchWord#i", $item->getLabel());
        });
    }

    public function filterHumanFriendlyRegexp(string $input): string
    {
        $input = preg_replace('#\(\?<!.+?\)#', '', $input);
        $input = preg_replace('#\(\?!.+?\)#', '', $input);
        $input = preg_replace('#\([^a-z]+?\)#i', '', $input);
        $input = preg_replace('#[()?]#', '', $input);
        $input = preg_replace('#\[.+?\]#', '', $input);

        return strtoupper($input);
    }
}
