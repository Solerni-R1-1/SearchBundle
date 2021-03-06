<?php

namespace Orange\SearchBundle\Migrations\ibm_db2;

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
                id INTEGER GENERATED BY DEFAULT AS IDENTITY NOT NULL, 
                entity_id INTEGER DEFAULT NULL, 
                document_id VARCHAR(255) DEFAULT NULL, 
                class_name VARCHAR(255) NOT NULL, 
                status INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE orange_search_sync_index
        ");
    }
}