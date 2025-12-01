<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251127194937 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE signature CHANGE street street VARCHAR(255) DEFAULT NULL, CHANGE house_number house_number VARCHAR(10) DEFAULT NULL, CHANGE postal_code postal_code INT DEFAULT NULL, CHANGE city city VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE signature CHANGE street street VARCHAR(255) NOT NULL, CHANGE house_number house_number VARCHAR(10) NOT NULL, CHANGE postal_code postal_code INT NOT NULL, CHANGE city city VARCHAR(255) NOT NULL');
    }
}
