<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250604112021 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE attendance ADD CONSTRAINT FK_6DE30D91CB944F1A FOREIGN KEY (student_id) REFERENCES student (student_number)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE log ADD CONSTRAINT FK_8F3F68C564DE5A5 FOREIGN KEY (media_object_id) REFERENCES media_object (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE attendance DROP FOREIGN KEY FK_6DE30D91CB944F1A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C564DE5A5
        SQL);
    }
}
