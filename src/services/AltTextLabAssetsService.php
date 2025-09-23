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

    private $LOG_FAILED_MESSAGE = 'Something went wrong. Make sure your image is public, or disable 
    “This site is reachable over the public internet” in Plugin Settings if your site is private or hosted locally.';
    private $logService;
    private $utilityService;
    private $apiService;

    public function __construct()
    {
        $this->logService = new LogService();
        $this->utilityService = new UtilityService();
        $this->apiService = new ApiService();
    }

    public function getAllAssets($filters = [], &$thereIsWithoutURL)
    {
        $recordsQuery = AltTextLabAssetRecord::find();

        if (array_key_exists('bulkGenerationId', $filters) && $filters['bulkGenerationId']){
            $recordsQuery->where(['bulkGenerationId' => $filters['bulkGenerationId']]);
        }

        if (array_key_exists('limit', $filters)) {
            $recordsQuery->limit($filters['limit']);
        }
        if (array_key_exists('offset', $filters)) {
            $recordsQuery->offset($filters['offset']);
        }

        $recordsQuery->orderBy(['id' => SORT_DESC]);

        $records = $recordsQuery->all();

        $models = array();

        foreach ($records as $record) {
            $model = new AltTextLabAssetModel($record->getAttributes());

            $asset = Asset::find()->id($model->assetId)->one();
            if (!$asset || !$asset->getUrl()) {
                $thereIsWithoutURL = true;
            }

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
            'bulkGenerationId',
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

    public function buildModel($assetId, $bulkGenerationId, $responseId, $generatedAltText): AltTextLabAssetModel
    {
        $model = new AltTextLabAssetModel();
        $model->assetId = $assetId;
        $model->bulkGenerationId = $bulkGenerationId;
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

    public function getTotalCount(array $conditions = []): int
    {
        $recordsQuery = AltTextLabAssetRecord::find();

        if (!empty($conditions)) {
            $recordsQuery->where($conditions);
        }

        return $recordsQuery->count();
    }

    public function generateAltText($assetId, $bulkGenerationId): void
    {
        try {
            $asset = Asset::find()->id($assetId)->one();
            if (!$asset) {
                return;
            }

            $settings = AltTextLab::getInstance()->getSettings();
            $disabledVolumeUids = $settings->disabledVolumeUids ?? [];
            $assetVolumeUid = $asset->getVolume()->uid ?? null;
            if ($assetVolumeUid && in_array($assetVolumeUid, $disabledVolumeUids, true)) {
                return;
            }

            if (!$this->utilityService->checkAssetIsValid($asset, $bulkGenerationId)) {
                return;
            }

            $callDetails = $this->prepareApiRequestData($asset, $bulkGenerationId, $settings);
            if (!$callDetails) {
                return;
            }

            $responseArray = $this->apiService->generateAltText($callDetails);

            if ($responseArray == 'API_KEY_IS_INVALID'){
                $this->logService->log($asset->id, $bulkGenerationId, 'Api Key is invalid!');
                return;
            }

            if ($responseArray == 'NOT_ENOUGH_FUNDS'){
                $this->logService->log($asset->id, $bulkGenerationId, 'You dont have enough funds to generate alt text!');
                return;
            }

            $responseArray = json_decode($responseArray, true);

            if (!isset($responseArray['result'])) {
                $this->logService->log($asset->id, $bulkGenerationId, $this->LOG_FAILED_MESSAGE);
                return;
            }

            $this->setAltTextOnAsset($asset, $responseArray['result'], $settings);
            $success = Craft::$app->elements->saveElement($asset);

            if ($success) {
                $this->saveAsset($this->buildModel($assetId, $bulkGenerationId, $responseArray['id'], $responseArray['result']));
            }
        } catch (\Throwable $e) {
            Craft::error('Alt text generation error: ' . $e->getMessage(), __METHOD__);
            $this->logService->log($assetId, $bulkGenerationId, $this->LOG_FAILED_MESSAGE);
        }
    }

    private function prepareApiRequestData($asset, $bulkGenerationId, $settings): ?array
    {
        if ($settings->isPublic) {
            $imageUrl = UrlHelper::siteUrl($asset->url);

            if (!$imageUrl){
                $this->logService->log($asset->id, $bulkGenerationId,"Asset doesn't have URL.");
                return null;
            }

            $body = ['imageUrl' => $imageUrl];
        } else {
            $fsPath = Craft::getAlias($asset->getVolume()->fs->path ?? '');
            $subpath = Craft::parseEnv($asset->getVolume()->subpath ?? '');

            $fsPath = rtrim($fsPath, DIRECTORY_SEPARATOR);
            $subpath = trim($subpath, DIRECTORY_SEPARATOR);

            $rootPath = $subpath ? ($fsPath . DIRECTORY_SEPARATOR . $subpath) : $fsPath;

            $filePath = rtrim($rootPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($asset->getPath(), DIRECTORY_SEPARATOR);

            if (!file_exists($filePath)) {
                $this->logService->log($asset->id, $bulkGenerationId, "File doesn't exist: " . $filePath);
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