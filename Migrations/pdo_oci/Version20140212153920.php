<?php

namespace Orange\SearchBundle\Migrations\pdo_oci;

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
                id NUMBER(10) NOT NULL, 
                entity_id NUMBER(10) DEFAULT NULL, 
                document_id VARCHAR2(255) DEFAULT NULL, 
                class_name VARCHAR2(255) NOT NULL, 
                status NUMBER(10) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'ORANGE_SEARCH_SYNC_INDEX' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE ORANGE_SEARCH_SYNC_INDEX ADD CONSTRAINT ORANGE_SEARCH_SYNC_INDEX_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE ORANGE_SEARCH_SYNC_INDEX_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER ORANGE_SEARCH_SYNC_INDEX_AI_PK BEFORE INSERT ON ORANGE_SEARCH_SYNC_INDEX FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT ORANGE_SEARCH_SYNC_INDEX_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT ORANGE_SEARCH_SYNC_INDEX_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'ORANGE_SEARCH_SYNC_INDEX_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT ORANGE_SEARCH_SYNC_INDEX_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE orange_search_sync_index
        ");
    }
}