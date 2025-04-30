<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250426132032 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Removes old submissions (backups kept separately). Add timestamp and payload columns to the submissions table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DELETE FROM submissions WHERE str_id < '2024-05-19_130727_5899'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__submissions AS SELECT id, str_id, directives, comment FROM submissions
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE submissions
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE submissions (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, str_id CLOB NOT NULL, submitted_at_utc DATETIME NOT NULL, payload CLOB NOT NULL, directives CLOB NOT NULL, comment CLOB NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO submissions (id, str_id, submitted_at_utc, payload, directives, comment) SELECT id, str_id, '1970-01-01 00:00:00', '', directives, comment FROM __temp__submissions
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__submissions
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_3F6169F76810ECF6 ON submissions (str_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(); // Restore the backup.
    }
}
