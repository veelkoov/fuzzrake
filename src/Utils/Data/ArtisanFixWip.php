<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\Entity\Artisan;
use App\Utils\Artisan\Field;
use App\Utils\Artisan\Fields;
use Doctrine\ORM\EntityManagerInterface;

class ArtisanFixWip
{
    private Artisan $original;
    private Artisan $fixed;
    private EntityManagerInterface $objectMgr;

    public function __construct(Artisan $fixSubject, EntityManagerInterface $objectMgr)
    {
        $this->original = $fixSubject;
        $this->fixed = clone $fixSubject;
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

    public function apply(): void
    {
        foreach (Fields::persisted() as $field) {
            $this->applyField($field);
        }
    }

    public function applyField(Field $field): void
    {
        $this->original->set($field, $this->fixed->get($field));
    }
}
