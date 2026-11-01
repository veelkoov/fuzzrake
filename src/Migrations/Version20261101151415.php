<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20261101151415 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Empty "latest" migration.';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        // Nothing
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(); // Restore the backup.
    }
}
