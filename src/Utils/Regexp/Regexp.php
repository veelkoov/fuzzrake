<?php

declare(strict_types=1);

namespace App\Utils\Regexp;

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
     * @var SplObjectStorage
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
     * @param string  $testedString
     * @param Variant $variant
     *
     * @return Match|null
     *
     * @throws RegexpFailure
     */
    public function matches(string $testedString, Variant $variant): ?Match
    {
        $variant = $this->useDefaultVariantWhenNull($variant);

        $result = preg_match($this->compiled[$variant], $testedString, $matches);
        $this->throwIfRegexpFailed($variant, $result);

        if (0 === $result) {
            return null;
        }

        return new Match($this, $variant, $matches[0], $this->getContext($testedString, $matches[0]));
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

        $result = preg_replace($this->compiled[$variant], '', $input);
        $this->throwIfRegexpFailed($variant, $result);

        return $result;
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

    /**
     * @param Variant $variant
     * @param mixed   $result
     *
     * @throws RegexpFailure
     */
    private function throwIfRegexpFailed(Variant $variant, $result): void
    {
        if (null === $result || false === $result) {
            throw new RegexpFailure('Regexp '.$this->id.' failed: '.$this->compiled[$variant], preg_last_error());
        }
    }

    private function getContext(string $wholeInput, string $match): string
    {
        $start = max(0, strpos($wholeInput, $match) - self::CONTEXT_LENGTH);

        return substr($wholeInput, $start, strlen($match) + self::CONTEXT_LENGTH);
    }
}
