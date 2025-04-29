<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250428123002 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP INDEX uniq_1483a5e97adf3dfb
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users RENAME COLUMN email_email TO email
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_1483A5E9E7927C74
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users RENAME COLUMN email TO email_email
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_1483a5e97adf3dfb ON users (email_email)
        SQL);
    }
}
