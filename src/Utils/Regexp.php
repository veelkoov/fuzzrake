<?php

declare(strict_types=1);

namespace App\Utils;

use LogicException;
use SplObjectStorage;

class Regexp
{
    /**
     * @var string
     */
    private $original;

    /**
     * @var SplObjectStorage
     */
    private $compiled;

    /**
     * @param $original
     * @param $compiled
     */
    public function __construct(string $original, SplObjectStorage $compiled)
    {
        $this->original = $original;
        $this->compiled = $compiled;
        $this->compiled->rewind();
    }

    public function matches(string $testedString, RegexpVariant $variant): bool
    {
        $variant = $this->useDefaultVariantWhenNull($variant);

        $result = preg_match($this->compiled[$variant], $testedString);
        $this->throwIfRegexpFailed($variant, $result);

        return 1 === $result;
    }

    public function removeFrom(string $input, RegexpVariant $variant = null): string
    {
        $variant = $this->useDefaultVariantWhenNull($variant);

        $result = preg_replace($this->compiled[$variant], '', $input);
        $this->throwIfRegexpFailed($variant, $result);

        return $result;
    }

    public function getCompiled(RegexpVariant $variant = null): string
    {
        $variant = $this->useDefaultVariantWhenNull($variant);

        return (string) $this->compiled[$variant];
    }

    private function useDefaultVariantWhenNull(RegexpVariant $variant = null): RegexpVariant
    {
        if (null !== $variant) {
            return $variant;
        }

        if (count($this->compiled) > 1) {
            throw new LogicException('Regexp variant selection required');
        }

        return $this->compiled->current();
    }

    private function throwIfRegexpFailed(RegexpVariant $variant, $result): void
    {
        if (null === $result) {
            throw new LogicException("Regexp failed: {$this->compiled[$variant]}", preg_last_error());
        }
    }
}
