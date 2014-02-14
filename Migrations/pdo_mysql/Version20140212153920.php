<?php

namespace Orange\SearchBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/02/12 03:39:21
 */
class Version20140212153920 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE orange_search_sync_index (
                id INT AUTO_INCREMENT NOT NULL, 
                entity_id INT DEFAULT NULL, 
                document_id VARCHAR(255) DEFAULT NULL, 
                class_name VARCHAR(255) NOT NULL, 
                status INT NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE orange_search_sync_index
        ");
    }
}