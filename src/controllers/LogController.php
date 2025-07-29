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
        $ogService = new LogService();

        $limit = $request->getQueryParam('limit', $settings->itemPerPage);
        $offset = $request->getQueryParam('offset', 0);
        $logTotalCount = $ogService->getTotalCount();

        $templateParams = [
            'title' => 'Logs',
            'settings' => $settings,
            'logs' => $ogService->getAllLogs(['limit' => $limit, 'offset' => $offset]),
            'totalCount' => $logTotalCount,
            'limit' => $limit,
            'offset' => $offset,
            'currentPage' => (int) floor($offset / $limit) + 1,
            'totalPages' => (int) ceil($logTotalCount / $limit),
        ];

        return $this->renderTemplate('alt-text-lab/log.twig', $templateParams);
    }
}