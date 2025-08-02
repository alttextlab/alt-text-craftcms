<?php

namespace alttextlab\AltTextLab\controllers;

use Craft;
use craft\web\Controller;
use alttextlab\AltTextLab\AltTextLab;
use alttextlab\AltTextLab\services\LogService;

class LogController extends Controller
{

    public function actionIndex()
    {
        $settings = AltTextLab::getInstance()->getSettings();
        $request = Craft::$app->getRequest();
        $logService = new LogService();

        $bulkId = Craft::$app->getRequest()->getQueryParam('bulkId');

        $conditions = array();

        if (!empty($bulkId)) {
            $conditions['bulkGenerationId'] = $bulkId;
        }

        $limit = $request->getQueryParam('limit', $settings->itemPerPage);
        $offset = $request->getQueryParam('offset', 0);

        $logTotalCount = $logService->getTotalCount($conditions);
        $logs = $logService->getAllLogs(['limit' => $limit, 'offset' => $offset, 'bulkGenerationId' => $bulkId]);

        $templateParams = [
            'title' => 'Logs',
            'settings' => $settings,
            'logs' => $logs,
            'totalCount' => $logTotalCount,
            'limit' => $limit,
            'offset' => $offset,
            'currentPage' => (int) floor($offset / $limit) + 1,
            'totalPages' => (int) ceil($logTotalCount / $limit),
            'bulkId' => $bulkId
        ];

        return $this->renderTemplate('alt-text-lab/log.twig', $templateParams);
    }
}