<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250410185938 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove obsolete table "kotlin_data". Sync private data table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP TABLE kotlin_data
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__artisans_private_data AS SELECT id, artisan_id, original_contact_info, password, notes FROM artisans_private_data
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE artisans_private_data
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE artisans_private_data (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, artisan_id INTEGER NOT NULL, original_contact_info CLOB NOT NULL, password CLOB NOT NULL, notes CLOB DEFAULT NULL, CONSTRAINT FK_C7CF9EFE5ED3C7B7 FOREIGN KEY (artisan_id) REFERENCES artisans (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO artisans_private_data (id, artisan_id, original_contact_info, password, notes) SELECT id, artisan_id, original_contact_info, password, notes FROM __temp__artisans_private_data
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__artisans_private_data
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_C7CF9EFE5ED3C7B7 ON artisans_private_data (artisan_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(); // Restore the backup.
    }
}
