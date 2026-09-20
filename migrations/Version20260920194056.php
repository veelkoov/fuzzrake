<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20260920194056 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Add labels table.';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE labels (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, added_at_utc DATETIME NOT NULL, subject VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, value CLOB NOT NULL, comment CLOB NOT NULL, creator_id INTEGER NOT NULL, CONSTRAINT FK_B5D1021161220EA6 FOREIGN KEY (creator_id) REFERENCES creators (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_B5D1021161220EA6 ON labels (creator_id)');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(); // Restore the backup.
    }
}
