<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\Entity\Artisan;
use App\Utils\Artisan\Field;
use App\Utils\Artisan\Fields;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

class ArtisanFixWip
{
    private Artisan $original;
    private Artisan $fixed;
    private EntityManagerInterface $objectMgr;

    public function __construct(Artisan $fixSubject, EntityManagerInterface $objectMgr)
    {
        $this->original = clone $fixSubject;
        $this->fixed = $fixSubject;
        $this->objectMgr = $objectMgr;
    }

    public function getOriginal(): Artisan
    {
        return $this->original;
    }

    public function getFixed(): Artisan
    {
        return $this->fixed;
    }

    public function reset(): void
    {
        $urls = $this->fixed->getUrls()->toArray(); // Copy
        $this->objectMgr->refresh($this->fixed);

        foreach ($urls as $url) {
            if (!$this->fixed->getUrls()->contains($url)) {
                $url->setArtisan(null);
                $this->objectMgr->remove($url);
            }
        }
    }

    public function resetField(Field $field): void
    {
        if (in_array($field, Fields::urls())) {
            throw new InvalidArgumentException('URL fields not supported by '.__METHOD__);
        }

        $this->fixed->set($field, $this->original->get($field));
    }
}
