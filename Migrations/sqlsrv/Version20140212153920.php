<?php

namespace Orange\SearchBundle\Migrations\sqlsrv;

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
                id INT IDENTITY NOT NULL, 
                entity_id INT, 
                document_id NVARCHAR(255), 
                class_name NVARCHAR(255) NOT NULL, 
                status INT NOT NULL, 
                PRIMARY KEY (id)
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