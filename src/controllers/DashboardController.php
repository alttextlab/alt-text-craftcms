<?php

namespace alttextlab\AltTextLab\controllers;

use craft\web\Controller;
use alttextlab\AltTextLab\AltTextLab;
use alttextlab\AltTextLab\services\ApiService;

class DashboardController extends Controller
{

    public function actionIndex()
    {
        $apiService = new ApiService();
        $account = null;

        $settings = AltTextLab::getInstance()->getSettings();
        $apiKey = $settings->apiKey;

        if ($apiKey) {
            $account = $apiService->GetAccount();
        }

        $templateParams = [
            'title' => 'Alt Text Lab',
            'settings' => $settings,
            'apiKey' => $apiKey,
            'account' => $account,
        ];

        return $this->renderTemplate('alt-text-lab/dashboard.twig', $templateParams);
    }
}