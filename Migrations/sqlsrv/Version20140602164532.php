<?php

namespace Orange\SearchBundle\Migrations\sqlsrv;

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
                id INT IDENTITY NOT NULL, 
                class_name NVARCHAR(255) NOT NULL, 
                to_index BIT NOT NULL, 
                PRIMARY KEY (id)
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