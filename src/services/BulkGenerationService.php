<?php

namespace alttextlab\AltTextLab\services;

use alttextlab\AltTextLab\models\AltTextLabBulkGeneration as AltTextLabBulkGenerationModel;
use alttextlab\AltTextLab\records\AltTextLabBulkGeneration as AltTextLabBulkGenerationRecord;

class BulkGenerationService
{

    public function saveAsset(AltTextLabBulkGenerationModel $model): AltTextLabBulkGenerationModel
    {
        $record = new AltTextLabBulkGenerationRecord();

        $fieldsToUpdate = [
            'countOfImages',
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

    public function getAll($filters = [])
    {
        $recordsQuery = AltTextLabBulkGenerationRecord::find();

        if (array_key_exists('limit', $filters)) {
            $recordsQuery->limit($filters['limit']);
        }
        if (array_key_exists('offset', $filters)) {
            $recordsQuery->offset($filters['offset']);
        }

        $records = $recordsQuery->all();

        $models = array();

        foreach ($records as $record) {
            $model = new AltTextLabBulkGenerationModel($record->getAttributes());
            $models[] = $model;
        }

        return $models;
    }

}