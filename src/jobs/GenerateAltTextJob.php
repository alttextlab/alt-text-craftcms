<?php

namespace alttextlab\AltTextLab\jobs;

use craft\queue\BaseJob;
use alttextlab\AltTextLab\services\AltTextLabAssetsService;

class GenerateAltTextJob extends BaseJob
{
    public int $assetId;

    public function execute($queue): void
    {
        $service = new AltTextLabAssetsService();
        $service->generateAltText($this->assetId);
    }

    protected function defaultDescription(): string
    {
        return 'Generate alt text for asset.';
    }
}