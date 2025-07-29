<?php

namespace alttextlab\AltTextLab\services;

use craft\elements\Asset as AssetElement;

class CraftAssetsService
{

    public function getCountAssets(array $assetIds = []): int
    {
        $query = AssetElement::find()
            ->kind('image');

        if (!empty($assetIds)) {
            $query->id($assetIds);
        }

        return $query->count();
    }

    public function getCountAssetsByAltTextFilter($hasAltText, array $assetIds = []): int
    {
        $query = AssetElement::find()
            ->kind('image')
            ->hasAlt($hasAltText);

        if (!empty($assetIds)) {
            $query->id($assetIds);
        }

        return $query->count();
    }

    public function getAssetsByAltTextFilter(bool $hasAltText, array $assetIds = []): array
    {
        $query = AssetElement::find()
            ->kind('image')
            ->hasAlt($hasAltText);

        if (!empty($assetIds)) {
            $query->id($assetIds);
        }

        return $query->all();
    }

}