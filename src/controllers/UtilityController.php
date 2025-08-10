<?php

namespace alttextlab\AltTextLab\controllers;

use Craft;
use craft\helpers\Queue;
use craft\web\Controller;
use Exception;
use alttextlab\AltTextLab\jobs\RefreshAltTextJob;

class UtilityController extends Controller
{
    public function actionIndex()
    {
        $request = Craft::$app->getRequest();
        $data = $request->getBodyParams();

        $uid = $data['uid'] ?? null;
        $ids = $data['ids'] ?? [];

        if ($uid && is_array($ids)) {
            Craft::$app->getCache()->set($uid, $ids, 3600);
        }

        return $this->asJson(['success' => true]);
    }

    public function actionChangeAssetAltText()
    {
        $assetId = null;
        try {
            $this->requirePostRequest();
            $request = Craft::$app->getRequest();

            $assetId = $request->getBodyParam('assetId');

            Queue::push(new RefreshAltTextJob([
                'assetId' => $assetId
            ]));

            return $this->asJson([
                'success' => true,
                'assetId' => $assetId
            ]);
        } catch (Exception $exception) {
            Craft::info($exception->getMessage(), 'alt-text-lab');
            return $this->asJson([
                'success' => false,
                'message' => $exception->getMessage(),
                'assetId' => $assetId
            ]);
        }
    }

}