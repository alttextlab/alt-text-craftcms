<?php

namespace alttextlab\AltTextLab\records;

use craft\db\ActiveRecord;

class AltTextLabAsset extends ActiveRecord
{
    public const tableName = '{{%alt-text-lab_asset}}';

    public static function tableName(): string
    {
        return '{{%alt-text-lab_asset}}';
    }
}