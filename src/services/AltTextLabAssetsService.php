<?php

namespace alttextlab\AltTextLab\services;


use Craft;
use craft\elements\Asset;
use craft\helpers\UrlHelper;
use alttextlab\AltTextLab\models\AltTextLabAsset;
use alttextlab\AltTextLab\models\AltTextLabAsset as AltTextLabAssetModel;
use alttextlab\AltTextLab\records\AltTextLabAsset as AltTextLabAssetRecord;
use alttextlab\AltTextLab\AltTextLab;

class AltTextLabAssetsService
{

    private $logService;
    private $utilityService;
    private $apiService;

    public function __construct()
    {
        $this->logService = new LogService();
        $this->utilityService = new UtilityService();
        $this->apiService = new ApiService();
    }

    public function getAllAssets($filters = [])
    {
        $recordsQuery = AltTextLabAssetRecord::find();

        if (array_key_exists('limit', $filters)) {
            $recordsQuery->limit($filters['limit']);
        }
        if (array_key_exists('offset', $filters)) {
            $recordsQuery->offset($filters['offset']);
        }

        $records = $recordsQuery->all();

        $models = array();

        foreach ($records as $record) {
            $model = new AltTextLabAssetModel($record->getAttributes());
            $models[] = $model;
        }

        return $models;
    }

    public function saveAsset(AltTextLabAssetModel $model): AltTextLabAssetModel
    {
        $isNew = !$model->id;

        if (!$isNew) {
            $record = AltTextLabAssetRecord::findOne(['id' => $model->id]);
        } else {
            $record = new AltTextLabAssetRecord();
        }


        $fieldsToUpdate = [
            'assetId',
            'responseId',
            'generatedAltText',
        ];

        foreach ($fieldsToUpdate as $handle) {
            if (property_exists($model, $handle)) {
                $record->$handle = $model->$handle;
            }
        }

        $record->validate();
        $model->addErrors($record->getErrors());


        $record->save(false);

        if ($isNew) {
            $model->id = $record->id;
        }

        return  $model;
    }

    public function getAssetById($assetId): AltTextLabAssetModel
    {
        $record = AltTextLabAssetRecord::findOne($assetId);

        $assetModel = new AltTextLabAssetModel($record->getAttributes());

        return  $assetModel;
    }

    public function buildModel($assetId, $responseId, $generatedAltText): AltTextLabAssetModel
    {
        $model = new AltTextLabAsset();
        $model->assetId = $assetId;
        $model->responseId = (int)($responseId ?? 0);
        $model->generatedAltText = $generatedAltText;

        return $model;
    }

    public function changeCraftAssetAltByAssetId($assetId): bool
    {
        $assetModel = $this->getAssetById($assetId);

        $asset = Asset::find()->id($assetModel->assetId)->one();
        $settings = AltTextLab::getInstance()->getSettings();
        if(isset($settings->customField) && ( $settings->customField == "alt" || $settings->customField == null ))
        {
            $asset->alt = $assetModel->generatedAltText;
        }
        elseif( $asset->getFieldLayout()->getFieldByHandle($settings->customField) )
        {
            $asset->setFieldValue($settings->customField, $assetModel->generatedAltText );
        }
        else{
            $asset->alt = $assetModel->generatedAltText;
        }

        $success = Craft::$app->elements->saveElement($asset);

        return $success;
    }

    public function getTotalCount(): int
    {
        $recordsQuery = AltTextLabAssetRecord::find();
        return $recordsQuery->count();
    }

    public function generateAltText($assetId): void
    {
        try {
            $asset = Asset::find()->id($assetId)->one();
            if (!$asset) {
                return;
            }

            if (!$this->utilityService->checkAssetIsValid($asset)) {
                return;
            }

            $settings = AltTextLab::getInstance()->getSettings();

            $callDetails = $this->prepareApiRequestData($asset, $settings);
            if (!$callDetails) {
                return;
            }

            $responseJson = $this->apiService->generateAltText($callDetails);
            $responseArray = json_decode($responseJson, true);

            if (!isset($responseArray['result'])) {
                $this->logService->log($asset->id, 'Alt text doesnt generated for image.');
                return;
            }

            $this->setAltTextOnAsset($asset, $responseArray['result'], $settings);
            $success = Craft::$app->elements->saveElement($asset);

            if ($success) {
                $this->saveAsset($this->buildModel($assetId, $responseArray['id'], $responseArray['result']));
            }

            Craft::info('Alt text generated: ' . print_r($responseArray, true), __METHOD__);

        } catch (\Throwable $e) {
            Craft::error('AltText generation error: ' . $e->getMessage(), __METHOD__);
            $this->logService->log($assetId, $e->getMessage());
        }
    }

    private function prepareApiRequestData($asset, $settings): ?array
    {
        if ($settings->isPublic) {
            $imageUrl = UrlHelper::siteUrl($asset->url);

            if (!$imageUrl){
                $this->logService->log($asset->id, 'Asset doesnt have URL.');
                return null;
            }

            $body = ['imageUrl' => $imageUrl];
        } else {
            $fsPath = Craft::getAlias($asset->getVolume()->fs->path);
            $filePath = $fsPath . DIRECTORY_SEPARATOR . $asset->getPath();

            if (!file_exists($filePath)) {
                $this->logService->log($asset->id, 'File does not exist: ' . $filePath);
                return null;
            }

            $content = file_get_contents($filePath);
            $base64 = base64_encode($content);
            $body = ['imageRaw' => $base64];
        }

        return array_merge($body, [
            'source' => 'craftcms',
            'style'  => $settings->modelName,
            'lang'   => $settings->lang
        ]);
    }

    private function setAltTextOnAsset($asset, string $altText, $settings): void
    {
        $customField = $settings->customField;

        if (empty($customField) || $customField === 'alt') {
            $asset->alt = $altText;
        } elseif ($asset->getFieldLayout()?->getFieldByHandle($customField)) {
            $asset->setFieldValue($customField, $altText);
        } else {
            $asset->alt = $altText;
        }
    }


}