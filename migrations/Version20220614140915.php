<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220614140915 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE equipment_slot (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE item (id INT AUTO_INCREMENT NOT NULL, equipment_slot_id INT NOT NULL, name VARCHAR(255) NOT NULL, icon_path VARCHAR(255) NOT NULL, is_default_item TINYINT(1) NOT NULL, cost INT NOT NULL, type VARCHAR(255) NOT NULL, sprite_path VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_1F1B251E5E237E06 (name), INDEX IDX_1F1B251E2F9EDE73 (equipment_slot_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stat (id INT AUTO_INCREMENT NOT NULL, equipment_id INT NOT NULL, type_id INT NOT NULL, base_value INT NOT NULL, modifiers LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_20B8FF21517FE9FE (equipment_id), INDEX IDX_20B8FF21C54C8C93 (type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stat_type (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E2F9EDE73 FOREIGN KEY (equipment_slot_id) REFERENCES equipment_slot (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE stat ADD CONSTRAINT FK_20B8FF21517FE9FE FOREIGN KEY (equipment_id) REFERENCES item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE stat ADD CONSTRAINT FK_20B8FF21C54C8C93 FOREIGN KEY (type_id) REFERENCES currency_type (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251E2F9EDE73');
        $this->addSql('ALTER TABLE stat DROP FOREIGN KEY FK_20B8FF21517FE9FE');
        $this->addSql('DROP TABLE equipment_slot');
        $this->addSql('DROP TABLE item');
        $this->addSql('DROP TABLE stat');
        $this->addSql('DROP TABLE stat_type');
    }
}
