<?php

namespace alttextlab\AltTextLab\records;

use craft\db\ActiveRecord;

class AltTextLabLog extends ActiveRecord
{

    public const tableName = '{{%alt-text-lab_log}}';

    public static function tableName(): string
    {
        return '{{%alt-text-lab_log}}';
    }

}