<?php
namespace alttextlab\AltTextLab\controllers;

use Craft;
use craft\web\Controller;
use alttextlab\alttextlab\AltTextLab;
use alttextlab\AltTextLab\services\AltTextLabAssetsService;

class HistoryController extends Controller
{

    public function actionIndex()
    {
        $assetsService = new AltTextLabAssetsService();
        $settings = AltTextLab::getInstance()->getSettings();
        $request = Craft::$app->getRequest();

        $bulkId = Craft::$app->getRequest()->getQueryParam('bulkId');

        $conditions = array();

        if (!empty($bulkId)) {
            $conditions['bulkGenerationId'] = $bulkId;
        }

        $limit = $request->getQueryParam('limit', $settings->itemPerPage);
        $offset = $request->getQueryParam('offset', 0);

        $assetsTotalCount = $assetsService->getTotalCount($conditions);
        $assets = $assetsService->getAllAssets(['limit' => $limit, 'offset' => $offset, 'bulkGenerationId' => $bulkId]);

        $templateParams = [
            'title' => 'History',
            'settings' => $settings,
            'assets' => $assets,
            'totalCount' => $assetsTotalCount,
            'limit' => $limit,
            'offset' => $offset,
            'currentPage' => (int) floor($offset / $limit) + 1,
            'totalPages' => (int) ceil($assetsTotalCount / $limit),
            'bulkId' => $bulkId,
        ];

        return $this->renderTemplate('alt-text-lab/history.twig', $templateParams);
    }
}
