<?php

namespace alttextlab\AltTextLab\models;

use craft\base\Model;
use craft\helpers\App;

class Settings extends Model
{
    public ?string $apiKey = null;
    public ?bool $onUploadGenerate = false;
    public ?string $customField = 'alt';
    public ?string $modelName = "describe-regular";
    public ?string $lang = "en";
    public ?bool $isPublic = false;

    public ?int $itemPerPage = 10;

    public function getApiKey(bool $parse = true): string
    {
        return ($parse ? App::parseEnv($this->apiKey) : $this->apiKey) ?? '';
    }

    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

}
