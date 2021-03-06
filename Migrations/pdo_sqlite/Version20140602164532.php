<?php

namespace Orange\SearchBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/02 04:45:35
 */
class Version20140602164532 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE orange_search_entity_to_index (
                id INTEGER NOT NULL, 
                class_name VARCHAR(255) NOT NULL, 
                to_index BOOLEAN NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE orange_search_entity_to_index
        ");
    }
}