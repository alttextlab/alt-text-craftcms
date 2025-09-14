<?php

namespace alttextlab\AltTextLab\records;

use craft\db\ActiveRecord;

class AltTextLabAsset extends ActiveRecord
{
    public const tableName = '{{%alttextlab_asset}}';

    public static function tableName(): string
    {
        return '{{%alttextlab_asset}}';
    }
}