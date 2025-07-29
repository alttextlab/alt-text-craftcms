<?php

namespace alttextlab\AltTextLab\models;

use craft\base\Model;
use DateTime;

class AltTextLabLog extends Model
{

    public ?int $id = null;
    public ?int $assetId = null;
    public ?string $logMessage = null;
    public ?DateTime $dateCreated = null;

    protected function defineRules(): array
    {
        return array_merge(parent::defineRules(), [
            // ...
        ]);
    }

}