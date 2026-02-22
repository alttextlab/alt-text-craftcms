<?php

namespace alttextlab\AltTextLab\services;

use alttextlab\AltTextLab\models\AltTextLabLog as AltTextLabLogModel;
use alttextlab\AltTextLab\records\AltTextLabLog as AltTextLabLogRecord;
use Craft;
use craft\elements\Asset;
use craft\db\Query;

class LogService
{
    public function log($assetId, $bulkGenerationId, $text, ?int $siteId = null): AltTextLabLogModel
    {
        $model = new AltTextLabLogModel();
        $model->assetId = $assetId;
        $model->logMessage = $text;
        $model->bulkGenerationId = $bulkGenerationId;
        $model->siteId = $siteId;

        $record = new AltTextLabLogRecord();

        $fieldsToUpdate = [
            'assetId',
            'logMessage',
            'bulkGenerationId',
            'siteId',
        ];

        foreach ($fieldsToUpdate as $handle) {
            if (property_exists($model, $handle)) {
                $record->$handle = $model->$handle;
            }
        }

        $record->validate();
        $model->addErrors($record->getErrors());

        $record->save(false);

        $model->id = $record->id;

        return $model;
    }

    public function getAllLogs($filters = [], &$thereIsWithoutURL): array
    {
        $recordsQuery = AltTextLabLogRecord::find();

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
            $model = new AltTextLabLogModel($record->getAttributes());

            $asset = Asset::find()->id($model->assetId)->one();
            if (!$asset || !$asset->getUrl()) {
                $thereIsWithoutURL = true;
            }

            $models[] = $model;
        }

        return $models;
    }

    public function getTotalCount(array $conditions = []):int
    {
        $recordsQuery = AltTextLabLogRecord::find();

        if (!empty($conditions)) {
            $recordsQuery->where($conditions);
        }

        return $recordsQuery->count();
    }

    public function getDistinctAssetCount(array $conditions = []): int
    {
        $query = (new Query())
            ->from(AltTextLabLogRecord::tableName())
            ->select(['assetId'])
            ->distinct(true);

        if (!empty($conditions)) {
            $query->where($conditions);
        }

        return (int)$query->count('assetId', Craft::$app->db);
    }

}