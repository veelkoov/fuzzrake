<?php

declare(strict_types=1);

namespace App\Utils;

use App\Entity\Artisan;

class ArtisanImport
{
    /**
     * @var array
     */
    private $rawNewData;

    /**
     * @var Artisan
     */
    private $newData;

    /**
     * @var Artisan
     */
    private $upsertedArtisan;

    /**
     * @var Artisan
     */
    private $originalArtisan;

    /**
     * @var string
     */
    private $providedPasscode;

    /**
     * @var string
     */
    private $newRawDataHash;

    public function __construct(array $rawNewData)
    {
        $this->rawNewData = $rawNewData;
        $this->newRawDataHash = sha1(json_encode($rawNewData));

        $this->providedPasscode = $rawNewData[ArtisanMetadata::getUiFormFieldIndexByPrettyName(ArtisanMetadata::PASSCODE)];
    }

    /**
     * @return Artisan
     */
    public function getNewData(): Artisan
    {
        return $this->newData;
    }

    /**
     * @param Artisan $newData
     */
    public function setNewData(Artisan $newData): void
    {
        $this->newData = $newData;
    }

    /**
     * @return Artisan
     */
    public function getUpsertedArtisan(): Artisan
    {
        return $this->upsertedArtisan;
    }

    /**
     * @param Artisan $upsertedArtisan
     */
    public function setUpsertedArtisan(Artisan $upsertedArtisan): void
    {
        $this->upsertedArtisan = $upsertedArtisan;
    }

    /**
     * @return Artisan
     */
    public function getOriginalArtisan(): Artisan
    {
        return $this->originalArtisan;
    }

    /**
     * @param Artisan $originalArtisan
     */
    public function setOriginalArtisan(Artisan $originalArtisan): void
    {
        $this->originalArtisan = $originalArtisan;
    }

    /**
     * @return string
     */
    public function getProvidedPasscode(): string
    {
        return $this->providedPasscode;
    }

    /**
     * @return string
     */
    public function getNewRawDataHash(): string
    {
        return $this->newRawDataHash;
    }

    /**
     * @return array
     */
    public function getRawNewData(): array
    {
        return $this->rawNewData;
    }
}
