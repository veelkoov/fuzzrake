<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20260411140715 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Introduce posts and post votes tables. Add nickname to users.';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE posts (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, posted_utc DATETIME NOT NULL, message CLOB NOT NULL, user_id INTEGER NOT NULL, submission_id INTEGER NOT NULL, parent_id INTEGER DEFAULT NULL, CONSTRAINT FK_885DBAFAA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_885DBAFAE1FD4933 FOREIGN KEY (submission_id) REFERENCES submissions (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_885DBAFA727ACA70 FOREIGN KEY (parent_id) REFERENCES posts (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_885DBAFAA76ED395 ON posts (user_id)');
        $this->addSql('CREATE INDEX IDX_885DBAFAE1FD4933 ON posts (submission_id)');
        $this->addSql('CREATE INDEX IDX_885DBAFA727ACA70 ON posts (parent_id)');
        $this->addSql('CREATE TABLE posts_votes (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, is_positive BOOLEAN NOT NULL, user_id INTEGER NOT NULL, post_id INTEGER NOT NULL, CONSTRAINT FK_E38C21DEA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_E38C21DE4B89032C FOREIGN KEY (post_id) REFERENCES posts (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_E38C21DEA76ED395 ON posts_votes (user_id)');
        $this->addSql('CREATE INDEX IDX_E38C21DE4B89032C ON posts_votes (post_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E38C21DE4B89032CA76ED395 ON posts_votes (post_id, user_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__users AS SELECT id, email, password, contact_permit FROM users');
        $this->addSql('DROP TABLE users');
        $this->addSql('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email CLOB NOT NULL, password CLOB NOT NULL, contact_permit CLOB DEFAULT NULL, nickname CLOB NOT NULL)');
        $this->addSql('INSERT INTO users (id, email, password, contact_permit, nickname) SELECT id, email, password, contact_permit, \'\' FROM __temp__users');
        $this->addSql('DROP TABLE __temp__users');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON users (email)');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(); // Restore the backup.
    }
}
