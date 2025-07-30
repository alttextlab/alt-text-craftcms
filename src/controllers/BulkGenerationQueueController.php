<?php

namespace alttextlab\AltTextLab\controllers;

use alttextlab\AltTextLab\models\AltTextLabBulkGeneration;
use alttextlab\AltTextLab\services\BulkGenerationService;
use Craft;
use craft\helpers\Queue;
use alttextlab\AltTextLab\AltTextLab;
use craft\web\Controller;
use alttextlab\AltTextLab\jobs\GenerateAltTextJob;
use alttextlab\AltTextLab\services\CraftAssetsService;

class BulkGenerationQueueController extends Controller
{

    public function actionIndex()
    {
        $craftAssetsService = new CraftAssetsService();
        $bulkGenerationService = new BulkGenerationService();
        $uid = Craft::$app->getRequest()->getQueryParam('uid');
        $ids = array();
        $settings = AltTextLab::getInstance()->getSettings();

        $overwriteAltText = (bool)Craft::$app->getRequest()->getBodyParam('OverwriteAltText');

        if ($uid) {
            $ids = Craft::$app->getCache()->get($uid);
        }

        $allAssets = $craftAssetsService->getAssetsByAltTextFilter($overwriteAltText, $ids);

        $bulkGenerationModel = new AltTextLabBulkGeneration();
        $bulkGenerationModel->countOfImages = count($allAssets);

        $bulkGenerationModel = $bulkGenerationService->saveAsset($bulkGenerationModel);

        foreach ($allAssets as $asset) {
            Queue::push(new GenerateAltTextJob([
                'assetId' => $asset->id,
                'bulkGenerationId' => $bulkGenerationModel->id
            ]));
        }

        $templateParams = [
            'title' => 'Alt Text Lab',
            'settings' => $settings,
            'uid' => $uid
        ];

        return $this->renderTemplate('alt-text-lab/bulk-generation-queue.twig', $templateParams);
    }

}