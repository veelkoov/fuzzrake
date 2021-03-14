<?php

declare(strict_types=1);

namespace App\Tests\TestUtils;

use App\Utils\Traits\UtilityClass;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool as OrmSchemaTool;

class SchemaTool
{
    use UtilityClass;

    public static function resetOn(EntityManagerInterface $entityManager): void
    {
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new OrmSchemaTool($entityManager);
        $schemaTool->dropSchema($metadata);
        $schemaTool->updateSchema($metadata);
    }
}
