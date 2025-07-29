<?php
namespace alttextlab\AltTextLab\services;

use Craft;

class UtilityService
{

    private $logService;

    public function __construct()
    {
        $this->logService = new LogService();
    }

    public function checkAssetIsValid($asset): bool
    {

        // Check asset kind
        if ($asset->kind !== 'image') {
            $this->logMessage('Not an image.', $asset->id);
            return false;
        }

        // Check allowed file extensions
        $validExtensions = ['jpg', 'jpeg', 'png', 'avif', 'webp', 'svg'];
        if (!in_array(strtolower($asset->extension), $validExtensions, true)) {
            $this->logMessage("Unsupported image type: {$asset->extension}", $asset->id);
            return false;
        }

        // Check file size (max 16MB)
        $maxSizeBytes = 16 * 1024 * 1024;
        if ($asset->size > $maxSizeBytes) {
            $this->logMessage('Image file size exceeds 16MB.', $asset->id);
            return false;
        }

        // Check minimum image dimensions (51x51)
        if ($asset->width < 51 || $asset->height < 51) {
            $this->logMessage("Image too small ({$asset->width}x{$asset->height}). Minimum size is 51x51.", $asset->id);
            return false;
        }

        return true;
    }

    public function logMessage(string $message, $assetId): void
    {
        $fullMessage = "Asset ID {$assetId}: $message";
        Craft::info($fullMessage, 'alt-text-lab');
        $this->logService->log($assetId, $fullMessage);
    }

}