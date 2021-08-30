<?php

declare(strict_types=1);

namespace App\Utils\IuSubmissions;

use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Data\ArtisanChanges;
use App\Utils\Data\Diff;
use App\Utils\StrUtils;

class ImportItem
{
    private ?Diff $diff = null;

    /**
     * @var string[]
     */
    private array $replaced = [];

    public function __construct(
        private IuSubmission $iuSubmission,
        private ArtisanChanges $input,
        private ArtisanChanges $entity,
    ) {
    }

    public function getOriginalInput(): Artisan
    {
        return $this->input->getSubject();
    }

    public function getFixedInput(): Artisan
    {
        return $this->input->getChanged();
    }

    public function getFixedEntity(): Artisan
    {
        return $this->entity->getChanged();
    }

    public function getOriginalEntity(): Artisan
    {
        return $this->entity->getSubject();
    }

    public function getInput(): ArtisanChanges
    {
        return $this->input;
    }

    public function getEntity(): ArtisanChanges
    {
        return $this->entity;
    }

    public function getIdStrSafe(): string
    {
        return StrUtils::artisanNamesSafeForCli($this->getOriginalInput(), $this->getFixedEntity(), $this->getOriginalEntity())
            .' ['.$this->iuSubmission->getTimestamp()->format('Y-m-d H:i').']';
    }

    public function getNamesStrSafe(): string
    {
        return StrUtils::artisanNamesSafeForCli($this->getOriginalEntity(), $this->getFixedEntity());
    }

    public function getMakerId(): string
    {
        return $this->entity->getChanged()->getMakerId();
    }

    public function getId(): string
    {
        return $this->iuSubmission->getId();
    }

    public function getProvidedPassword(): string
    {
        return $this->input->getChanged()->getPassword();
    }

    public function getExpectedPassword(): string
    {
        return $this->entity->getSubject()->getPassword();
    }

    public function getDiff(): ?Diff
    {
        return $this->diff;
    }

    public function calculateDiff(): void
    {
        $this->diff = new Diff($this->getOriginalEntity(), $this->getFixedEntity());
    }

    public function addReplaced(ImportItem $replaced): void
    {
        $this->replaced[] = $replaced->getIdStrSafe();
    }

    /**
     * @return string[]
     */
    public function getReplaced(): array
    {
        return $this->replaced;
    }
}
