<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240411065302 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE arbeitsbereiche (id INT AUTO_INCREMENT NOT NULL, objekt_id INT DEFAULT NULL, bezeichnung VARCHAR(255) NOT NULL, INDEX IDX_2467C5BD135D1DEA (objekt_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE arbeitszeit (id INT AUTO_INCREMENT NOT NULL, fehlzeit_id INT DEFAULT NULL, user_id INT NOT NULL, datum DATE NOT NULL, eintrittszeit TIME NOT NULL, austrittszeit TIME DEFAULT NULL, INDEX IDX_7BD95A4A7D899BB2 (fehlzeit_id), INDEX IDX_7BD95A4AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE area (id INT AUTO_INCREMENT NOT NULL, objekt_id INT NOT NULL, name VARCHAR(255) NOT NULL, map VARCHAR(255) DEFAULT NULL, size JSON DEFAULT NULL, INDEX IDX_D7943D68135D1DEA (objekt_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE company (id INT AUTO_INCREMENT NOT NULL, onjekt_admin_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_4FBF094F22CB1F10 (onjekt_admin_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE compensation_types (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contract_data (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, compensation_types_id INT NOT NULL, start_date DATE NOT NULL, sing_date DATE NOT NULL, lohn DOUBLE PRECISION NOT NULL, stunden INT DEFAULT NULL, end_date DATE DEFAULT NULL, urlaub INT DEFAULT NULL, bezeichnung VARCHAR(255) NOT NULL, status VARCHAR(255) DEFAULT NULL, INDEX IDX_C586BD0BA76ED395 (user_id), INDEX IDX_C586BD0B82D35B3D (compensation_types_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dienste (id INT AUTO_INCREMENT NOT NULL, dienstplan_id INT NOT NULL, user_id INT NOT NULL, status_id INT DEFAULT NULL, kommen DATETIME NOT NULL, gehen DATETIME DEFAULT NULL, INDEX IDX_67796E3BEA913F5 (dienstplan_id), INDEX IDX_67796E3BA76ED395 (user_id), INDEX IDX_67796E3B6BF700BD (status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dienstplan (id INT AUTO_INCREMENT NOT NULL, objket_id INT NOT NULL, bezeichnung VARCHAR(255) NOT NULL, start DATE NOT NULL, ende DATE DEFAULT NULL, INDEX IDX_F53EDFE59667C2FB (objket_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dienstplan_user (dienstplan_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_98B9A50EA913F5 (dienstplan_id), INDEX IDX_98B9A50A76ED395 (user_id), PRIMARY KEY(dienstplan_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fehlzeiten (id INT AUTO_INCREMENT NOT NULL, bezeichnung VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE item_categories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, booktime INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE objekt (id INT AUTO_INCREMENT NOT NULL, company_id INT DEFAULT NULL, categories_id INT NOT NULL, name VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, ort VARCHAR(255) NOT NULL, plz VARCHAR(255) NOT NULL, main_mail VARCHAR(255) NOT NULL, website VARCHAR(255) DEFAULT NULL, telefon VARCHAR(255) NOT NULL, fax VARCHAR(255) DEFAULT NULL, bestellung_mail VARCHAR(255) DEFAULT NULL, fibi_mail VARCHAR(255) DEFAULT NULL, ust_id VARCHAR(255) DEFAULT NULL, handelsregister VARCHAR(255) DEFAULT NULL, amtsgericht VARCHAR(255) DEFAULT NULL, bild VARCHAR(255) DEFAULT NULL, staytime INT DEFAULT NULL, INDEX IDX_607421E4979B1AD6 (company_id), INDEX IDX_607421E4A21214B7 (categories_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE objekt_categories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE objekt_sub_categories (id INT AUTO_INCREMENT NOT NULL, objekt_categories_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_4EF0BBA5230058A (objekt_categories_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE opening_time (id INT AUTO_INCREMENT NOT NULL, objekt_id INT NOT NULL, day INT NOT NULL, start TIME NOT NULL, end TIME NOT NULL, effective_date DATE NOT NULL, INDEX IDX_89115E6E135D1DEA (objekt_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE recomonsation (id INT AUTO_INCREMENT NOT NULL, reservation_id INT DEFAULT NULL, points INT DEFAULT NULL, UNIQUE INDEX UNIQ_3149050CB83297E7 (reservation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rent_items (id INT AUTO_INCREMENT NOT NULL, objekt_id INT NOT NULL, category_id INT NOT NULL, area_id INT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, pax INT DEFAULT NULL, status TINYINT(1) DEFAULT NULL, usetime INT DEFAULT NULL, position JSON DEFAULT NULL, size JSON DEFAULT NULL, INDEX IDX_601BFB135D1DEA (objekt_id), INDEX IDX_601BFB12469DE2 (category_id), INDEX IDX_601BFBBD0F409C (area_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reservation (id INT AUTO_INCREMENT NOT NULL, item_id INT NOT NULL, user VARCHAR(255) NOT NULL, kommen DATETIME NOT NULL, gehen DATETIME NOT NULL, pax VARCHAR(255) NOT NULL, fon VARCHAR(255) NOT NULL, mail VARCHAR(255) NOT NULL, points INT DEFAULT NULL, status VARCHAR(32) DEFAULT NULL, aktiv VARCHAR(32) DEFAULT NULL, INDEX IDX_42C84955126F525E (item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE roles (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE special_opening_time (id INT AUTO_INCREMENT NOT NULL, objket_id INT NOT NULL, day DATE NOT NULL, start TIME DEFAULT NULL, end TIME DEFAULT NULL, close TINYINT(1) NOT NULL, INDEX IDX_51D7F0679667C2FB (objket_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, objekt_id INT DEFAULT NULL, company_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, vorname VARCHAR(255) DEFAULT NULL, nachname VARCHAR(255) DEFAULT NULL, adresse VARCHAR(255) DEFAULT NULL, strasse VARCHAR(255) DEFAULT NULL, plz VARCHAR(255) DEFAULT NULL, ort VARCHAR(255) DEFAULT NULL, land VARCHAR(255) DEFAULT NULL, telefon VARCHAR(255) DEFAULT NULL, steuernummer VARCHAR(255) DEFAULT NULL, rentenversicherungsnummer VARCHAR(255) DEFAULT NULL, iban VARCHAR(255) DEFAULT NULL, krankenkasse VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D649135D1DEA (objekt_id), INDEX IDX_8D93D649979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_contrect_data (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, contract_data_id INT DEFAULT NULL, INDEX IDX_AF421EA1A76ED395 (user_id), INDEX IDX_AF421EA13FA4DCF4 (contract_data_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_dokumente (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, titel VARCHAR(120) NOT NULL, discription VARCHAR(255) DEFAULT NULL, path VARCHAR(255) NOT NULL, uplode_time DATETIME NOT NULL, INDEX IDX_656B045AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vertrag (id INT AUTO_INCREMENT NOT NULL, objekt_id INT NOT NULL, titel VARCHAR(255) NOT NULL, discription VARCHAR(255) DEFAULT NULL, text LONGTEXT DEFAULT NULL, INDEX IDX_D5E732C1135D1DEA (objekt_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vertrag_variable (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(36) NOT NULL, var VARCHAR(255) NOT NULL, entity VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE arbeitsbereiche ADD CONSTRAINT FK_2467C5BD135D1DEA FOREIGN KEY (objekt_id) REFERENCES objekt (id)');
        $this->addSql('ALTER TABLE arbeitszeit ADD CONSTRAINT FK_7BD95A4A7D899BB2 FOREIGN KEY (fehlzeit_id) REFERENCES fehlzeiten (id)');
        $this->addSql('ALTER TABLE arbeitszeit ADD CONSTRAINT FK_7BD95A4AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE area ADD CONSTRAINT FK_D7943D68135D1DEA FOREIGN KEY (objekt_id) REFERENCES objekt (id)');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094F22CB1F10 FOREIGN KEY (onjekt_admin_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE contract_data ADD CONSTRAINT FK_C586BD0BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE contract_data ADD CONSTRAINT FK_C586BD0B82D35B3D FOREIGN KEY (compensation_types_id) REFERENCES compensation_types (id)');
        $this->addSql('ALTER TABLE dienste ADD CONSTRAINT FK_67796E3BEA913F5 FOREIGN KEY (dienstplan_id) REFERENCES dienstplan (id)');
        $this->addSql('ALTER TABLE dienste ADD CONSTRAINT FK_67796E3BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE dienste ADD CONSTRAINT FK_67796E3B6BF700BD FOREIGN KEY (status_id) REFERENCES fehlzeiten (id)');
        $this->addSql('ALTER TABLE dienstplan ADD CONSTRAINT FK_F53EDFE59667C2FB FOREIGN KEY (objket_id) REFERENCES objekt (id)');
        $this->addSql('ALTER TABLE dienstplan_user ADD CONSTRAINT FK_98B9A50EA913F5 FOREIGN KEY (dienstplan_id) REFERENCES dienstplan (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dienstplan_user ADD CONSTRAINT FK_98B9A50A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE objekt ADD CONSTRAINT FK_607421E4979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE objekt ADD CONSTRAINT FK_607421E4A21214B7 FOREIGN KEY (categories_id) REFERENCES objekt_categories (id)');
        $this->addSql('ALTER TABLE objekt_sub_categories ADD CONSTRAINT FK_4EF0BBA5230058A FOREIGN KEY (objekt_categories_id) REFERENCES objekt_categories (id)');
        $this->addSql('ALTER TABLE opening_time ADD CONSTRAINT FK_89115E6E135D1DEA FOREIGN KEY (objekt_id) REFERENCES objekt (id)');
        $this->addSql('ALTER TABLE recomonsation ADD CONSTRAINT FK_3149050CB83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('ALTER TABLE rent_items ADD CONSTRAINT FK_601BFB135D1DEA FOREIGN KEY (objekt_id) REFERENCES objekt (id)');
        $this->addSql('ALTER TABLE rent_items ADD CONSTRAINT FK_601BFB12469DE2 FOREIGN KEY (category_id) REFERENCES item_categories (id)');
        $this->addSql('ALTER TABLE rent_items ADD CONSTRAINT FK_601BFBBD0F409C FOREIGN KEY (area_id) REFERENCES area (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955126F525E FOREIGN KEY (item_id) REFERENCES rent_items (id)');
        $this->addSql('ALTER TABLE special_opening_time ADD CONSTRAINT FK_51D7F0679667C2FB FOREIGN KEY (objket_id) REFERENCES objekt (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649135D1DEA FOREIGN KEY (objekt_id) REFERENCES objekt (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE user_contrect_data ADD CONSTRAINT FK_AF421EA1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_contrect_data ADD CONSTRAINT FK_AF421EA13FA4DCF4 FOREIGN KEY (contract_data_id) REFERENCES contract_data (id)');
        $this->addSql('ALTER TABLE user_dokumente ADD CONSTRAINT FK_656B045AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE vertrag ADD CONSTRAINT FK_D5E732C1135D1DEA FOREIGN KEY (objekt_id) REFERENCES objekt (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE arbeitsbereiche DROP FOREIGN KEY FK_2467C5BD135D1DEA');
        $this->addSql('ALTER TABLE arbeitszeit DROP FOREIGN KEY FK_7BD95A4A7D899BB2');
        $this->addSql('ALTER TABLE arbeitszeit DROP FOREIGN KEY FK_7BD95A4AA76ED395');
        $this->addSql('ALTER TABLE area DROP FOREIGN KEY FK_D7943D68135D1DEA');
        $this->addSql('ALTER TABLE company DROP FOREIGN KEY FK_4FBF094F22CB1F10');
        $this->addSql('ALTER TABLE contract_data DROP FOREIGN KEY FK_C586BD0BA76ED395');
        $this->addSql('ALTER TABLE contract_data DROP FOREIGN KEY FK_C586BD0B82D35B3D');
        $this->addSql('ALTER TABLE dienste DROP FOREIGN KEY FK_67796E3BEA913F5');
        $this->addSql('ALTER TABLE dienste DROP FOREIGN KEY FK_67796E3BA76ED395');
        $this->addSql('ALTER TABLE dienste DROP FOREIGN KEY FK_67796E3B6BF700BD');
        $this->addSql('ALTER TABLE dienstplan DROP FOREIGN KEY FK_F53EDFE59667C2FB');
        $this->addSql('ALTER TABLE dienstplan_user DROP FOREIGN KEY FK_98B9A50EA913F5');
        $this->addSql('ALTER TABLE dienstplan_user DROP FOREIGN KEY FK_98B9A50A76ED395');
        $this->addSql('ALTER TABLE objekt DROP FOREIGN KEY FK_607421E4979B1AD6');
        $this->addSql('ALTER TABLE objekt DROP FOREIGN KEY FK_607421E4A21214B7');
        $this->addSql('ALTER TABLE objekt_sub_categories DROP FOREIGN KEY FK_4EF0BBA5230058A');
        $this->addSql('ALTER TABLE opening_time DROP FOREIGN KEY FK_89115E6E135D1DEA');
        $this->addSql('ALTER TABLE recomonsation DROP FOREIGN KEY FK_3149050CB83297E7');
        $this->addSql('ALTER TABLE rent_items DROP FOREIGN KEY FK_601BFB135D1DEA');
        $this->addSql('ALTER TABLE rent_items DROP FOREIGN KEY FK_601BFB12469DE2');
        $this->addSql('ALTER TABLE rent_items DROP FOREIGN KEY FK_601BFBBD0F409C');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955126F525E');
        $this->addSql('ALTER TABLE special_opening_time DROP FOREIGN KEY FK_51D7F0679667C2FB');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649135D1DEA');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649979B1AD6');
        $this->addSql('ALTER TABLE user_contrect_data DROP FOREIGN KEY FK_AF421EA1A76ED395');
        $this->addSql('ALTER TABLE user_contrect_data DROP FOREIGN KEY FK_AF421EA13FA4DCF4');
        $this->addSql('ALTER TABLE user_dokumente DROP FOREIGN KEY FK_656B045AA76ED395');
        $this->addSql('ALTER TABLE vertrag DROP FOREIGN KEY FK_D5E732C1135D1DEA');
        $this->addSql('DROP TABLE arbeitsbereiche');
        $this->addSql('DROP TABLE arbeitszeit');
        $this->addSql('DROP TABLE area');
        $this->addSql('DROP TABLE company');
        $this->addSql('DROP TABLE compensation_types');
        $this->addSql('DROP TABLE contract_data');
        $this->addSql('DROP TABLE dienste');
        $this->addSql('DROP TABLE dienstplan');
        $this->addSql('DROP TABLE dienstplan_user');
        $this->addSql('DROP TABLE fehlzeiten');
        $this->addSql('DROP TABLE item_categories');
        $this->addSql('DROP TABLE objekt');
        $this->addSql('DROP TABLE objekt_categories');
        $this->addSql('DROP TABLE objekt_sub_categories');
        $this->addSql('DROP TABLE opening_time');
        $this->addSql('DROP TABLE recomonsation');
        $this->addSql('DROP TABLE rent_items');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('DROP TABLE roles');
        $this->addSql('DROP TABLE special_opening_time');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_contrect_data');
        $this->addSql('DROP TABLE user_dokumente');
        $this->addSql('DROP TABLE vertrag');
        $this->addSql('DROP TABLE vertrag_variable');
    }
}
