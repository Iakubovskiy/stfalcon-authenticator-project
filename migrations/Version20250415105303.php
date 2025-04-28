<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250415105303 extends AbstractMigration
{

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ADD photo_url VARCHAR(255) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" DROP photo_url
        SQL);
    }
}
