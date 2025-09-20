<?php

namespace alttextlab\AltTextLab\services;

use Craft;
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

        $disabledIds = $this->getVolumesDisabledIds();
        if (!empty($disabledIds)) {
            $query->volumeId(['not', ...$disabledIds]);
        }

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

        $disabledIds = $this->getVolumesDisabledIds();
        if (!empty($disabledIds)) {
            $assetsQuery->volumeId(['not', ...$disabledIds]);
        }

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

        $disabledIds = $this->getVolumesDisabledIds();
        if (!empty($disabledIds)) {
            $query->volumeId(['not', ...$disabledIds]);
        }

        $resultAssets = array();

        if (!empty($assetIds)) {
            $query->id($assetIds);
        }

        if (empty($altFieldHandle) || $altFieldHandle === 'alt') {
            if (!$hasAltText){
                $query->hasAlt($hasAltText);
            }
            $resultAssets = $query->all();
        } else {
            foreach ($query->each() as $asset) {
                $fieldLayout = $asset->getFieldLayout();

                if (!$fieldLayout || !$fieldLayout->getFieldByHandle($altFieldHandle)) {
                    continue;
                }

                $value = trim((string)$asset->getFieldValue($altFieldHandle));

                if (!$hasAltText && $value === '') {
                    $resultAssets[] = $asset;
                }else{
                    $resultAssets[] = $asset;
                }
            }
        }

        return $resultAssets;
    }

    private function getVolumesDisabledIds()
    {
        $disabledIds = [];
        $settings = AltTextLab::getInstance()->getSettings();

        $disabledUids = $settings->disabledVolumeUids ?? [];
        if (!empty($disabledUids)) {
            $volService = Craft::$app->getVolumes();

            foreach ($disabledUids as $uid) {
                if ($volume = $volService->getVolumeByUid($uid)) {
                    $disabledIds[] = $volume->id;
                }
            }
        }

        return $disabledIds;
    }

}