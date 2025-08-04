<?php

namespace alttextlab\AltTextLab\services;

use alttextlab\AltTextLab\AltTextLab;
use craft\elements\Asset as AssetElement;

class CraftAssetsService
{

    public function getCountAssets(array $assetIds = []): int
    {
        $settings = AltTextLab::getInstance()->getSettings();
        $altFieldHandle = $settings->customField;
        $count = 0;

        $query = AssetElement::find()
            ->kind('image');

        if (!empty($assetIds)) {
            $query->id($assetIds);
        }

        if (empty($altFieldHandle) || $altFieldHandle === 'alt') {
            $count = $query->count();
        } else {
            foreach ($query->each() as $asset) {
                $fieldLayout = $asset->getFieldLayout();

                if (!$fieldLayout || !$fieldLayout->getFieldByHandle($altFieldHandle)) {
                    continue;
                }

                $count++;
            }
        }

        return $count;
    }


    public function getCountAssetsByAltTextFilter(bool $hasAltText, array $assetIds = []): int
    {
        $settings = AltTextLab::getInstance()->getSettings();
        $altFieldHandle = $settings->customField;
        $count = 0;

        $assetsQuery = AssetElement::find()
            ->kind('image');

        if (!empty($assetIds)) {
            $assetsQuery->id($assetIds);
        }

        if (empty($altFieldHandle) || $altFieldHandle === 'alt') {
            $assetsQuery->hasAlt($hasAltText);
            $count = $assetsQuery->count();
        } else {
            foreach ($assetsQuery->each() as $asset) {
                $fieldLayout = $asset->getFieldLayout();

                if (!$fieldLayout || !$fieldLayout->getFieldByHandle($altFieldHandle)) {
                    continue;
                }

                $value = trim((string)$asset->getFieldValue($altFieldHandle));

                if ($hasAltText && $value !== '') {
                    $count++;
                } elseif (!$hasAltText && $value === '') {
                    $count++;
                }
            }
        }

        return $count;
    }

    public function getAssetsByAltTextFilter(bool $hasAltText, array $assetIds = []): array
    {
        $settings = AltTextLab::getInstance()->getSettings();
        $altFieldHandle = $settings->customField;

        $query = AssetElement::find()
            ->kind('image');

        $resultAssets = array();

        if (!empty($assetIds)) {
            $query->id($assetIds);
        }

        if (empty($altFieldHandle) || $altFieldHandle === 'alt') {
            $query->hasAlt($hasAltText);
            $resultAssets = $query->all();
        } else {
            foreach ($query->each() as $asset) {
                $fieldLayout = $asset->getFieldLayout();

                if (!$fieldLayout || !$fieldLayout->getFieldByHandle($altFieldHandle)) {
                    continue;
                }

                $value = trim((string)$asset->getFieldValue($altFieldHandle));

                if ($hasAltText && $value !== '') {
                    $resultAssets[] = $asset;
                } elseif (!$hasAltText && $value === '') {
                    $resultAssets[] = $asset;
                }
            }
        }

        return $resultAssets;
    }

}