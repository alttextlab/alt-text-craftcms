<?php

namespace alttextlab\AltTextLab\records;

use craft\db\ActiveRecord;

class AltTextLabBulkGeneration extends ActiveRecord
{
    public const tableName = '{{%alt-text-lab_bulk_generation}}';

    public static function tableName(): string
    {
        return '{{%alt-text-lab_bulk_generation}}';
    }

}