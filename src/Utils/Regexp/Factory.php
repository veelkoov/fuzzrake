<?php

declare(strict_types=1);

namespace App\Utils\Regexp;

use SplObjectStorage;
use TRegx\SafeRegex\Exception\PregException;
use TRegx\SafeRegex\preg;

class Factory
{
    public function __construct(
        private array $commonReplacements,
    ) {
    }

    public function createSet(array $originals, array $variants = []): array
    {
        return array_map(fn (string $key) => $this->create($key, $originals[$key], $variants), array_keys($originals));
    }

    private function create(string $key, string $original, array $variants = []): TrackingRegexp
    {
        $compiled = new SplObjectStorage();

        foreach ($variants as $variant) {
            $compiled[$variant] = $this->compileVariant($original, $variant);
        }

        return new TrackingRegexp($key, $original, $compiled);
    }

    private function compileVariant(string $regexp, Variant $variant): string
    {
        foreach (array_merge($variant->getReplacements(), $this->commonReplacements) as $pattern => $replacement) {
            try {
                $regexp = preg::replace("#$pattern#", $replacement, $regexp);
            } catch (PregException $e) {
                throw new RuntimeRegexpException(previous: $e);
            }
        }

        return "#$regexp#s";
    }
}
