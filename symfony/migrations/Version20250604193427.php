<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20250604193427 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Add creator_id to events.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__events AS SELECT id, timestamp, description, type, no_longer_open_for, now_open_for, tracking_issues, creator_name, checked_urls, new_creators_count, updated_creators_count, reported_updated_creators_count, git_commits FROM events
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE events
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE events (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, timestamp DATETIME NOT NULL, description CLOB NOT NULL, type CLOB NOT NULL, no_longer_open_for CLOB NOT NULL, now_open_for CLOB NOT NULL, tracking_issues BOOLEAN NOT NULL, creator_name CLOB NOT NULL, creator_id CLOB NOT NULL, checked_urls CLOB NOT NULL, new_creators_count INTEGER NOT NULL, updated_creators_count INTEGER NOT NULL, reported_updated_creators_count INTEGER NOT NULL, git_commits CLOB NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO events (id, timestamp, description, type, no_longer_open_for, now_open_for, tracking_issues, creator_name, creator_id, checked_urls, new_creators_count, updated_creators_count, reported_updated_creators_count, git_commits) SELECT id, timestamp, description, type, no_longer_open_for, now_open_for, tracking_issues, creator_name, '', checked_urls, new_creators_count, updated_creators_count, reported_updated_creators_count, git_commits FROM __temp__events
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__events
        SQL);
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(); // Restore the backup.
    }
}
