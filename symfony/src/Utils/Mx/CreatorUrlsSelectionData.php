<?php

declare(strict_types=1);

namespace App\Utils\Mx;

use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Veelkoov\Debris\Maps\StringToBool;
use Veelkoov\Debris\StringSet;

class CreatorUrlsSelectionData
{
    private StringToBool $urlIds;

    public function __construct()
    {
        $this->urlIds = new StringToBool();
    }

    public function set(string $name, bool $value): void
    {
        $this->urlIds->set($name, $value);
    }

    public function get(string $name): bool
    {
        return $this->urlIds->getOrDefault($name, static fn () => false);
    }

    public function getChosenUrls(): StringSet
    {
        return $this->urlIds->filterValues(static fn (bool $value) => $value)->getKeys();
    }

    #[Callback]
    public function validate(ExecutionContextInterface $context, mixed $payload): void
    {
        if ($this->getChosenUrls()->isEmpty()) {
            $context->addViolation('You need to select at least one URL');
        }
    }
}
