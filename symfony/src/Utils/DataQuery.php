<?php

declare(strict_types=1);

namespace App\Utils;

use App\Repository\ArtisanRepository;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;

class DataQuery
{
    private const string EXCLUDE_CHAR = '-';
    private const string CMD_START_CHAR = ':';
    private const string CMD_ONLY_FEEDBACK_YES = ':YES';

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
    private array $excludedItems = [];

    /**
     * @var array<string, int> Associative: name = item, value = count
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
        $items = array_filter(pattern('\s+')->split($input));

        foreach ($items as $item) {
            switch ($item[0]) {
                case self::EXCLUDE_CHAR:
                    $this->excludedItems[] = substr((string) $item, 1);
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

        $creators = $artisanRepository->getOthersLike($this->searchedItems);

        foreach (Artisan::wrapAll($creators) as $artisan) {
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
     * @return array<string, int>
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
     * @param list<string> $listInput
     *
     * @return string[]
     */
    public function filterList(array $listInput): array
    {
        return $this->filterListInternal($listInput, false);
    }

    private function command(string $item): void
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

    /**
     * @param list<string> $listInput
     */
    private function listMatches(array $listInput): bool
    {
        return !empty($this->filterListInternal($listInput, true));
    }

    /**
     * @param list<string> $listInput
     *
     * @return string[]
     */
    private function filterListInternal(array $listInput, bool $addMatches): array
    {
        $result = [];

        foreach ($listInput as $item) {
            if (!$this->itemMatchesList($item, $this->excludedItems) && $this->itemMatchesList($item, $this->searchedItems)) {
                $result[] = $item;

                if ($addMatches && !in_array($item, $this->matchedItems)) {
                    $this->matchedItems[$item] = ($this->matchedItems[$item] ?? 0) + 1;
                }
            }
        }

        return $result;
    }

    /**
     * @param string[] $list
     */
    private function itemMatchesList(string $item, array $list): bool
    {
        foreach ($list as $listItem) {
            if (false !== stripos($item, $listItem)) {
                return true;
            }
        }

        return false;
    }
}
