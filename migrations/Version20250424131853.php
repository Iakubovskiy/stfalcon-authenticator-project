<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250424131853 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP INDEX uniq_1483a5e9e7927c74
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users ALTER email TYPE VARCHAR(255)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE users ALTER email TYPE VARCHAR(180)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_1483a5e9e7927c74 ON users (email)
        SQL);
    }
}
