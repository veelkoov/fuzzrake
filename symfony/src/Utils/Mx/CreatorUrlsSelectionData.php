<?php

declare(strict_types=1);

namespace App\Utils\Mx;

use Psl\Dict;
use Psl\Vec;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CreatorUrlsSelectionData
{
    /**
     * @var array<string, bool>
     */
    private array $urlIds = [];

    public function set(string $name, bool $value): void
    {
        $this->urlIds[$name] = $value;
    }

    public function get(string $name): bool
    {
        return $this->urlIds[$name] ?? false;
    }

    /**
     * @return string[]
     */
    public function getChosenUrls(): array
    {
        return Vec\keys(Dict\filter($this->urlIds));
    }

    #[Callback]
    public function validate(ExecutionContextInterface $context, mixed $payload): void
    {
        if ([] === $this->getChosenUrls()) {
            $context->addViolation('You need to select at least one URL');
        }
    }
}
