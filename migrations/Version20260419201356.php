<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20260419201356 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Remove Furry Amino.';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $this->addSql("DELETE FROM creators_urls WHERE type = 'URL_FURRY_AMINO'");
        $this->addSql('DELETE FROM creators_urls_states WHERE creator_url_id NOT IN (SELECT id FROM creators_urls)');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(); // Restore the backup.
    }
}
