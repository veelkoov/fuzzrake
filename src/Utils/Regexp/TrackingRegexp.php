<?php

declare(strict_types=1);

namespace App\Utils\Regexp;

use App\Utils\StrContext\StrContextUtils;
use App\Utils\Tracking\TrackingMatch;
use SplObjectStorage;
use TRegx\SafeRegex\Exception\PregException;
use TRegx\SafeRegex\preg;

class TrackingRegexp
{
    private const CONTEXT_LENGTH = 100;

    public function __construct(
        private string $id,
        private string $original,
        private SplObjectStorage $compiled,
    ) {
        $this->compiled->rewind();
    }

    public function matches(string $subject, Variant $variant): ?TrackingMatch
    {
        $variant = $this->useDefaultVariantWhenNull($variant);

        try {
            if (!preg::match($this->compiled[$variant], $subject, $matches)) {
                return null;
            }
        } catch (PregException $e) {
            throw new RuntimeRegexpException(previous: $e);
        }

        return new TrackingMatch($this, $variant, StrContextUtils::extractFrom($subject, $matches[0], self::CONTEXT_LENGTH));
    }

    public function removeFrom(string $input, Variant $variant = null): string
    {
        $variant = $this->useDefaultVariantWhenNull($variant);

        try {
            return preg::replace($this->compiled[$variant], '', $input);
        } catch (PregException $e) {
            throw new RuntimeRegexpException(previous: $e);
        }
    }

    public function getCompiled(Variant $variant = null): string
    {
        $variant = $this->useDefaultVariantWhenNull($variant);

        return (string) $this->compiled[$variant];
    }

    public function getId(): string
    {
        return $this->id;
    }

    private function useDefaultVariantWhenNull(Variant $variant = null): Variant
    {
        if (null !== $variant) {
            return $variant;
        }

        if (count($this->compiled) > 1) {
            throw new RuntimeRegexpException('Regexp variant selection required');
        }

        /* @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->compiled->current();
    }
}
