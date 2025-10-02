<?php

namespace alttextlab\AltTextLab\services;

use Craft;
use alttextlab\AltTextLab\AltTextLab;
use craft\elements\Asset as AssetElement;

class CraftAssetsService
{

    private $utilityService;

    public function __construct()
    {
        $this->utilityService = new UtilityService();
    }

    public function getCountAssets(array $assetIds = []): int
    {
        $settings = AltTextLab::getInstance()->getSettings();
        $altFieldHandle = $settings->customField;
        $count = 0;

        $query = AssetElement::find()
            ->kind('image');

        $disabledVolumeIds = $this->getVolumesDisabledIds();
        if (!empty($disabledVolumeIds)) {
            $query->volumeId(['not', ...$disabledVolumeIds]);
        }

        if (!empty($assetIds)) {
            $query->id($assetIds);
        }

        if (empty($altFieldHandle) || $altFieldHandle === 'alt') {
            foreach ($query->each() as $asset) {
                if ($this->utilityService->isPathExcludedByRegex($asset)) {
                    continue;
                }
                $count++;
            }
        } else {
            foreach ($query->each() as $asset) {
                if ($this->utilityService->isPathExcludedByRegex($asset)) {
                    continue;
                }

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

        $disabledVolumeIds = $this->getVolumesDisabledIds();
        if (!empty($disabledVolumeIds)) {
            $assetsQuery->volumeId(['not', ...$disabledVolumeIds]);
        }

        if (!empty($assetIds)) {
            $assetsQuery->id($assetIds);
        }

        if (empty($altFieldHandle) || $altFieldHandle === 'alt') {
            $assetsQuery->hasAlt($hasAltText);

            foreach ($assetsQuery->each() as $asset) {
                if ($this->utilityService->isPathExcludedByRegex($asset)) {
                    continue;
                }
                $count++;
            }
        } else {
            foreach ($assetsQuery->each() as $asset) {
                if ($this->utilityService->isPathExcludedByRegex($asset)) {
                    continue;
                }

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

        $disabledVolumeIds = $this->getVolumesDisabledIds();
        if (!empty($disabledVolumeIds)) {
            $query->volumeId(['not', ...$disabledVolumeIds]);
        }

        $resultAssets = array();

        if (!empty($assetIds)) {
            $query->id($assetIds);
        }

        if (empty($altFieldHandle) || $altFieldHandle === 'alt') {
            if (!$hasAltText){
                $query->hasAlt($hasAltText);
            }
            foreach ($query->each() as $asset) {
                if ($this->utilityService->isPathExcludedByRegex($asset)) {
                    continue;
                }
                $resultAssets[] = $asset;
            }
        } else {
            foreach ($query->each() as $asset) {
                if ($this->utilityService->isPathExcludedByRegex($asset)) {
                    continue;
                }

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
        $disabledVolumeIds = [];
        $settings = AltTextLab::getInstance()->getSettings();

        $disabledVolumeUid = $settings->disabledVolumeUids ?? [];
        if (!empty($disabledVolumeUid)) {
            $volService = Craft::$app->getVolumes();

            foreach ($disabledVolumeUid as $disabledVolumeUid) {
                if ($volume = $volService->getVolumeByUid($disabledVolumeUid)) {
                    $disabledVolumeIds[] = $volume->id;
                }
            }
        }

        return $disabledVolumeIds;
    }

}