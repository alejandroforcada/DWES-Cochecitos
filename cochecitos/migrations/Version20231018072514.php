<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231018072514 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE coche ADD marca_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE coche ADD CONSTRAINT FK_A1981CD481EF0041 FOREIGN KEY (marca_id) REFERENCES marca (id)');
        $this->addSql('CREATE INDEX IDX_A1981CD481EF0041 ON coche (marca_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE coche DROP FOREIGN KEY FK_A1981CD481EF0041');
        $this->addSql('DROP INDEX IDX_A1981CD481EF0041 ON coche');
        $this->addSql('ALTER TABLE coche DROP marca_id');
    }
}
