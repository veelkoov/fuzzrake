<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250424214005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename all legacy names in tables and columns.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE creators (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, creator_id CLOB NOT NULL, name CLOB NOT NULL, formerly CLOB NOT NULL, intro CLOB NOT NULL, since CLOB NOT NULL, country CLOB NOT NULL, state CLOB NOT NULL, city CLOB NOT NULL, payment_plans CLOB NOT NULL, species_does CLOB NOT NULL, species_doesnt CLOB NOT NULL, notes CLOB NOT NULL, contact_allowed CLOB DEFAULT NULL, inactive_reason CLOB NOT NULL, production_models_comment CLOB NOT NULL, styles_comment CLOB NOT NULL, order_types_comment CLOB NOT NULL, features_comment CLOB NOT NULL, payment_methods CLOB NOT NULL, currencies_accepted CLOB NOT NULL, species_comment CLOB NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO creators (id, creator_id, name, formerly, intro, since, country, state, city, payment_plans, species_does, species_doesnt, notes, contact_allowed, inactive_reason, production_models_comment, styles_comment, order_types_comment, features_comment, payment_methods, currencies_accepted, species_comment) SELECT id, maker_id, name, formerly, intro, since, country, state, city, payment_plans, species_does, species_doesnt, notes, contact_allowed, inactive_reason, production_models_comment, styles_comment, order_types_comment, features_comment, payment_methods, currencies_accepted, species_comment FROM artisans
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE artisans
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE creators_offers_statuses (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, creator_id INTEGER NOT NULL, offer CLOB NOT NULL, is_open BOOLEAN NOT NULL, CONSTRAINT FK_50A31F9561220EA6 FOREIGN KEY (creator_id) REFERENCES creators (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO creators_offers_statuses (id, creator_id, offer, is_open) SELECT id, artisan_id, offer, is_open FROM artisans_commissions_statuses
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE artisans_commissions_statuses
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6DEF354461220EA6 ON creators_offers_statuses (creator_id)
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE creators_urls (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, creator_id INTEGER NOT NULL, type CLOB NOT NULL, url CLOB NOT NULL, CONSTRAINT FK_45AAF3BC61220EA6 FOREIGN KEY (creator_id) REFERENCES creators (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO creators_urls (id, creator_id, type, url) SELECT id, artisan_id, type, url FROM artisans_urls
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE artisans_urls
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_88CC944961220EA6 ON creators_urls (creator_id)
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE creators_urls_states (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, creator_url_id INTEGER NOT NULL, last_success DATETIME DEFAULT NULL, last_failure DATETIME DEFAULT NULL, last_failure_code INTEGER NOT NULL, last_failure_reason CLOB NOT NULL, CONSTRAINT FK_2A7FF195AD99577A FOREIGN KEY (creator_url_id) REFERENCES creators_urls (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO creators_urls_states (id, creator_url_id, last_success, last_failure, last_failure_code, last_failure_reason) SELECT id, artisan_url_id, last_success, last_failure, last_failure_code, last_failure_reason FROM artisans_urls_states
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE artisans_urls_states
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_9B9D6F3FAD99577A ON creators_urls_states (creator_url_id)
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE creators_values (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, creator_id INTEGER NOT NULL, field_name CLOB NOT NULL, value CLOB NOT NULL, CONSTRAINT FK_59C2496D61220EA6 FOREIGN KEY (creator_id) REFERENCES creators (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO creators_values (id, creator_id, field_name, value) SELECT id, artisan_id, field_name, value FROM artisans_values
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE artisans_values
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E3DF650861220EA6 ON creators_values (creator_id)
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE creators_volatile_data (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, creator_id INTEGER NOT NULL, last_cs_update DATETIME DEFAULT NULL, cs_tracker_issue BOOLEAN NOT NULL, CONSTRAINT FK_C9E5172C61220EA6 FOREIGN KEY (creator_id) REFERENCES creators (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO creators_volatile_data (id, creator_id, last_cs_update, cs_tracker_issue) SELECT id, artisan_id, last_cs_update, cs_tracker_issue FROM artisans_volatile_data
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE artisans_volatile_data
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_8ABA4CEE61220EA6 ON creators_volatile_data (creator_id)
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__creators_species AS SELECT id, specie_id, artisan_id FROM creators_species
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE creators_species
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE creators_species (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, specie_id INTEGER NOT NULL, creator_id INTEGER NOT NULL, CONSTRAINT FK_E2644044D5436AB7 FOREIGN KEY (specie_id) REFERENCES species (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_E264404461220EA6 FOREIGN KEY (creator_id) REFERENCES creators (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO creators_species (id, specie_id, creator_id) SELECT id, specie_id, artisan_id FROM __temp__creators_species
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__creators_species
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E2644044D5436AB7 ON creators_species (specie_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E264404461220EA6 ON creators_species (creator_id)
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__events AS SELECT id, timestamp, type, artisan_name, git_commits, checked_urls, description, no_longer_open_for, now_open_for, tracking_issues, new_makers_count, updated_makers_count, reported_updated_makers_count FROM events
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE events
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE events (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, timestamp DATETIME NOT NULL, type CLOB NOT NULL, creator_name CLOB NOT NULL, new_creators_count INTEGER NOT NULL, updated_creators_count INTEGER NOT NULL, reported_updated_creators_count INTEGER NOT NULL, git_commits CLOB NOT NULL, checked_urls CLOB NOT NULL, description CLOB NOT NULL, no_longer_open_for CLOB NOT NULL, now_open_for CLOB NOT NULL, tracking_issues BOOLEAN NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO events (id, timestamp, type, creator_name, new_creators_count, updated_creators_count, reported_updated_creators_count, git_commits, checked_urls, description, no_longer_open_for, now_open_for, tracking_issues)
                         SELECT id, timestamp, type, artisan_name, new_makers_count, updated_makers_count, reported_updated_makers_count, git_commits, checked_urls, description, no_longer_open_for, now_open_for, tracking_issues FROM __temp__events
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__events
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE creator_ids (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, owner_creator_id INTEGER NOT NULL, creator_id CLOB NOT NULL, CONSTRAINT FK_190B865F334897E3 FOREIGN KEY (owner_creator_id) REFERENCES creators (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO creator_ids (id, owner_creator_id, creator_id) SELECT id, artisan_id, maker_id FROM maker_ids
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE maker_ids
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_24DAEBD861220EA6 ON creator_ids (creator_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_24DAEBD8334897E3 ON creator_ids (owner_creator_id)
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__creators_private_data AS SELECT id, creator_id, email_address, password FROM creators_private_data
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE creators_private_data
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE creators_private_data (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, creator_id INTEGER NOT NULL, email_address CLOB NOT NULL, password CLOB NOT NULL, CONSTRAINT FK_F17D369661220EA6 FOREIGN KEY (creator_id) REFERENCES creators (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO creators_private_data (id, creator_id, email_address, password) SELECT id, creator_id, email_address, password FROM __temp__creators_private_data
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__creators_private_data
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_F17D369661220EA6 ON creators_private_data (creator_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(); // Restore the backup.
    }
}
