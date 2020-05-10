<?php

declare(strict_types=1);

namespace App\Utils;

use App\Entity\Artisan;
use App\Repository\ArtisanRepository;
use App\Utils\Regexp\Regexp;

class DataQuery
{
    private const BLACKLIST_CHAR = '-';
    private const CMD_START_CHAR = ':';
    private const CMD_ONLY_FEEDBACK_YES = ':YES';

    /**
     * @var Artisan[]
     */
    private array $result = [];

    /**
     * @var string[]
     */
    private array $searchedItems = [];

    /**
     * @var string[]
     */
    private array $blacklistedItems = [];

    /**
     * @var string[] Associative: name = item, value = count
     */
    private array $matchedItems = [];

    /**
     * @var string[]
     */
    private array $errors = [];

    private bool $optOnlyFeedbackYes = false;

    private bool $wasRun = false;

    public function __construct(string $input)
    {
        $items = array_filter(Regexp::split('#\s+#', $input));

        foreach ($items as $item) {
            switch ($item[0]) {
                case self::BLACKLIST_CHAR:
                    $this->blacklistedItems[] = substr($item, 1);
                    break;

                case self::CMD_START_CHAR:
                    $this->command($item);
                    break;

                default:
                    $this->searchedItems[] = $item;
                    break;
            }
        }
    }

    public function run(ArtisanRepository $artisanRepository): void
    {
        $this->result = [];
        $this->matchedItems = [];

        foreach ($artisanRepository->getOthersLike($this->searchedItems) as $artisan) {
            if ($this->artisanMatches($artisan)) {
                $this->result[] = $artisan;
            }
        }

        $this->wasRun = true;
    }

    public function getWasRun(): bool
    {
        return $this->wasRun;
    }

    /**
     * @return Artisan[]
     */
    public function getResult(): array
    {
        return $this->result;
    }

    /**
     * @return string[]
     */
    public function getMatchedItems(): array
    {
        $res = $this->matchedItems;

        arsort($res);

        foreach ($res as $k => &$v) {
            $v = "$k ({$v}×)";
        }

        return $res;
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return string[]
     */
    public function filterList(string $listInput): array
    {
        return $this->filterListInternal($listInput, false);
    }

    private function command($item): void
    {
        switch (strtoupper($item)) {
            case self::CMD_ONLY_FEEDBACK_YES:
                $this->optOnlyFeedbackYes = true;
                break;
            default:
                $this->errors[] = "Unknown command: $item";
        }
    }

    private function artisanMatches(Artisan $artisan): bool
    {
        if ($this->optOnlyFeedbackYes && !$artisan->allowsFeedback()) {
            return false;
        }

        return $this->listMatches($artisan->getOtherFeatures())
            || $this->listMatches($artisan->getOtherOrderTypes())
            || $this->listMatches($artisan->getOtherStyles());
    }

    private function listMatches(string $listInput): bool
    {
        return !empty($this->filterListInternal($listInput, true));
    }

    private function filterListInternal(string $listInput, bool $addMatches): array
    {
        $result = [];

        foreach (StringList::unpack($listInput) as $item) {
            if (!$this->itemMatchesList($item, $this->blacklistedItems) && $this->itemMatchesList($item, $this->searchedItems)) {
                $result[] = $item;

                if ($addMatches && !in_array($item, $this->matchedItems)) {
                    $this->matchedItems[$item] = ($this->matchedItems[$item] ?? 0) + 1;
                }
            }
        }

        return $result;
    }

    private function itemMatchesList(string $item, array $list): bool
    {
        foreach ($list as $blacklistedItem) {
            if (false !== stripos($item, $blacklistedItem)) {
                return true;
            }
        }

        return false;
    }
}
