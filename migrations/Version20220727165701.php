<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220727165701 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('INSERT INTO user (username, email, roles, password) VALUES ("Admin", "admin@aoyos.com", "[\"ROLE_ADMIN\"]", "$2y$13$cO0wos67/LHbwCMP6VjvoeVdhVffYZ.aqfHKgDHs3r2V9VjjvbUUu");');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DELETE user WHERE email="admin@aoyos.com";');
    }
}