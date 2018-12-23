<?php

declare(strict_types=1);

namespace App\Utils;

use App\Entity\Artisan;

class ArtisanImport
{
    /**
     * @var Artisan
     */
    private $newOriginalData;

    /**
     * @var Artisan
     */
    private $newFixedData;

    /**
     * @var Artisan
     */
    private $upsertedArtisan;

    /**
     * @var Artisan
     */
    private $originalArtisan;

    /**
     * @return Artisan
     */
    public function getNewOriginalData(): Artisan
    {
        return $this->newOriginalData;
    }

    /**
     * @param Artisan $newOriginalData
     */
    public function setNewOriginalData(Artisan $newOriginalData): void
    {
        $this->newOriginalData = $newOriginalData;
    }

    /**
     * @return Artisan
     */
    public function getNewFixedData(): Artisan
    {
        return $this->newFixedData;
    }

    /**
     * @param Artisan $newFixedData
     */
    public function setNewFixedData(Artisan $newFixedData): void
    {
        $this->newFixedData = $newFixedData;
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
}
