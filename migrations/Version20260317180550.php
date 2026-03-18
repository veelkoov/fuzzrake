<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20260317180550 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Add creator reference and status to submissions.';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TEMPORARY TABLE __temp__submissions AS SELECT id, str_id, submitted_at_utc, payload, directives, comment FROM submissions');
        $this->addSql('DROP TABLE submissions');
        $this->addSql('CREATE TABLE submissions (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, str_id CLOB NOT NULL, submitted_at_utc DATETIME NOT NULL, payload CLOB NOT NULL, directives CLOB NOT NULL, comment CLOB NOT NULL, status VARCHAR(17) NOT NULL, is_update BOOLEAN NOT NULL)');
        $this->addSql('INSERT INTO submissions (id, str_id, submitted_at_utc, payload, directives, comment, status, is_update) SELECT id, str_id, submitted_at_utc, payload, directives, comment, \'NEW\', false FROM __temp__submissions');
        $this->addSql('DROP TABLE __temp__submissions');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3F6169F76810ECF6 ON submissions (str_id)');

        $this->addSql('UPDATE submissions SET status = \'IMPORTED\' WHERE comment = \'Imported\'');
        $this->addSql('UPDATE submissions SET comment = \'\' WHERE status = \'IMPORTED\'');

        $this->addSql('UPDATE submissions SET status = \'AWAITING_RESPONSE\' WHERE comment LIKE \'%emailed%\'');

        $this->addSql('UPDATE submissions SET status = \'REJECTED\' WHERE comment LIKE \'MX TESTING\'');

        $this->addSql('UPDATE submissions SET status = \'REPLACED\' WHERE comment LIKE \'Replaced%\'');
        $this->addSql('UPDATE submissions SET comment = \'\' WHERE comment = \'Replaced\'');

        $this->addSql('UPDATE submissions SET status = \'OTHER\' WHERE status = \'NEW\' AND comment <> \'\'');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(); // Restore the backup.
    }
}
