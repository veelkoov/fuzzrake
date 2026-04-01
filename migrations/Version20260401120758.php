<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20260401120758 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Migrate roles to a dedicated table. Add the creator role.';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user_roles (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, role VARCHAR(512) NOT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_54FCD59FA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');

        $this->addSql('INSERT INTO user_roles (user_id, role) SELECT id, \'ROLE_ADMIN\' FROM users WHERE roles LIKE \'%"ROLE_ADMIN"%\'');
        $this->addSql('INSERT INTO user_roles (user_id, role) SELECT id, \'ROLE_VERIFIED\' FROM users WHERE is_verified');
        $this->addSql('INSERT INTO user_roles (user_id, role) SELECT id, \'ROLE_CREATOR\' FROM users WHERE roles NOT LIKE \'%"ROLE_ADMIN"%\'');

        $this->addSql('CREATE INDEX IDX_54FCD59FA76ED395 ON user_roles (user_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__users AS SELECT id, email, password, contact_permit FROM users');
        $this->addSql('DROP TABLE users');
        $this->addSql('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email CLOB NOT NULL, password CLOB NOT NULL, contact_permit CLOB DEFAULT NULL)');
        $this->addSql('INSERT INTO users (id, email, password, contact_permit) SELECT id, email, password, contact_permit FROM __temp__users');
        $this->addSql('DROP TABLE __temp__users');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON users (email)');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(); // Restore the backup.
    }
}
