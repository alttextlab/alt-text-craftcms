<?php

namespace alttextlab\AltTextLab\services;


use Craft;
use craft\elements\Asset;
use craft\helpers\UrlHelper;
use alttextlab\AltTextLab\models\AltTextLabAsset;
use alttextlab\AltTextLab\models\AltTextLabAsset as AltTextLabAssetModel;
use alttextlab\AltTextLab\records\AltTextLabAsset as AltTextLabAssetRecord;
use alttextlab\AltTextLab\AltTextLab;
use craft\db\Query;
use craft\base\Field;

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
            'siteId'
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

    public function buildModel($assetId, $bulkGenerationId, $responseId, $generatedAltText, ?int $siteId): AltTextLabAssetModel
    {
        $model = new AltTextLabAssetModel();
        $model->assetId = $assetId;
        $model->bulkGenerationId = $bulkGenerationId;
        $model->responseId = (int)($responseId ?? 0);
        $model->generatedAltText = $generatedAltText;
        $model->siteId = $siteId;
        return $model;
    }

    public function changeCraftAssetAltByAssetId($assetId, ?int $siteId = null): bool
    {
        $assetModel = $this->getAssetById($assetId);

        if (!$siteId && !empty($assetModel->siteId)) {
            $siteId = (int)$assetModel->siteId;
        }

        $query = Asset::find()
            ->id($assetModel->assetId)
            ->status(null);

        if ($siteId) {
            $query->siteId($siteId);
        }

        $asset = $query->one();
        if (!$asset) {
            return false;
        }
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

        $success = Craft::$app->elements->saveElement($asset, true, false);

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

    public function getDistinctAssetCount(array $conditions = []): int
    {
        $query = (new Query())
            ->from(AltTextLabAssetRecord::tableName())
            ->select(['assetId'])
            ->distinct(true);

        if (!empty($conditions)) {
            $query->where($conditions);
        }

        return (int)$query->count('assetId', Craft::$app->db);
    }

    public function generateAltText($assetId, $bulkGenerationId): void
    {
        try {
            $asset = Asset::find()->id($assetId)->status(null)->one();
            if (!$asset) {
                return;
            }

            $settings = AltTextLab::getInstance()->getSettings();
            $disabledVolumeUids = $settings->disabledVolumeUids ?? [];
            $assetVolumeUid = $asset->getVolume()->uid ?? null;
            if ($assetVolumeUid && in_array($assetVolumeUid, $disabledVolumeUids, true)) {
                return;
            }

            $regexExcludePathPattern = $this->utilityService->getExcludeRegexEnv();
            if ($regexExcludePathPattern && $this->utilityService->isPathExcludedByRegex($asset, $regexExcludePathPattern)){
                return;
            }

            if (!$this->utilityService->checkAssetIsValid($asset, $bulkGenerationId)) {
                return;
            }

            $customField = (string)($settings->customField ?? 'alt');
            $supportsPerSite = false;
            $method = null;

            if ($customField === '' || $customField === 'alt') {
                $volume = $asset->getVolume();
                $method = $volume->altTranslationMethod ?? Field::TRANSLATION_METHOD_SITE;
            } else {
                $field = Craft::$app->getFields()->getFieldByHandle($customField);
                $method = $field?->translationMethod;
            }

            $supportsPerSite = in_array(
                $method,
                [Field::TRANSLATION_METHOD_SITE, Field::TRANSLATION_METHOD_LANGUAGE],
                true
            );

            if (empty($settings->autoUseSiteLanguage) || !$supportsPerSite) {
                $callDetails = $this->prepareApiRequestData($asset, $bulkGenerationId, $settings, null);
                if (!$callDetails) {
                    return;
                }

                $apiResult = $this->requestAltTextFromApi($callDetails, (int)$asset->id, $bulkGenerationId, (int)$asset->siteId);
                if (!$apiResult) {
                    return;
                }

                $this->setAltTextOnAsset($asset, $apiResult['altText'], $settings);

                $success = Craft::$app->elements->saveElement($asset, true, false);

                if ($success) {
                    $this->saveAsset($this->buildModel($assetId, $bulkGenerationId, $apiResult['responseId'], $apiResult['altText'], (int)$asset->siteId));
                }

                return;
            }

            $supportedLanguages = require AltTextLab::getInstance()->getBasePath() . '/configs/Languages.php';
            $supportedLookup = $this->utilityService->buildSupportedLanguageLookup(array_keys($supportedLanguages));

            $sites = Craft::$app->getSites()->getAllSites();
            $sitesByApiLang = [];

            foreach ($sites as $site) {
                $assetForSite = Asset::find()
                    ->id($assetId)
                    ->siteId($site->id)
                    ->status(null)
                    ->one();

                if (!$assetForSite) {
                    continue;
                }

                if (!$assetForSite->enabled || !$assetForSite->getEnabledForSite()) {
                    continue;
                }

                $apiLang = $this->utilityService->normalizeCraftLanguageToApi((string)$site->language, $supportedLookup);
                $sitesByApiLang[$apiLang][] = (int)$site->id;
            }

            if (empty($sitesByApiLang)) {
                return;
            }

            foreach ($sitesByApiLang as $apiLang => $siteIds) {
                $callDetails = $this->findCallDetailsForSiteGroup($assetId, $bulkGenerationId, $settings, $apiLang, $siteIds);

                if (!$callDetails) {
                    continue;
                }

                $apiResult = $this->requestAltTextFromApi($callDetails, (int)$asset->id, $bulkGenerationId, $siteIds[0] ?? null);
                if (!$apiResult) {
                    continue;
                }

                foreach ($siteIds as $siteId) {
                    $assetForSave = Asset::find()
                        ->id($assetId)
                        ->siteId($siteId)
                        ->status(null)
                        ->one();

                    if (!$assetForSave) {
                        continue;
                    }

                    $this->setAltTextOnAsset($assetForSave, $apiResult['altText'], $settings);

                    $saved = Craft::$app->elements->saveElement($assetForSave, true, false);
                    if ($saved) {
                        $this->saveAsset($this->buildModel($assetId, $bulkGenerationId, $apiResult['responseId'], $apiResult['altText'], (int)$siteId));
                    }
                }
            }
        } catch (\Throwable $e) {
            Craft::error('Alt text generation error: ' . $e->getMessage(), __METHOD__);
            $siteId = isset($asset) ? ((int)($asset->siteId ?? 0) ?: null) : null;
            $this->logService->log($assetId, $bulkGenerationId, $this->LOG_FAILED_MESSAGE, $siteId);
        }
    }

    /**
     * Calls API and returns normalized payload for saving.
     */
    private function requestAltTextFromApi(array $callDetails, int $assetId, $bulkGenerationId, ?int $siteId = null): ?array
    {
        $response = $this->apiService->generateAltText($callDetails);

        if ($response === 'API_KEY_IS_INVALID') {
            $this->logService->log($assetId, $bulkGenerationId, 'Api Key is invalid!', $siteId);
            return null;
        }

        if ($response === 'NOT_ENOUGH_FUNDS') {
            $this->logService->log($assetId, $bulkGenerationId, 'You dont have enough funds to generate alt text!', $siteId);
            return null;
        }

        $json = json_decode($response, true);

        if (!is_array($json) || !isset($json['result'])) {
            $this->logService->log($assetId, $bulkGenerationId, $this->LOG_FAILED_MESSAGE, $siteId);
            return null;
        }

        return [
            'altText' => (string)$json['result'],
            'responseId' => (int)($json['id'] ?? 0),
        ];
    }

    private function findCallDetailsForSiteGroup($assetId, $bulkGenerationId, $settings, string $apiLang, array $siteIds): ?array
    {
        foreach ($siteIds as $candidateSiteId) {
            $assetCandidate = Asset::find()
                ->id($assetId)
                ->siteId($candidateSiteId)
                ->status(null)
                ->one();

            if (!$assetCandidate) {
                continue;
            }

            $callDetails = $this->prepareApiRequestData($assetCandidate, $bulkGenerationId, $settings, $apiLang);
            if ($callDetails) {
                return $callDetails;
            }
        }

        return null;
    }

    private function prepareApiRequestData($asset, $bulkGenerationId, $settings, ?string $langOverride): ?array
    {
        if ($settings->isPublic) {
            $imageUrl = UrlHelper::siteUrl($asset->url);

            if (!$imageUrl){
                $this->logService->log($asset->id, $bulkGenerationId,"Asset doesn't have URL.", (int)$asset->siteId);
                return null;
            }

            $body = ['imageUrl' => $imageUrl];
        } else {
            $filePath = $this->utilityService->getFilePath($asset);

            if (!file_exists($filePath)) {
                $this->logService->log($asset->id, $bulkGenerationId, "File doesn't exist: " . $filePath, (int)$asset->siteId);
                return null;
            }

            $content = file_get_contents($filePath);
            $base64 = base64_encode($content);
            $body = ['imageRaw' => $base64];
        }

        $lang = $langOverride ?: ($settings->lang ?: 'en');

        return array_merge($body, [
            'source' => 'craftcms',
            'style'  => $settings->modelName,
            'lang'   => $lang,
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