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

        $templateParams = [
            'title' => 'Bulk Generation History',
            'settings' => $settings,
            'bulkGenerationItems'=>$bulkGenerationService->getAll([])
        ];

        return $this->renderTemplate('alt-text-lab/bulk-generation-history.twig', $templateParams);
    }

}