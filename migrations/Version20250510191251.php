<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250510191251 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Convertit la colonne roles en type ENUM';
    }

    public function up(Schema $schema): void
    {
        // Cette requête SQL convertit directement la colonne roles de VARCHAR à ENUM
        $this->addSql('ALTER TABLE user MODIFY roles ENUM(\'ROLE_ADMIN\', \'ROLE_USER\', \'ROLE_MODERATOR\', \'\') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT \'(DC2Type:role_enum)\'');
    }

    public function down(Schema $schema): void
    {
        // Cette requête permet de revenir en arrière si nécessaire
        $this->addSql('ALTER TABLE user MODIFY roles VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT \'(DC2Type:role_enum)\'');
    }
}
