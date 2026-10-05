<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261004221330 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CATEGORY_NAME ON category (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CATEGORY_SLUG ON category (slug)');
        $this->addSql('ALTER TABLE skill DROP FOREIGN KEY `FK_5E3DE47712469DE2`');
        $this->addSql('ALTER TABLE skill ADD CONSTRAINT FK_5E3DE47712469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_SKILL_NAME ON skill (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_SKILL_SLUG ON skill (slug)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_CATEGORY_NAME ON category');
        $this->addSql('DROP INDEX UNIQ_CATEGORY_SLUG ON category');
        $this->addSql('ALTER TABLE skill DROP FOREIGN KEY FK_5E3DE47712469DE2');
        $this->addSql('DROP INDEX UNIQ_SKILL_NAME ON skill');
        $this->addSql('DROP INDEX UNIQ_SKILL_SLUG ON skill');
        $this->addSql('ALTER TABLE skill ADD CONSTRAINT `FK_5E3DE47712469DE2` FOREIGN KEY (category_id) REFERENCES category (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
