<?php

namespace App\Utils\Mx;

use Psl\Dict;
use Psl\Vec;

class CreatorUrlsSelectionData
{
    /**
     * @var array<string, bool>
     */
    private array $urlIds = [];

    public function set(string $name, bool $value): void
    {
        $this->urlIds[$name] = $value;
    }

    public function get(string $name): bool
    {
        return $this->urlIds[$name] ?? false;
    }

    /**
     * @return string[]
     */
    public function getChosenUrls(): array
    {
        return Vec\keys(Dict\filter($this->urlIds, fn (mixed $value): bool => (bool) $value));
    }
}
