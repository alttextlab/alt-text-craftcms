<?php

namespace alttextlab\AltTextLab\jobs;

use craft\queue\BaseJob;
use alttextlab\AltTextLab\services\AltTextLabAssetsService;

class RefreshAltTextJob extends BaseJob
{

    public int $assetId;

    public function execute($queue): void
    {
       $service = new AltTextLabAssetsService();
       $service->changeCraftAssetAltByAssetId($this->assetId);
    }

    protected function defaultDescription(): string
    {
        return 'Refreshing alt text by text from history.';
    }

}