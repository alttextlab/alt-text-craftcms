<?php

namespace alttextlab\AltTextLab\models;

use alttextlab\AltTextLab\AltTextLab;
use craft\base\Model;
use craft\elements\Asset;
use DateTime;

class AltTextLabAsset extends Model
{
    public ?int $id = null;
    public ?int $assetId = null;
    public ?int $bulkGenerationId = null;
    public ?int $responseId = null;
    public ?string $generatedAltText = null;
    public ?DateTime $dateCreated = null;

    protected function defineRules(): array
    {
        return array_merge(parent::defineRules(), [
            // ...
        ]);
    }

    public function getCurrentAltText()
    {
        $asset = Asset::find()->id($this->assetId)->one();
        $settings = AltTextLab::getInstance()->getSettings();

        if ($asset) {
            if (isset($settings->customField) && ($settings->customField == "alt" || $settings->customField == null)) {

                $currentAltText = $asset->alt;
            } elseif ($asset->getFieldLayout()->getFieldByHandle($settings->customField)) {

                $currentAltText = $asset->getFieldValue($settings->customField);
            } else {
                $currentAltText = $asset->alt;
            }
        }

        return $currentAltText ?? null;
    }

    public function getIsExistCurrentSelectedFieldInAsset()
    {
        $asset = Asset::find()->id($this->assetId)->one();
        $settings = AltTextLab::getInstance()->getSettings();

        if ($asset) {
            if (isset($settings->customField) && ($settings->customField == "alt" || $settings->customField == null)) {
                return true;
            } elseif ($asset->getFieldLayout()->getFieldByHandle($settings->customField)) {

                return true;
            }
        }

        return false;
    }
}