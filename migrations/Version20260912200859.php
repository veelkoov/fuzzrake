<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20260912200859 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Rename production models to offers and order types to products.';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TEMPORARY TABLE __temp__creators AS SELECT
            id, creator_id, name, formerly, intro, since, country, state, city, allergy_warning_info, species_does, species_doesnt, notes, inactive_reason, styles_comment, features_comment, payment_methods, currencies_accepted, species_comment, ages, has_allergy_warning, offers_payment_plans, payment_plans_info, user_id, order_types_comment, production_models_comment FROM creators');
        $this->addSql('DROP TABLE creators');
        $this->addSql('CREATE TABLE creators (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, creator_id CLOB NOT NULL, name CLOB NOT NULL, formerly CLOB NOT NULL, intro CLOB NOT NULL, since CLOB NOT NULL, country CLOB NOT NULL, state CLOB NOT NULL, city CLOB NOT NULL, allergy_warning_info CLOB NOT NULL, species_does CLOB NOT NULL, species_doesnt CLOB NOT NULL, notes CLOB NOT NULL, inactive_reason CLOB NOT NULL, styles_comment CLOB NOT NULL, features_comment CLOB NOT NULL, payment_methods CLOB NOT NULL, currencies_accepted CLOB NOT NULL, species_comment CLOB NOT NULL, ages CLOB DEFAULT NULL, has_allergy_warning BOOLEAN DEFAULT NULL, offers_payment_plans BOOLEAN DEFAULT NULL, payment_plans_info CLOB NOT NULL, user_id INTEGER NOT NULL, offers_comment CLOB NOT NULL, products_comment CLOB NOT NULL, CONSTRAINT FK_CF09F903A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO creators (id, creator_id, name, formerly, intro, since, country, state, city, allergy_warning_info, species_does, species_doesnt, notes, inactive_reason, styles_comment, features_comment, payment_methods, currencies_accepted, species_comment, ages, has_allergy_warning, offers_payment_plans, payment_plans_info, user_id, products_comment, offers_comment) SELECT id, creator_id, name, formerly, intro, since, country, state, city, allergy_warning_info, species_does, species_doesnt, notes, inactive_reason, styles_comment, features_comment, payment_methods, currencies_accepted, species_comment, ages, has_allergy_warning, offers_payment_plans, payment_plans_info, user_id, order_types_comment, production_models_comment FROM __temp__creators');
        $this->addSql('DROP TABLE __temp__creators');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CF09F903A76ED395 ON creators (user_id)');
        $this->addSql("UPDATE creators_values SET field_name = 'PRODUCTS' WHERE field_name = 'ORDER_TYPES'");
        $this->addSql("UPDATE creators_values SET field_name = 'OTHER_PRODUCTS' WHERE field_name = 'OTHER_ORDER_TYPES'");
        $this->addSql("UPDATE creators_values SET field_name = 'OFFERS' WHERE field_name = 'PRODUCTION_MODELS'");
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(); // Restore the backup.
    }
}
