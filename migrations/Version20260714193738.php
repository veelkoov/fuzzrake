<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20260714193738 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Add missing unique constraint to the roles table.';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TEMPORARY TABLE __temp__user_roles AS SELECT id, role, user_id FROM user_roles');
        $this->addSql('DROP TABLE user_roles');
        $this->addSql('CREATE TABLE user_roles (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, role VARCHAR(512) NOT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_54FCD59FA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO user_roles (id, role, user_id) SELECT id, role, user_id FROM __temp__user_roles');
        $this->addSql('DROP TABLE __temp__user_roles');
        $this->addSql('CREATE INDEX IDX_54FCD59F57698A6A ON user_roles (role)');
        $this->addSql('CREATE INDEX IDX_54FCD59FA76ED395 ON user_roles (user_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_54FCD59FA76ED39557698A6A ON user_roles (user_id, role)');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(); // Restore the backup.
    }
}
