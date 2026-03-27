<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20260327172446 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Modified type of email/password columns for consistency.';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TEMPORARY TABLE __temp__users AS SELECT id, email, roles, password, is_verified FROM users');
        $this->addSql('DROP TABLE users');
        $this->addSql('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email CLOB NOT NULL, roles CLOB NOT NULL, password CLOB NOT NULL, is_verified BOOLEAN NOT NULL)');
        $this->addSql('INSERT INTO users (id, email, roles, password, is_verified) SELECT id, email, roles, password, is_verified FROM __temp__users');
        $this->addSql('DROP TABLE __temp__users');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON users (email)');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(); // Restore the backup.
    }
}
