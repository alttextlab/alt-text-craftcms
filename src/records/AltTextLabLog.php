<?php

namespace alttextlab\AltTextLab\records;

use craft\db\ActiveRecord;

class AltTextLabLog extends ActiveRecord
{

    public const tableName = '{{%alttextlab_log}}';

    public static function tableName(): string
    {
        return '{{%alttextlab_log}}';
    }

}