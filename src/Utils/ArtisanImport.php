<?php

declare(strict_types=1);

namespace App\Utils;

use App\Entity\Artisan;
use App\Utils\ArtisanFields as Fields;
use DateTime;
use DateTimeZone;
use Exception;

class ArtisanImport
{
    /**
     * @var DateTime
     */
    private $timestamp;

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

    /**
     * @param array $rawNewData
     *
     * @throws Exception
     */
    public function __construct(array $rawNewData)
    {
        $this->rawNewData = $rawNewData;
        $this->setTimestamp($rawNewData);
        $this->setNewRawDataHash($rawNewData);

        $this->providedPasscode = $rawNewData[Fields::uiFormIndex(Fields::PASSCODE)];
    }

    public function getNewData(): Artisan
    {
        return $this->newData;
    }

    public function setNewData(Artisan $newData): void
    {
        $this->newData = $newData;
    }

    public function getUpsertedArtisan(): Artisan
    {
        return $this->upsertedArtisan;
    }

    public function setUpsertedArtisan(Artisan $upsertedArtisan): void
    {
        $this->upsertedArtisan = $upsertedArtisan;
    }

    public function getOriginalArtisan(): Artisan
    {
        return $this->originalArtisan;
    }

    public function setOriginalArtisan(Artisan $originalArtisan): void
    {
        $this->originalArtisan = $originalArtisan;
    }

    public function getProvidedPasscode(): string
    {
        return $this->providedPasscode;
    }

    public function getNewRawDataHash(): string
    {
        return $this->newRawDataHash;
    }

    public function getRawNewData(): array
    {
        return $this->rawNewData;
    }

    public function getIdStringSafe(): string
    {
        return Utils::artisanNamesSafe($this->getNewData(), $this->getUpsertedArtisan(), $this->getOriginalArtisan())
            .' ['.$this->timestamp->format(DATE_ISO8601).']';
    }

    /**
     * It looks like Google Forms changes timestamp's timezone, so let's get rid of it for the sake of hash calculation.
     *
     * @param array $rawNewData
     *
     * @throws Exception
     */
    private function setTimestamp(array $rawNewData): void
    {
        $this->timestamp = new DateTime($rawNewData[Fields::uiFormIndex(Fields::TIMESTAMP)], new DateTimeZone('UTC'));
    }

    private function setNewRawDataHash(array $rawNewData)
    {
        $rawNewData[Fields::uiFormIndex(Fields::TIMESTAMP)] = null;
        $this->newRawDataHash = sha1(json_encode($rawNewData));
    }
}
