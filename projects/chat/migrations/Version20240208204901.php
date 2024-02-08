<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240208204901 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add chat and chat_message tables.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE chat (id INT AUTO_INCREMENT NOT NULL, uuid CHAR(36) NOT NULL, title VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_659DF2AAD17F50A6 (uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE chat_message (id INT AUTO_INCREMENT NOT NULL, role VARCHAR(32) NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, chat_id INT NOT NULL, INDEX IDX_FAB3FC161A9A7125 (chat_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE chat_message ADD CONSTRAINT FK_FAB3FC161A9A7125 FOREIGN KEY (chat_id) REFERENCES chat (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chat_message DROP FOREIGN KEY FK_FAB3FC161A9A7125');
        $this->addSql('DROP TABLE chat');
        $this->addSql('DROP TABLE chat_message');
    }
}
