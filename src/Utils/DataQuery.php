<?php

declare(strict_types=1);

namespace App\Utils;

use App\Repository\CreatorRepository;
use App\Utils\Collections\Arrays;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use Composer\Pcre\Preg;

class DataQuery
{
    private const string EXCLUDE_CHAR = '-';
    private const string CMD_START_CHAR = ':';
    private const string CMD_ONLY_FEEDBACK_YES = ':YES';

    /**
     * @var list<Creator>
     */
    public private(set) array $result = [];

    /**
     * @var list<string>
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
     * @var list<string>
     */
    public private(set) array $errors = [];

    private bool $optOnlyFeedbackYes = false;

    public private(set) bool $wasRun = false;

    public function __construct(string $input)
    {
        $items = Arrays::nonEmptyStrings(Preg::split('~\s+~', $input));

        foreach ($items as $item) {
            switch ($item[0]) {
                case self::EXCLUDE_CHAR:
                    $this->excludedItems[] = substr($item, 1);
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

    public function run(CreatorRepository $creatorRepository): void
    {
        $this->result = [];
        $this->matchedItems = [];

        $creators = $creatorRepository->getWithOtherItemsLikePaged($this->searchedItems);

        foreach ($creators as $creatorE) {
            $creator = Creator::wrap($creatorE);

            if ($this->creatorMatches($creator)) {
                $this->result[] = $creator;
            }
        }

        $this->wasRun = true;
    }

    /**
     * @return array<string, string>
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

    private function creatorMatches(Creator $creator): bool
    {
        if ($this->optOnlyFeedbackYes && !$creator->allowsFeedback()) {
            return false;
        }

        return $this->listMatches($creator->getOtherFeatures())
            || $this->listMatches($creator->getOtherOrderTypes())
            || $this->listMatches($creator->getOtherStyles());
    }

    /**
     * @param list<string> $listInput
     */
    private function listMatches(array $listInput): bool
    {
        return [] !== $this->filterListInternal($listInput, true);
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

                if ($addMatches && !array_key_exists($item, $this->matchedItems)) {
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
        return array_any($list, static fn (string $listItem) => false !== stripos($item, $listItem));
    }
}
