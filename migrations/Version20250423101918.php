<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250423101918 extends AbstractMigration
{

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP INDEX uniq_1483a5e9e7927c74
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users ADD email_email VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users DROP email
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_1483A5E97ADF3DFB ON users (email_email)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_1483A5E97ADF3DFB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users ADD email VARCHAR(180) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users DROP email_email
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_1483a5e9e7927c74 ON users (email)
        SQL);
    }
}
