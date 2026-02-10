<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20260210131036 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Introduce users table; migrate login data.';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL, password VARCHAR(255) NOT NULL, creator_id INTEGER DEFAULT NULL, CONSTRAINT FK_8D93D64961220EA6 FOREIGN KEY (creator_id) REFERENCES creators (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON user (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D64961220EA6 ON user (creator_id)');

        $this->addSql('DELETE FROM creators_private_data WHERE creator_id IN (SELECT id FROM creators WHERE inactive_reason LIKE \'Duplicate; replaced by%\')');
        $this->addSql('UPDATE creators_private_data SET email_address = concat(\'missing-email-\', id) WHERE email_address = \'\'');
        $this->addSql('INSERT INTO user (email, roles, password, creator_id) SELECT email_address, \'[]\', password, creator_id FROM creators_private_data');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(); // Restore the backup.
    }
}
