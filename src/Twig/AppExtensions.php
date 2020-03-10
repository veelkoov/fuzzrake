<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace App\Twig;

use App\Repository\ArtisanCommissionsStatusRepository;
use App\Service\HostsService;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\DateTimeUtils;
use App\Utils\FilterItem;
use App\Utils\Regexp\Regexp;
use App\Utils\StringList;
use App\Utils\StrUtils;
use App\Utils\Tracking\Status;
use DateTimeInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtensions extends AbstractExtension
{
    private ArtisanCommissionsStatusRepository $acsRepository;
    private HostsService $hostsService;

    public function __construct(ArtisanCommissionsStatusRepository $acsRepository, HostsService $hostsService)
    {
        $this->acsRepository = $acsRepository;
        $this->hostsService = $hostsService;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('list', [$this, 'listFilter']),
            new TwigFilter('other', [$this, 'otherFilter']),
            new TwigFilter('nulldate', [$this, 'nulldateFilter']),
            new TwigFilter('event_url', [StrUtils::class, 'shortPrintUrl']),
            new TwigFilter('status_text', [Status::class, 'text']),
            new TwigFilter('filterItemsMatching', [$this, 'filterItemsMatchingFilter']),
            new TwigFilter('humanFriendlyRegexp', [$this, 'filterHumanFriendlyRegexp']),
            new TwigFilter('highlightQuery', [$this, 'filterHighlightQuery']),
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('getLastSystemUpdateTimeUtcStr', [$this, 'getLastSystemUpdateTimeUtcStrFunction']),
            new TwigFunction('getLastDataUpdateTimeUtcStr', [$this, 'getLastDataUpdateTimeUtcStrFunction']),
            new TwigFunction('isDevMachine', [$this, 'isDevMachineFunction']),
            new TwigFunction('isProduction', [$this, 'isProductionFunction']),
        ];
    }

    public function isDevMachineFunction(): bool
    {
        return $this->hostsService->isDevMachine();
    }

    public function isProductionFunction(): bool
    {
        return $this->hostsService->isProduction();
    }

    public function getLastDataUpdateTimeUtcStrFunction(): string
    {
        return $this->acsRepository->getLastCstUpdateTimeAsString();
    }

    public function getLastSystemUpdateTimeUtcStrFunction(): string
    {
        try {
            return DateTimeUtils::getUtcAt(`TZ=UTC git log -n1 --format=%cd --date=local`)->format('Y-m-d H:i');
        } catch (DateTimeException $e) {
            return 'unknown/error';
        }
    }

    public function otherFilter($primaryList, $otherList): string
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

    public function listFilter(string $input): array
    {
        return StringList::unpack($input);
    }

    public function nulldateFilter($input, string $format = 'Y-m-d H:i'): string
    {
        if (null === $input) {
            return 'never';
        } elseif ($input instanceof DateTimeInterface) {
            return $input->format($format);
        } else {
            return 'unknown/error';
        }
    }

    public function filterItemsMatchingFilter(array $items, string $matchWord): array
    {
        return array_filter($items, function (FilterItem $item) use ($matchWord) {
            return Regexp::match("#$matchWord#i", $item->getLabel());
        });
    }

    public function filterHumanFriendlyRegexp(string $input): string
    {
        $input = Regexp::replace('#\(\?<!.+?\)#', '', $input);
        $input = Regexp::replace('#\(\?!.+?\)#', '', $input);
        $input = Regexp::replace('#\([^a-z]+?\)#i', '', $input);
        $input = Regexp::replace('#[()?]#', '', $input);
        $input = Regexp::replace('#\[.+?\]#', '', $input);

        return strtoupper($input);
    }

    public function filterHighlightQuery(string $input, array $searchedItems): string
    {
        $subjectItems = StringList::unpack($input);
        $result = [];

        foreach ($subjectItems as $subjectItem) {
            foreach ($searchedItems as $searchedItem) {
                if (false !== stripos($subjectItem, $searchedItem)) {
                    $result[] = $subjectItem;
                    break;
                }
            }
        }

        return implode(', ', $result);
    }
}
