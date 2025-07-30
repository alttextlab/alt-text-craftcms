<?php

namespace alttextlab\AltTextLab;

use Craft;
use craft\base\Element;
use craft\base\Model;
use craft\base\Plugin;
use craft\elements\Asset;
use craft\events\ModelEvent;
use craft\events\RegisterElementActionsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\Queue;
use craft\web\UrlManager;
use alttextlab\AltTextLab\actions\BulkGeneration;
use alttextlab\AltTextLab\jobs\GenerateAltTextJob;
use alttextlab\AltTextLab\models\Settings;
use alttextlab\AltTextLab\services\ApiService;
use yii\base\Event;

class AltTextLab extends Plugin
{
    public static AltTextLab $plugin;
    public bool $hasCpSection = true;
    public bool $hasCpSettings = true;

    public function init(): void
    {
        parent::init();
        self::$plugin = $this;
        Craft::info('AltTextLab plugin loaded', __METHOD__);
        Craft::$app->onInit(function () {
            $this->attachEventHandlers();
        });
    }

    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }

    protected function settingsHtml(): ?string
    {
        $aiModels = require \Craft::$app->getPlugins()->getPlugin('alt-text-lab')->getBasePath() . '/configs/AiModels.php';
        $languages = require \Craft::$app->getPlugins()->getPlugin('alt-text-lab')->getBasePath() . '/configs/Languages.php';

        $apiService = new ApiService();
        $account = $apiService->GetAccount();

        return Craft::$app->view->renderTemplate('alt-text-lab/settings.twig', [
            'plugin' => $this,
            'settings' => $this->getSettings(),
            'modelOptions' => $aiModels,
            'languages' => $languages,
            'account' => $account,
        ]);
    }

    private function attachEventHandlers(): void
    {
        $settings = $this->getSettings();

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function (RegisterUrlRulesEvent $event) {
            $event->rules['alt-text-lab/history'] = 'alt-text-lab/history';
            $event->rules['alt-text-lab/bulk-generation-history'] = 'alt-text-lab/bulk-generation-history';
            $event->rules['alt-text-lab/bulk-generation'] = 'alt-text-lab/bulk-generation';
            $event->rules['alt-text-lab/bulk-generation-queue'] = 'alt-text-lab/bulk-generation-queue';
            $event->rules['alt-text-lab/utility'] = 'alt-text-lab/utility';
            $event->rules['alt-text-lab/log'] = 'alt-text-lab/log';
            $event->rules['alt-text-lab'] = 'alt-text-lab/dashboard';
            $event->rules['alt-text-lab/utility/change-asset-alt-text'] = 'alt-text-lab/utility/change-asset-alt-text';
        });

        if ($settings->onUploadGenerate) {
            Event::on(
                Asset::class,
                Asset::EVENT_AFTER_SAVE,
                function (ModelEvent $event) {
                    if ($event->sender->firstSave && $event->sender->alt == "") {
                        $asset = $event->sender;

                        Queue::push(new GenerateAltTextJob([
                            'assetId' => $asset->id
                        ]));
                    }
                }
            );
        }

        Event::on(
            Asset::class,
            Element::EVENT_REGISTER_ACTIONS,
            function (RegisterElementActionsEvent $event) {
                $event->actions[] = BulkGeneration::class;
            }
        );
    }

    public function getCpNavItem(): ?array
    {
        $item = parent::getCpNavItem();

        $item['subnav']['settings'] = ['label' => 'Settings', 'url' => 'settings/plugins/alt-text-lab'];
        $item['subnav']['bulk-generation'] = ['label' => 'Bulk generator', 'url' => 'alt-text-lab/bulk-generation'];
        $item['subnav']['bulk-generation-history'] = ['label' => 'Bulk History', 'url' => 'alt-text-lab/bulk-generation-history'];
        $item['subnav']['history'] = ['label' => 'History', 'url' => 'alt-text-lab/history'];
        $item['subnav']['log'] = ['label' => 'Logs', 'url' => 'alt-text-lab/log'];

        return $item;
    }

}