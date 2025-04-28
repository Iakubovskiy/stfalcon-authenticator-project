<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250409091350 extends AbstractMigration
{

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" DROP two_factor_authentication_enabled
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ALTER secret_key DROP NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ADD two_factor_authentication_enabled BOOLEAN NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ALTER secret_key SET NOT NULL
        SQL);
    }
}
