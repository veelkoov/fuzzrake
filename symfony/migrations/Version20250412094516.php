<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20250412094516 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Remove obsolete table "kotlin_data". Rename "creators_private_data" table, columns, remove unused column "notes".';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE creators_private_data (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, creator_id INTEGER NOT NULL, email_address CLOB NOT NULL, password CLOB NOT NULL, CONSTRAINT FK_F17D369661220EA6 FOREIGN KEY (creator_id) REFERENCES artisans (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO creators_private_data (id, creator_id, email_address, password) SELECT id, artisan_id, original_contact_info, password FROM artisans_private_data
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_F17D369661220EA6 ON creators_private_data (creator_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE artisans_private_data
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE kotlin_data
        SQL);
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(); // Restore the backup.
    }
}
