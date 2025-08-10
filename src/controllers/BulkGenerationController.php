<?php

namespace alttextlab\AltTextLab\controllers;

use alttextlab\AltTextLab\services\ApiService;
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
        $apiService = new ApiService();
        $account = null;

        $apiKey = $settings->apiKey;

        if ($apiKey) {
            $account = $apiService->GetAccount();
        }


        $ids = array();

        $uid = Craft::$app->getRequest()->getQueryParam('uid');
        if ($uid) {
            $ids = Craft::$app->getCache()->get($uid);
        }

        $templateParams = [
            'title' => 'Bulk Generation',
            'isValidAccount' => ($account && $account['isActive']),
            'accountExist' => (bool)($account),
            'credits' => $account ? $account['credits'] : 0,
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