<?php

namespace alttextlab\AltTextLab\migrations;

use craft\db\Migration;
use craft\db\Table;
use alttextlab\AltTextLab\records\AltTextLabBulkGeneration;
use alttextlab\AltTextLab\records\AltTextLabAsset;
use alttextlab\AltTextLab\records\AltTextLabLog;

class Install extends Migration
{

    public function safeUp(): bool
    {

        $this->createTable(
            AltTextLabBulkGeneration::tableName,
            [
                'id' => $this->primaryKey(),
                'countOfImages' => $this->integer()->notNull(),
                'dateCreated' => $this->dateTime()->notNull(),
            ]
        );

        $this->createTable(
            AltTextLabAsset::tableName,
            [
                'id' => $this->primaryKey(),
                'assetId' => $this->integer()->notNull(),
                'bulkGenerationId' => $this->integer(),
                'responseId' => $this->string(128)->notNull(),
                'generatedAltText' => $this->string(512)->defaultValue(null),
                'dateCreated' => $this->dateTime()->notNull(),
            ]
        );

        $this->addForeignKey(
            null,
            AltTextLabAsset::tableName,
            ['bulkGenerationId'],
            AltTextLabBulkGeneration::tableName,
            ['id'],
            'CASCADE'
        );

        $this->addForeignKey(
            null,
            AltTextLabAsset::tableName,
            ['assetId'],
            Table::ELEMENTS,
            ['id'],
            'CASCADE'
        );

        $this->createTable(
            AltTextLabLog::tableName,
            [
                'id' => $this->primaryKey(),
                'assetId' => $this->integer()->notNull(),
                'bulkGenerationId' => $this->integer(),
                'logMessage' => $this->string(512)->defaultValue(null),
                'dateCreated' => $this->dateTime()->notNull(),
            ]
        );

        $this->addForeignKey(
            null,
            AltTextLabLog::tableName,
            ['bulkGenerationId'],
            AltTextLabBulkGeneration::tableName,
            ['id'],
            'CASCADE'
        );


        $this->addForeignKey(
            null,
            AltTextLabLog::tableName,
            ['assetId'],
            Table::ELEMENTS,
            ['id'],
            'CASCADE'
        );


        return true;
    }

    public function safeDown(): bool
    {
        $this->dropTableIfExists(AltTextLabAsset::tableName);
        $this->dropTableIfExists(AltTextLabLog::tableName);
        $this->dropTableIfExists(AltTextLabBulkGeneration::tableName);
        return true;
    }

}