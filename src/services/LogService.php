<?php

namespace alttextlab\AltTextLab\services;

use alttextlab\AltTextLab\models\AltTextLabLog as AltTextLabLogModel;
use alttextlab\AltTextLab\records\AltTextLabLog as AltTextLabLogRecord;

class LogService
{
    public function log($assetId, $text): AltTextLabLogModel
    {

        $model = new AltTextLabLogModel();
        $model->assetId = $assetId;
        $model->logMessage = $text;

        $record = new AltTextLabLogRecord();

        $fieldsToUpdate = [
            'assetId',
            'logMessage',
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

        return  $model;
    }

    public function getAllLogs($filters = []): array
    {
        $recordsQuery = AltTextLabLogRecord::find();

        if (array_key_exists('limit', $filters)) {
            $recordsQuery->limit($filters['limit']);
        }
        if (array_key_exists('offset', $filters)) {
            $recordsQuery->offset($filters['offset']);
        }

        $records = $recordsQuery->all();

        $models = array();

        foreach ($records as $record) {
            $model = new AltTextLabLogModel($record->getAttributes());
            $models[] = $model;
        }

        return $models;
    }

    public function getTotalCount(): bool|int|string|null
    {
        $recordsQuery = AltTextLabLogRecord::find();
        return $recordsQuery->count();
    }

}