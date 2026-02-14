<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20250816154014 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Add allergy warning fields, refactor payment plans into boolean + text info (instead of list of strings). Migrate ages from creator_values to creators table.';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__creators AS SELECT id, creator_id, name, formerly, intro, since, country, state, city, payment_plans, species_does, species_doesnt, notes, contact_allowed, inactive_reason, production_models_comment, styles_comment, order_types_comment, features_comment, payment_methods, currencies_accepted, species_comment FROM creators
        SQL);
        $this->addSql('DROP TABLE creators');
        $this->addSql(<<<'SQL'
            CREATE TABLE creators (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, creator_id CLOB NOT NULL, name CLOB NOT NULL, formerly CLOB NOT NULL, intro CLOB NOT NULL, since CLOB NOT NULL, country CLOB NOT NULL, state CLOB NOT NULL, city CLOB NOT NULL, allergy_warning_info CLOB NOT NULL, species_does CLOB NOT NULL, species_doesnt CLOB NOT NULL, notes CLOB NOT NULL, contact_allowed CLOB DEFAULT NULL, inactive_reason CLOB NOT NULL, production_models_comment CLOB NOT NULL, styles_comment CLOB NOT NULL, order_types_comment CLOB NOT NULL, features_comment CLOB NOT NULL, payment_methods CLOB NOT NULL, currencies_accepted CLOB NOT NULL, species_comment CLOB NOT NULL, ages CLOB DEFAULT NULL, has_allergy_warning BOOLEAN DEFAULT NULL, offers_payment_plans BOOLEAN DEFAULT NULL, payment_plans_info CLOB NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO creators (id, creator_id, name, formerly, intro, since, country, state, city, allergy_warning_info, species_does, species_doesnt, notes, contact_allowed, inactive_reason, production_models_comment, styles_comment, order_types_comment, features_comment, payment_methods, currencies_accepted, species_comment, payment_plans_info) SELECT id, creator_id, name, formerly, intro, since, country, state, city, '', species_does, species_doesnt, notes, contact_allowed, inactive_reason, production_models_comment, styles_comment, order_types_comment, features_comment, payment_methods, currencies_accepted, species_comment, payment_plans FROM __temp__creators
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__creators
        SQL);
        $this->addSql(<<<'SQL'
            UPDATE creators SET offers_payment_plans = true WHERE payment_plans_info <> ''
        SQL);
        $this->addSql(<<<'SQL'
            UPDATE creators SET offers_payment_plans = false WHERE payment_plans_info = 'None'
        SQL);
        $this->addSql(<<<'SQL'
            UPDATE creators SET payment_plans_info = '' WHERE payment_plans_info = 'None'
        SQL);
        $this->addSql(<<<'SQL'
            UPDATE creators SET payment_plans_info = '• ' || REPLACE(payment_plans_info, char(10), char(10) || '• ') WHERE payment_plans_info LIKE '%' || char(10) || '%'
        SQL);
        $this->addSql(<<<'SQL'
            UPDATE creators SET ages = (SELECT value FROM creators_values AS cv WHERE cv.creator_id = creators.id AND cv.field_name = 'AGES')
        SQL);
        $this->addSql(<<<'SQL'
            DELETE FROM creators_values WHERE field_name = 'AGES'
        SQL);
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(); // Restore the backup.
    }
}
