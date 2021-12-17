<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211204115641 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE exercise_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE person_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE training_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE exercise (id INT NOT NULL, training_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_AEDAD51CBEFD98D1 ON exercise (training_id)');
        $this->addSql('CREATE TABLE person (id INT NOT NULL, fullname TEXT DEFAULT NULL, email VARCHAR(255) NOT NULL, birthday DATE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN person.birthday IS \'(DC2Type:date_immutable)\'');
        $this->addSql('CREATE TABLE training (id INT NOT NULL, person_id INT DEFAULT NULL, description TEXT DEFAULT NULL, type VARCHAR(255) NOT NULL, duration SMALLINT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D5128A8F217BBB47 ON training (person_id)');
        $this->addSql('COMMENT ON COLUMN training.date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE exercise ADD CONSTRAINT FK_AEDAD51CBEFD98D1 FOREIGN KEY (training_id) REFERENCES training (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE training ADD CONSTRAINT FK_D5128A8F217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE training DROP CONSTRAINT FK_D5128A8F217BBB47');
        $this->addSql('ALTER TABLE exercise DROP CONSTRAINT FK_AEDAD51CBEFD98D1');
        $this->addSql('DROP SEQUENCE exercise_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE person_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE training_id_seq CASCADE');
        $this->addSql('DROP TABLE exercise');
        $this->addSql('DROP TABLE person');
        $this->addSql('DROP TABLE training');
    }
}
