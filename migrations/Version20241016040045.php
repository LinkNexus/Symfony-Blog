<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241016040045 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE
          user
        ADD
          cover_photo VARCHAR(255) DEFAULT NULL,
        DROP
          updated_at,
        CHANGE
          avatar_file_name profile_picture VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE
          `user`
        ADD
          avatar_file_name VARCHAR(255) DEFAULT NULL,
        ADD
          updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
        DROP
          profile_picture,
        DROP
          cover_photo');
    }
}