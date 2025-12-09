<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251209161305 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create tables: ai_conversation, exercise, sandbox_log, user, user_exercise_progress';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ai_conversation (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, context_type VARCHAR(20) NOT NULL, exercise_id INT DEFAULT NULL, database_type VARCHAR(20) DEFAULT NULL, user_message LONGTEXT NOT NULL, ai_response LONGTEXT NOT NULL, metadata JSON NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_D13CEF47A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE exercise (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, instructions LONGTEXT NOT NULL, database_type VARCHAR(20) NOT NULL, initial_schema LONGTEXT NOT NULL, expected_result LONGTEXT NOT NULL, difficulty INT NOT NULL, order_number INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sandbox_log (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, database_type VARCHAR(20) NOT NULL, query LONGTEXT NOT NULL, result LONGTEXT DEFAULT NULL, error LONGTEXT DEFAULT NULL, execution_time INT NOT NULL, executed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_successful TINYINT(1) NOT NULL, operation_type VARCHAR(50) DEFAULT NULL, INDEX IDX_3D5BFB49A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, username VARCHAR(100) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX uniq_email (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_exercise_progress (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, exercise_id INT NOT NULL, status VARCHAR(20) NOT NULL, user_query LONGTEXT DEFAULT NULL, started_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', completed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', attempts INT DEFAULT NULL, score DOUBLE PRECISION DEFAULT NULL, INDEX IDX_DC72E985A76ED395 (user_id), INDEX IDX_DC72E985E934951A (exercise_id), UNIQUE INDEX uniq_user_exercise (user_id, exercise_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ai_conversation ADD CONSTRAINT FK_D13CEF47A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sandbox_log ADD CONSTRAINT FK_3D5BFB49A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_exercise_progress ADD CONSTRAINT FK_DC72E985A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_exercise_progress ADD CONSTRAINT FK_DC72E985E934951A FOREIGN KEY (exercise_id) REFERENCES exercise (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ai_conversation DROP FOREIGN KEY FK_D13CEF47A76ED395');
        $this->addSql('ALTER TABLE sandbox_log DROP FOREIGN KEY FK_3D5BFB49A76ED395');
        $this->addSql('ALTER TABLE user_exercise_progress DROP FOREIGN KEY FK_DC72E985A76ED395');
        $this->addSql('ALTER TABLE user_exercise_progress DROP FOREIGN KEY FK_DC72E985E934951A');
        $this->addSql('DROP TABLE ai_conversation');
        $this->addSql('DROP TABLE exercise');
        $this->addSql('DROP TABLE sandbox_log');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_exercise_progress');
    }
}
