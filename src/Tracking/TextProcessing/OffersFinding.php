<?php

declare(strict_types=1);

namespace App\Tracking\TextProcessing;

use Veelkoov\Debris\Vecs\StringVec;

final readonly class OffersFinding
{
    public function __construct(
        public string $matchedText,
        public StringVec $offers,
        public ?bool $isOpen,
    ) {
    }

    /**
     * @phpstan-assert-if-true !null $this->isOpen
     */
    public function isValid(): bool
    {
        return null !== $this->isOpen && $this->offers->isNotEmpty();
    }
}
