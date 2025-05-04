<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250504134002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE users ALTER secret_key TYPE VARCHAR(255)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN users.secret_key IS '(DC2Type:secret_key)'
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE users ALTER secret_key TYPE VARCHAR(255)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN users.secret_key IS NULL
        SQL);
    }
}
