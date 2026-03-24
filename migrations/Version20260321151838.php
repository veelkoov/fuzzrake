<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20260321151838 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Migrate login&contact permit data from creators&private data tables. Add owner user and updated creator to submissions.';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD COLUMN contact_permit CLOB DEFAULT NULL');

        $this->addSql(<<<'SQL'
            UPDATE creator_ids SET owner_creator_id = (
                SELECT c_new.id
                FROM creators AS c_new
                WHERE c_new.creator_id = (
                    SELECT substring(c_old.inactive_reason, 24) AS replaced_with
                    FROM creators AS c_old
                    WHERE c_old.id = owner_creator_id
                )
            )
            WHERE owner_creator_id IN (
                SELECT c_old_w.id
                FROM creators AS c_old_w
                WHERE c_old_w.inactive_reason LIKE 'Duplicate; replaced by %'
            )
        SQL);

        $this->addSql('DELETE FROM creators WHERE inactive_reason LIKE \'Duplicate; replaced by %\'');
        $this->addSql('UPDATE creators_private_data SET email_address = concat(\'missing-email-\', id) WHERE email_address = \'\'');
        $this->addSql('INSERT INTO users (email, roles, password, is_verified, contact_permit) SELECT cpd.email_address, \'[]\', cpd.password, false, c.contact_allowed FROM creators_private_data AS cpd JOIN creators AS c ON c.id = cpd.creator_id');

        $this->addSql('CREATE TEMPORARY TABLE __temp__creators AS SELECT id, creator_id, name, formerly, intro, since, country, state, city, allergy_warning_info, species_does, species_doesnt, notes, inactive_reason, production_models_comment, styles_comment, order_types_comment, features_comment, payment_methods, currencies_accepted, species_comment, ages, has_allergy_warning, offers_payment_plans, payment_plans_info FROM creators');
        $this->addSql('DROP TABLE creators');
        $this->addSql('CREATE TABLE creators (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, creator_id CLOB NOT NULL, name CLOB NOT NULL, formerly CLOB NOT NULL, intro CLOB NOT NULL, since CLOB NOT NULL, country CLOB NOT NULL, state CLOB NOT NULL, city CLOB NOT NULL, allergy_warning_info CLOB NOT NULL, species_does CLOB NOT NULL, species_doesnt CLOB NOT NULL, notes CLOB NOT NULL, inactive_reason CLOB NOT NULL, production_models_comment CLOB NOT NULL, styles_comment CLOB NOT NULL, order_types_comment CLOB NOT NULL, features_comment CLOB NOT NULL, payment_methods CLOB NOT NULL, currencies_accepted CLOB NOT NULL, species_comment CLOB NOT NULL, ages CLOB DEFAULT NULL, has_allergy_warning BOOLEAN DEFAULT NULL, offers_payment_plans BOOLEAN DEFAULT NULL, payment_plans_info CLOB NOT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_CF09F903A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql(<<<'SQL'
            INSERT INTO creators (id, creator_id, name, formerly, intro, since, country, state, city, allergy_warning_info, species_does, species_doesnt, notes, inactive_reason, production_models_comment, styles_comment, order_types_comment, features_comment, payment_methods, currencies_accepted, species_comment, ages, has_allergy_warning, offers_payment_plans, payment_plans_info, user_id)
            SELECT id, creator_id, name, formerly, intro, since, country, state, city, allergy_warning_info, species_does, species_doesnt, notes, inactive_reason, production_models_comment, styles_comment, order_types_comment, features_comment, payment_methods, currencies_accepted, species_comment, ages, has_allergy_warning, offers_payment_plans, payment_plans_info, (
                SELECT uid_u.id
                FROM users AS uid_u
                JOIN creators_private_data AS uid_cpd
                    ON uid_u.email = uid_cpd.email_address
                WHERE uid_cpd.creator_id = __temp__creators.id
            )
            FROM __temp__creators
        SQL);
        $this->addSql('DROP TABLE __temp__creators');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CF09F903A76ED395 ON creators (user_id)');

        $this->addSql('DROP TABLE creators_private_data');

        $this->addSql('CREATE TEMPORARY TABLE __temp__submissions AS SELECT id, str_id, submitted_at_utc, payload, directives, comment, status, is_update FROM submissions');
        $this->addSql('DROP TABLE submissions');
        $this->addSql('CREATE TABLE submissions (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, str_id CLOB NOT NULL, submitted_at_utc DATETIME NOT NULL, payload CLOB NOT NULL, directives CLOB NOT NULL, comment CLOB NOT NULL, status VARCHAR(17) NOT NULL, is_update BOOLEAN NOT NULL, owner_id INTEGER DEFAULT NULL, creator_id INTEGER DEFAULT NULL, CONSTRAINT FK_3F6169F77E3C61F9 FOREIGN KEY (owner_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3F6169F761220EA6 FOREIGN KEY (creator_id) REFERENCES creators (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO submissions (id, str_id, submitted_at_utc, payload, directives, comment, status, is_update) SELECT id, str_id, submitted_at_utc, payload, directives, comment, status, is_update FROM __temp__submissions');
        $this->addSql('DROP TABLE __temp__submissions');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3F6169F76810ECF6 ON submissions (str_id)');
        $this->addSql('CREATE INDEX IDX_3F6169F77E3C61F9 ON submissions (owner_id)');
        $this->addSql('CREATE INDEX IDX_3F6169F761220EA6 ON submissions (creator_id)');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(); // Restore the backup.
    }
}
