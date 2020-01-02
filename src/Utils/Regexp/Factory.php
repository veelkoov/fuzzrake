<?php

declare(strict_types=1);

namespace App\Utils\Regexp;

use App\Utils\Regexp\Utils as RegexpUtils;
use SplObjectStorage;

class Factory
{
    private array $commonReplacements;

    public function __construct(array $commonReplacements)
    {
        $this->commonReplacements = $commonReplacements;
    }

    public function createSet(array $originals, array $variants = []): array
    {
        return array_map(function (string $key) use ($originals, $variants) {
            return $this->create($key, $originals[$key], $variants);
        }, array_keys($originals));
    }

    private function create(string $key, string $original, array $variants = []): Regexp
    {
        $compiled = new SplObjectStorage();

        foreach ($variants as $variant) {
            $compiled[$variant] = $this->compileVariant($original, $variant);
        }

        return new Regexp($key, $original, $compiled);
    }

    private function compileVariant(string $regexp, Variant $variant): string
    {
        $result = $regexp;

        foreach (array_merge($variant->getReplacements(), $this->commonReplacements) as $needle => $replacement) {
            $result = RegexpUtils::replace("#$needle#", $replacement, $result);
        }

        return "#$result#s";
    }
}
