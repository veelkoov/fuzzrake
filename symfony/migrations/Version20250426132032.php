<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250426132032 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__submissions AS SELECT id, str_id, directives, comment FROM submissions
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE submissions
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE submissions (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, str_id CLOB NOT NULL, directives CLOB NOT NULL, comment CLOB NOT NULL, payload CLOB NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO submissions (id, str_id, directives, comment, payload) SELECT id, str_id, directives, comment, '' FROM __temp__submissions
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__submissions
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_3F6169F76810ECF6 ON submissions (str_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
    }
}
