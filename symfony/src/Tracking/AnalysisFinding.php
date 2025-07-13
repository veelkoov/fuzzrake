<?php

declare(strict_types=1);

namespace App\Tracking;

final readonly class AnalysisFinding
{
    public function __construct(
        public string $matchedText,
        public ?string $offer,
        public ?bool $isOpen,
    ) {
    }

    /**
     * @phpstan-assert-if-true !null $this->offer
     * @phpstan-assert-if-true !null $this->isOpen
     */
    public function isValid(): bool
    {
        return null !== $this->isOpen && null !== $this->offer;
    }
}
