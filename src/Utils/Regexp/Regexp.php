<?php

declare(strict_types=1);

namespace App\Utils\Regexp;

use App\Utils\StrContext;
use SplObjectStorage;

class Regexp
{
    const CONTEXT_LENGTH = 25;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $original;

    /**
     * @var string[] SplObjectStorage of strings
     */
    private $compiled;

    /**
     * @param string           $id
     * @param string           $original
     * @param SplObjectStorage $compiled
     */
    public function __construct(string $id, string $original, SplObjectStorage $compiled)
    {
        $this->id = $id;
        $this->original = $original;
        $this->compiled = $compiled;
        $this->compiled->rewind();
    }

    /**
     * @param string  $subject
     * @param Variant $variant
     *
     * @return Match|null
     *
     * @throws RegexpFailure
     */
    public function matches(string $subject, Variant $variant): ?Match
    {
        $variant = $this->useDefaultVariantWhenNull($variant);

        if (!Utils::match($this->compiled[$variant], $subject, $matches, 'ID='.$this->id)) {
            return null;
        }

        return new Match($this, $variant, StrContext::createFrom($subject, $matches[0], self::CONTEXT_LENGTH));
    }

    /**
     * @param string       $input
     * @param Variant|null $variant
     *
     * @return string
     *
     * @throws RegexpFailure
     */
    public function removeFrom(string $input, Variant $variant = null): string
    {
        $variant = $this->useDefaultVariantWhenNull($variant);

        return Utils::replace($this->compiled[$variant], '', $input, 'ID='.$this->id);
    }

    /**
     * @param Variant|null $variant
     *
     * @return string
     *
     * @throws RegexpFailure
     */
    public function getCompiled(Variant $variant = null): string
    {
        $variant = $this->useDefaultVariantWhenNull($variant);

        return (string) $this->compiled[$variant];
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param Variant|null $variant
     *
     * @return Variant
     *
     * @throws RegexpFailure
     */
    private function useDefaultVariantWhenNull(Variant $variant = null): Variant
    {
        if (null !== $variant) {
            return $variant;
        }

        if (count($this->compiled) > 1) {
            throw new RegexpFailure('Regexp variant selection required');
        }

        return $this->compiled->current();
    }
}
