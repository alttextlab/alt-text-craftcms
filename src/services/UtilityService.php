<?php
namespace alttextlab\AltTextLab\services;

use Craft;
use craft\helpers\App;

class UtilityService
{

    private $logService;
    private const ENV_EXCLUDE_REGEX = '$ALT_TEXT_LAB_EXCLUDE_REGEX';

    public function __construct()
    {
        $this->logService = new LogService();
    }

    public function checkAssetIsValid($asset, $bulkGenerationId): bool
    {

        // Check asset kind
        if ($asset->kind !== 'image') {
            $this->logMessage('Not an image.', $bulkGenerationId, $asset->id);
            return false;
        }

        // Check allowed file extensions
        $validExtensions = ['jpg', 'jpeg', 'png', 'avif', 'webp', 'svg'];
        if (!in_array(strtolower($asset->extension), $validExtensions, true)) {
            $this->logMessage("Unsupported image type: {$asset->extension}",$bulkGenerationId, $asset->id);
            return false;
        }

        // Check file size (max 16MB)
        $maxSizeBytes = 16 * 1024 * 1024;
        if ($asset->size > $maxSizeBytes) {
            $this->logMessage('Image file size exceeds 16MB.',$bulkGenerationId, $asset->id);
            return false;
        }

        // Check minimum image dimensions (51x51)
        if ($asset->width < 51 || $asset->height < 51) {
            $this->logMessage("Image too small ({$asset->width}x{$asset->height}). Minimum size is 51x51.",$bulkGenerationId, $asset->id);
            return false;
        }

        return true;
    }

    public function isPathExcludedByRegex($asset, $pattern): bool
    {
        if (!$asset) {
            return false;
        }

        if (!$pattern || trim($pattern) === '') {
            return false;
        }

        $path = $this->getFilePath($asset);

        if (!$path) {
            return false;
        }

        $pattern = trim($pattern);

        $result = @preg_match($pattern, $path);

        if ($result === false) {
            $result = @preg_match('~' . $pattern . '~', $path);
        }

        return $result === 1;
    }

    public function getFilePath($asset)
    {
        $fsPath = Craft::getAlias($asset->getVolume()->fs->path ?? '');
        $subpath = Craft::parseEnv($asset->getVolume()->subpath ?? '');

        $fsPath = rtrim($fsPath, DIRECTORY_SEPARATOR);
        $subpath = trim($subpath, DIRECTORY_SEPARATOR);

        $rootPath = $subpath ? ($fsPath . DIRECTORY_SEPARATOR . $subpath) : $fsPath;

        $filePath = rtrim($rootPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($asset->getPath(), DIRECTORY_SEPARATOR);
        return $filePath;
    }

    public function getExcludeRegexEnv()
    {
        return App::parseEnv(self::ENV_EXCLUDE_REGEX);
    }

    public function logMessage(string $message, $bulkGenerationId, $assetId): void
    {
        $fullMessage = "Asset ID {$assetId}: $message";
        Craft::info($fullMessage, 'alt-text-lab');
        $this->logService->log($assetId, $bulkGenerationId, $fullMessage);
    }

}