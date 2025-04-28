<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250423104541 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP INDEX uniq_1483a5e97adf3dfb
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users ADD email VARCHAR(180) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users DROP email_email
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
            ALTER TABLE users ADD email_email VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users DROP email
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_1483a5e97adf3dfb ON users (email_email)
        SQL);
    }
}
