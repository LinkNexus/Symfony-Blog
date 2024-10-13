<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241012074646 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE block (
          id INT AUTO_INCREMENT NOT NULL,
          blocked_user_id INT NOT NULL,
          blocking_user_id INT NOT NULL,
          INDEX IDX_831B97221EBCBB63 (blocked_user_id),
          INDEX IDX_831B9722F90D76D (blocking_user_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (
          id INT AUTO_INCREMENT NOT NULL,
          name VARCHAR(255) NOT NULL,
          slug VARCHAR(255) NOT NULL,
          icon LONGTEXT DEFAULT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE COMMENT (
          id INT AUTO_INCREMENT NOT NULL,
          post_id INT NOT NULL,
          owner_id INT NOT NULL,
          responded_comment_id INT DEFAULT NULL,
          content LONGTEXT NOT NULL,
          created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          INDEX IDX_9474526C4B89032C (post_id),
          INDEX IDX_9474526C7E3C61F9 (owner_id),
          INDEX IDX_9474526C5D3F9342 (responded_comment_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE comment_reaction (
          id INT AUTO_INCREMENT NOT NULL,
          comment_id INT NOT NULL,
          owner_id INT NOT NULL,
          TYPE VARCHAR(255) NOT NULL,
          INDEX IDX_B99364F1F8697D13 (comment_id),
          INDEX IDX_B99364F17E3C61F9 (owner_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hidden_comment (
          id INT AUTO_INCREMENT NOT NULL,
          comment_id INT NOT NULL,
          user_id INT NOT NULL,
          INDEX IDX_A7173D9F8697D13 (comment_id),
          INDEX IDX_A7173D9A76ED395 (user_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hidden_post (
          id INT AUTO_INCREMENT NOT NULL,
          post_id INT NOT NULL,
          user_id INT NOT NULL,
          INDEX IDX_D3B171214B89032C (post_id),
          INDEX IDX_D3B17121A76ED395 (user_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE post (
          id INT AUTO_INCREMENT NOT NULL,
          owner_id INT NOT NULL,
          content LONGTEXT NOT NULL,
          audience_type VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          INDEX IDX_5A8A6C8D7E3C61F9 (owner_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE post_category (
          post_id INT NOT NULL,
          category_id INT NOT NULL,
          INDEX IDX_B9A190604B89032C (post_id),
          INDEX IDX_B9A1906012469DE2 (category_id),
          PRIMARY KEY(post_id, category_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE post_audience (
          id INT AUTO_INCREMENT NOT NULL,
          post_id INT NOT NULL,
          UNIQUE INDEX UNIQ_42201DB94B89032C (post_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE post_audience_user (
          post_audience_id INT NOT NULL,
          user_id INT NOT NULL,
          INDEX IDX_9510281268CC4C39 (post_audience_id),
          INDEX IDX_95102812A76ED395 (user_id),
          PRIMARY KEY(post_audience_id, user_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE post_modification (
          id INT AUTO_INCREMENT NOT NULL,
          post_id INT NOT NULL,
          content LONGTEXT NOT NULL,
          created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          INDEX IDX_445958014B89032C (post_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE post_reaction (
          id INT AUTO_INCREMENT NOT NULL,
          post_id INT NOT NULL,
          owner_id INT NOT NULL,
          TYPE VARCHAR(255) NOT NULL,
          INDEX IDX_1B3A8E564B89032C (post_id),
          INDEX IDX_1B3A8E567E3C61F9 (owner_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request (
          id INT AUTO_INCREMENT NOT NULL,
          user_id INT NOT NULL,
          selector VARCHAR(20) NOT NULL,
          hashed_token VARCHAR(100) NOT NULL,
          requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          INDEX IDX_7CE748AA76ED395 (user_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE snooze (
          id INT AUTO_INCREMENT NOT NULL,
          snoozed_user_id INT DEFAULT NULL,
          snoozing_user_id INT DEFAULT NULL,
          INDEX IDX_D2B0780368396DE3 (snoozed_user_id),
          INDEX IDX_D2B07803E25ED19B (snoozing_user_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (
          id INT AUTO_INCREMENT NOT NULL,
          username VARCHAR(180) NOT NULL,
          roles JSON NOT NULL,
          PASSWORD VARCHAR(255) NOT NULL,
          email VARCHAR(255) NOT NULL,
          gender VARCHAR(255) NOT NULL,
          born_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          joined_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          slug VARCHAR(255) NOT NULL,
          is_verified TINYINT(1) NOT NULL,
          last_logged_in_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          last_link_requested_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          blocked_till DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          UNIQUE INDEX UNIQ_8D93D649E7927C74 (email),
          UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (username),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE warning (
          id INT AUTO_INCREMENT NOT NULL,
          user_id INT NOT NULL,
          reason LONGTEXT NOT NULL,
          created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          INDEX IDX_404E9CC6A76ED395 (user_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (
          id BIGINT AUTO_INCREMENT NOT NULL,
          body LONGTEXT NOT NULL,
          headers LONGTEXT NOT NULL,
          queue_name VARCHAR(190) NOT NULL,
          created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          INDEX IDX_75EA56E0FB7336F0 (queue_name),
          INDEX IDX_75EA56E0E3BD61CE (available_at),
          INDEX IDX_75EA56E016BA31DB (delivered_at),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          block
        ADD
          CONSTRAINT FK_831B97221EBCBB63 FOREIGN KEY (blocked_user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE
          block
        ADD
          CONSTRAINT FK_831B9722F90D76D FOREIGN KEY (blocking_user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE
          COMMENT
        ADD
          CONSTRAINT FK_9474526C4B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE
          COMMENT
        ADD
          CONSTRAINT FK_9474526C7E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE
          COMMENT
        ADD
          CONSTRAINT FK_9474526C5D3F9342 FOREIGN KEY (responded_comment_id) REFERENCES COMMENT (id)');
        $this->addSql('ALTER TABLE
          comment_reaction
        ADD
          CONSTRAINT FK_B99364F1F8697D13 FOREIGN KEY (comment_id) REFERENCES COMMENT (id)');
        $this->addSql('ALTER TABLE
          comment_reaction
        ADD
          CONSTRAINT FK_B99364F17E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE
          hidden_comment
        ADD
          CONSTRAINT FK_A7173D9F8697D13 FOREIGN KEY (comment_id) REFERENCES COMMENT (id)');
        $this->addSql('ALTER TABLE
          hidden_comment
        ADD
          CONSTRAINT FK_A7173D9A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE
          hidden_post
        ADD
          CONSTRAINT FK_D3B171214B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE
          hidden_post
        ADD
          CONSTRAINT FK_D3B17121A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE
          post
        ADD
          CONSTRAINT FK_5A8A6C8D7E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE
          post_category
        ADD
          CONSTRAINT FK_B9A190604B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          post_category
        ADD
          CONSTRAINT FK_B9A1906012469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          post_audience
        ADD
          CONSTRAINT FK_42201DB94B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE
          post_audience_user
        ADD
          CONSTRAINT FK_9510281268CC4C39 FOREIGN KEY (post_audience_id) REFERENCES post_audience (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          post_audience_user
        ADD
          CONSTRAINT FK_95102812A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          post_modification
        ADD
          CONSTRAINT FK_445958014B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE
          post_reaction
        ADD
          CONSTRAINT FK_1B3A8E564B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE
          post_reaction
        ADD
          CONSTRAINT FK_1B3A8E567E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE
          reset_password_request
        ADD
          CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE
          snooze
        ADD
          CONSTRAINT FK_D2B0780368396DE3 FOREIGN KEY (snoozed_user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE
          snooze
        ADD
          CONSTRAINT FK_D2B07803E25ED19B FOREIGN KEY (snoozing_user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE
          warning
        ADD
          CONSTRAINT FK_404E9CC6A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('DROP TABLE blog_categories');
        $this->addSql('DROP TABLE users');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE blog_categories (
          id INT AUTO_INCREMENT NOT NULL,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          icon LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE users (
          id INT AUTO_INCREMENT NOT NULL,
          username VARCHAR(180) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          roles JSON NOT NULL,
          PASSWORD VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          gender VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          born_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          joined_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          slug VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          is_verified TINYINT(1) NOT NULL,
          last_logged_in_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          last_link_requested_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          blocked_till DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          UNIQUE INDEX UNIQ_E46CE621E7927C74 (email),
          UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (username),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE block DROP FOREIGN KEY FK_831B97221EBCBB63');
        $this->addSql('ALTER TABLE block DROP FOREIGN KEY FK_831B9722F90D76D');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C4B89032C');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C7E3C61F9');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C5D3F9342');
        $this->addSql('ALTER TABLE comment_reaction DROP FOREIGN KEY FK_B99364F1F8697D13');
        $this->addSql('ALTER TABLE comment_reaction DROP FOREIGN KEY FK_B99364F17E3C61F9');
        $this->addSql('ALTER TABLE hidden_comment DROP FOREIGN KEY FK_A7173D9F8697D13');
        $this->addSql('ALTER TABLE hidden_comment DROP FOREIGN KEY FK_A7173D9A76ED395');
        $this->addSql('ALTER TABLE hidden_post DROP FOREIGN KEY FK_D3B171214B89032C');
        $this->addSql('ALTER TABLE hidden_post DROP FOREIGN KEY FK_D3B17121A76ED395');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D7E3C61F9');
        $this->addSql('ALTER TABLE post_category DROP FOREIGN KEY FK_B9A190604B89032C');
        $this->addSql('ALTER TABLE post_category DROP FOREIGN KEY FK_B9A1906012469DE2');
        $this->addSql('ALTER TABLE post_audience DROP FOREIGN KEY FK_42201DB94B89032C');
        $this->addSql('ALTER TABLE post_audience_user DROP FOREIGN KEY FK_9510281268CC4C39');
        $this->addSql('ALTER TABLE post_audience_user DROP FOREIGN KEY FK_95102812A76ED395');
        $this->addSql('ALTER TABLE post_modification DROP FOREIGN KEY FK_445958014B89032C');
        $this->addSql('ALTER TABLE post_reaction DROP FOREIGN KEY FK_1B3A8E564B89032C');
        $this->addSql('ALTER TABLE post_reaction DROP FOREIGN KEY FK_1B3A8E567E3C61F9');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE snooze DROP FOREIGN KEY FK_D2B0780368396DE3');
        $this->addSql('ALTER TABLE snooze DROP FOREIGN KEY FK_D2B07803E25ED19B');
        $this->addSql('ALTER TABLE warning DROP FOREIGN KEY FK_404E9CC6A76ED395');
        $this->addSql('DROP TABLE block');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE comment_reaction');
        $this->addSql('DROP TABLE hidden_comment');
        $this->addSql('DROP TABLE hidden_post');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE post_category');
        $this->addSql('DROP TABLE post_audience');
        $this->addSql('DROP TABLE post_audience_user');
        $this->addSql('DROP TABLE post_modification');
        $this->addSql('DROP TABLE post_reaction');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE snooze');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE warning');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
