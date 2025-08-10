<?php

namespace alttextlab\AltTextLab\controllers;

use alttextlab\AltTextLab\services\BulkGenerationService;
use Craft;
use craft\helpers\Queue;
use alttextlab\AltTextLab\AltTextLab;
use craft\web\Controller;

class BulkGenerationHistoryController extends Controller
{

    public function actionIndex()
    {
        $settings = AltTextLab::getInstance()->getSettings();
        $bulkGenerationService = new BulkGenerationService();
        $request = Craft::$app->getRequest();

        $limit = $request->getQueryParam('limit', $settings->itemPerPage);
        $offset = $request->getQueryParam('offset', 0);

        $logTotalCount = $bulkGenerationService->getTotalCount();
        $bulkGenerations = $bulkGenerationService->getAll(['limit' => $limit, 'offset' => $offset]);

        $templateParams = [
            'title' => 'Bulk Generation History',
            'settings' => $settings,
            'bulkGenerationItems'=> $bulkGenerations,
            'totalCount' => $logTotalCount,
            'limit' => $limit,
            'offset' => $offset,
            'currentPage' => (int) floor($offset / $limit) + 1,
            'totalPages' => (int) ceil($logTotalCount / $limit),
        ];

        return $this->renderTemplate('alt-text-lab/bulk-generation-history.twig', $templateParams);
    }

    public function actionGetCurrentGenerationData(){
        $bulkGenerationService = new BulkGenerationService();
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $ids = $request->getBodyParam('ids');

        $bulkGenerations = $bulkGenerationService->getAll([], $ids);

        return $this->asJson([
            'success' => true,
            'data' => $bulkGenerations,
        ]);
    }

}