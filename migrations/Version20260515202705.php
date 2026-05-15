<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20260515202705 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Add post edit and vote send timestamp columns.';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE posts ADD COLUMN edited_utc DATETIME DEFAULT NULL');
        $this->addSql('CREATE TEMPORARY TABLE __temp__posts_votes AS SELECT id, is_positive, user_id, post_id FROM posts_votes');
        $this->addSql('DROP TABLE posts_votes');
        $this->addSql('CREATE TABLE posts_votes (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, is_positive BOOLEAN NOT NULL, user_id INTEGER NOT NULL, post_id INTEGER NOT NULL, sent_utc DATETIME NOT NULL, CONSTRAINT FK_E38C21DEA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_E38C21DE4B89032C FOREIGN KEY (post_id) REFERENCES posts (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO posts_votes (id, is_positive, user_id, post_id, sent_utc) SELECT id, is_positive, user_id, post_id, date() FROM __temp__posts_votes');
        $this->addSql('DROP TABLE __temp__posts_votes');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E38C21DE4B89032CA76ED395 ON posts_votes (post_id, user_id)');
        $this->addSql('CREATE INDEX IDX_E38C21DE4B89032C ON posts_votes (post_id)');
        $this->addSql('CREATE INDEX IDX_E38C21DEA76ED395 ON posts_votes (user_id)');
        $this->addSql('CREATE INDEX IDX_E38C21DEFE633B22 ON posts_votes (sent_utc)');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(); // Restore the backup.
    }
}
