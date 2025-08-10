<?php

namespace alttextlab\AltTextLab\models;

use craft\base\Model;
use craft\elements\Asset;
use DateTime;

class AltTextLabBulkGeneration extends Model
{

    public ?int $id = null;
    public ?int $countOfImages = null;
    public ?DateTime $dateCreated = null;

    public ?int $successfulCount = null;
    public ?int $failedCount = null;

    protected function defineRules(): array
    {
        return array_merge(parent::defineRules(), [
            // ...
        ]);
    }

}