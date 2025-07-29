<?php

namespace alttextlab\AltTextLab\controllers;

use Craft;
use craft\web\Controller;
use alttextlab\AltTextLab\AltTextLab;
use alttextlab\AltTextLab\services\CraftAssetsService;

class BulkGenerationController extends Controller
{

    public function actionIndex()
    {
        $settings = AltTextLab::getInstance()->getSettings();
        $craftAssetsService = new CraftAssetsService();

        $ids = array();

        $uid = Craft::$app->getRequest()->getQueryParam('uid');
        if ($uid) {
            $ids = Craft::$app->getCache()->get($uid);
        }

        $templateParams = [
            'title' => 'Bulk Generation',
            'settings' => $settings,
            'ids' => count($ids),
            'uid' => $uid,
            'assetsWithoutAlt' => $craftAssetsService->getCountAssetsByAltTextFilter(false, $ids),
            'assetsWithAlt' => $craftAssetsService->getCountAssetsByAltTextFilter(true, $ids),
            'totalCountOfImages' => $craftAssetsService->getCountAssets($ids)
        ];

        return $this->renderTemplate('alt-text-lab/bulk-generation.twig', $templateParams);
    }

}