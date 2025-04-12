<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250412091232 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Removed contact_info_obfuscated (obfuscated email address).';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__artisans AS SELECT id, maker_id, name, formerly, intro, since, country, state, city, payment_plans, species_does, species_doesnt, notes, contact_allowed, inactive_reason, production_models_comment, styles_comment, order_types_comment, features_comment, payment_methods, currencies_accepted, species_comment FROM artisans
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE artisans
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE artisans (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, maker_id CLOB NOT NULL, name CLOB NOT NULL, formerly CLOB NOT NULL, intro CLOB NOT NULL, since CLOB NOT NULL, country CLOB NOT NULL, state CLOB NOT NULL, city CLOB NOT NULL, payment_plans CLOB NOT NULL, species_does CLOB NOT NULL, species_doesnt CLOB NOT NULL, notes CLOB NOT NULL, contact_allowed CLOB DEFAULT NULL, inactive_reason CLOB NOT NULL, production_models_comment CLOB NOT NULL, styles_comment CLOB NOT NULL, order_types_comment CLOB NOT NULL, features_comment CLOB NOT NULL, payment_methods CLOB NOT NULL, currencies_accepted CLOB NOT NULL, species_comment CLOB NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO artisans (id, maker_id, name, formerly, intro, since, country, state, city, payment_plans, species_does, species_doesnt, notes, contact_allowed, inactive_reason, production_models_comment, styles_comment, order_types_comment, features_comment, payment_methods, currencies_accepted, species_comment) SELECT id, maker_id, name, formerly, intro, since, country, state, city, payment_plans, species_does, species_doesnt, notes, contact_allowed, inactive_reason, production_models_comment, styles_comment, order_types_comment, features_comment, payment_methods, currencies_accepted, species_comment FROM __temp__artisans
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__artisans
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(); // Restore the backup.
    }
}
