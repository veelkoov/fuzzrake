<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20260401110002 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Remove legacy string ID from submissions.';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TEMPORARY TABLE __temp__submissions AS SELECT id, submitted_at_utc, payload, directives, comment, status, is_update, owner_id, creator_id FROM submissions');
        $this->addSql('DROP TABLE submissions');
        $this->addSql('CREATE TABLE submissions (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, submitted_at_utc DATETIME NOT NULL, payload CLOB NOT NULL, directives CLOB NOT NULL, comment CLOB NOT NULL, status VARCHAR(17) NOT NULL, is_update BOOLEAN NOT NULL, owner_id INTEGER DEFAULT NULL, creator_id INTEGER DEFAULT NULL, CONSTRAINT FK_3F6169F77E3C61F9 FOREIGN KEY (owner_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3F6169F761220EA6 FOREIGN KEY (creator_id) REFERENCES creators (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO submissions (id, submitted_at_utc, payload, directives, comment, status, is_update, owner_id, creator_id) SELECT id, submitted_at_utc, payload, directives, comment, status, is_update, owner_id, creator_id FROM __temp__submissions');
        $this->addSql('DROP TABLE __temp__submissions');
        $this->addSql('CREATE INDEX IDX_3F6169F761220EA6 ON submissions (creator_id)');
        $this->addSql('CREATE INDEX IDX_3F6169F77E3C61F9 ON submissions (owner_id)');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(); // Restore the backup.
    }
}
