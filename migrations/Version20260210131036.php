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
        $this->addSql('DELETE FROM creators_private_data WHERE creator_id IN (SELECT id FROM creators WHERE inactive_reason LIKE \'Duplicate; replaced by%\')');
        $this->addSql('UPDATE creators_private_data SET email_address = concat(\'missing-email-\', id) WHERE email_address = \'\'');

        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL, password VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON user (email)');

        $this->addSql('CREATE TEMPORARY TABLE __temp__creators AS SELECT id, creator_id, name, formerly, intro, since, country, state, city, payment_plans, species_does, species_doesnt, notes, contact_allowed, inactive_reason, production_models_comment, styles_comment, order_types_comment, features_comment, payment_methods, currencies_accepted, species_comment FROM creators');
        $this->addSql('DROP TABLE creators');
        $this->addSql('CREATE TABLE creators (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, creator_id CLOB NOT NULL, name CLOB NOT NULL, formerly CLOB NOT NULL, intro CLOB NOT NULL, since CLOB NOT NULL, country CLOB NOT NULL, state CLOB NOT NULL, city CLOB NOT NULL, payment_plans CLOB NOT NULL, species_does CLOB NOT NULL, species_doesnt CLOB NOT NULL, notes CLOB NOT NULL, contact_allowed CLOB DEFAULT NULL, inactive_reason CLOB NOT NULL, production_models_comment CLOB NOT NULL, styles_comment CLOB NOT NULL, order_types_comment CLOB NOT NULL, features_comment CLOB NOT NULL, payment_methods CLOB NOT NULL, currencies_accepted CLOB NOT NULL, species_comment CLOB NOT NULL, user_id INTEGER DEFAULT NULL, CONSTRAINT FK_CF09F903A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO creators (id, creator_id, name, formerly, intro, since, country, state, city, payment_plans, species_does, species_doesnt, notes, contact_allowed, inactive_reason, production_models_comment, styles_comment, order_types_comment, features_comment, payment_methods, currencies_accepted, species_comment) SELECT id, creator_id, name, formerly, intro, since, country, state, city, payment_plans, species_does, species_doesnt, notes, contact_allowed, inactive_reason, production_models_comment, styles_comment, order_types_comment, features_comment, payment_methods, currencies_accepted, species_comment FROM __temp__creators');
        $this->addSql('DROP TABLE __temp__creators');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CF09F903A76ED395 ON creators (user_id)');

        $this->addSql('INSERT INTO user (email, roles, password) SELECT email_address, \'[]\', password FROM creators_private_data');
        $this->addSql('UPDATE creators SET user_id = (SELECT u.id FROM user AS u JOIN creators_private_data AS cpd ON u.email = cpd.email_address WHERE cpd.creator_id = creators.id)');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(); // Restore the backup.
    }
}
