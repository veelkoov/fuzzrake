<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20260607141035 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Add topics last read timestamps.';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE topics_reads (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, last_read DATETIME NOT NULL, user_id INTEGER NOT NULL, topic_id INTEGER NOT NULL, CONSTRAINT FK_E962E01DA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_E962E01D1F55203D FOREIGN KEY (topic_id) REFERENCES posts (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E962E01DA76ED3951F55203D ON topics_reads (user_id, topic_id)');
        $this->addSql('CREATE INDEX IDX_E962E01DA76ED395 ON topics_reads (user_id)');
        $this->addSql('CREATE INDEX IDX_E962E01D1F55203D ON topics_reads (topic_id)');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(); // Restore the backup.
    }
}
