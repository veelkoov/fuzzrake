<?php

declare(strict_types=1);

namespace App\Tracking;

use Veelkoov\Debris\StringList;

final readonly class AnalysisFinding
{
    public function __construct(
        public string $matchedText,
        public StringList $offers,
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
