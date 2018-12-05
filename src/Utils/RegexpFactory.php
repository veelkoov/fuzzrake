<?php

declare(strict_types=1);

namespace App\Utils;

use SplObjectStorage;

class RegexpFactory
{
    /**
     * @var array
     */
    private $commonReplacements;

    public function __construct(array $commonReplacements)
    {
        $this->commonReplacements = $commonReplacements;
    }

    public function createSet(array $originals, array $variants = []): array
    {
        return array_map(function (string $original) use ($variants) {
            return $this->create($original, $variants);
        }, $originals);
    }

    public function create(string $original, array $variants = []): Regexp
    {
        $compiled = new SplObjectStorage();

        foreach ($variants as $variant) {
            $compiled[$variant] = $this->compileVariant($original, $variant);
        }

        return new Regexp($original, $compiled);
    }

    private function compileVariant(string $regexp, RegexpVariant $variant): string
    {
        $result = $regexp;

        foreach (array_merge($variant->getReplacements(), $this->commonReplacements) as $needle => $replacement) {
            $result = preg_replace("#$needle#", $replacement, $result);
        }

        return "#$result#s";
    }
}
