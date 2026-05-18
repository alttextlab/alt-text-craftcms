<?php

namespace alttextlab\AltTextLab\services;

use Craft;
use craft\elements\Asset;
use alttextlab\AltTextLab\models\Settings;

class CommerceService
{
    private const PRODUCT_CLASS = 'craft\\commerce\\elements\\Product';
    private const VARIANT_CLASS = 'craft\\commerce\\elements\\Variant';

    public function getCommerceData(Asset $asset, Settings $settings): array
    {
        if (!$this->isCommerceAvailable()) {
            return [];
        }

        $elements = $this->getLinkedCommerceElements($asset);

        if (($elements['product'] ?? null) === null) {
            return [];
        }

        $payload = [];

        $nameSource = $settings->commerceNameSource ?? 'product';
        $colorSource = $settings->commerceColorSource ?? 'product';
        $materialSource = $settings->commerceMaterialSource ?? 'product';
        $brandField = trim((string) ($settings->commerceBrandField ?? ''));
        $colorField = trim((string) ($settings->commerceColorField ?? ''));
        $materialField = trim((string) ($settings->commerceMaterialField ?? ''));

        $name = $this->resolveCommerceProductNameForAsset($elements, $nameSource);
        if ($name !== '') {
            $payload['product'] = $name;
        }

        if ($brandField !== '') {
            $brand = $this->getFieldValue($elements['product'], $brandField);
            if ($brand !== '') {
                $payload['brand'] = $brand;
            }
        }

        if ($colorField !== '') {
            $color = $this->resolveProductAttribute($elements, $colorSource, $colorField);
            if ($color !== '') {
                $payload['color'] = $color;
            }
        }

        if ($materialField !== '') {
            $material = $this->resolveProductAttribute($elements, $materialSource, $materialField);
            if ($material !== '') {
                $payload['material'] = $material;
            }
        }

        return $payload;
    }

    private function getLinkedProductIdsToAsset(Asset $asset): array
    {
        if (!$this->isCommerceAvailable()) {
            return [];
        }

        $siteId = (int) $asset->siteId;

        $productIds = self::PRODUCT_CLASS::find()
            ->relatedTo($asset);

        if ($siteId !== null) {
            $productIds->siteId($siteId);
        }

        return array_map('intval', $productIds->ids());
    }

    private function getLinkedProductVariantIdsToAsset(Asset $asset): array
    {
        if (!$this->isCommerceAvailable()) {
            return [];
        }

        $siteId = (int) $asset->siteId;

        $variantIds = self::VARIANT_CLASS::find()
            ->relatedTo($asset);

        if ($siteId !== null) {
            $variantIds->siteId($siteId);
        }

        return array_map('intval', $variantIds->ids());
    }

    private function getLatestProductByIds(array $productIds, ?int $siteId = null): ?object
    {
        if (!$this->isCommerceAvailable() || $productIds === []) {
            return null;
        }

        $query = self::PRODUCT_CLASS::find()
            ->id($productIds)
            ->orderBy(['elements.dateUpdated' => SORT_DESC, 'elements.id' => SORT_DESC]);

        if ($siteId !== null) {
            $query->siteId($siteId);
        }

        return $query->one();
    }

    private function getLatestVariantByIds(array $variantIds, ?int $siteId = null): ?object
    {
        if (!$this->isCommerceAvailable() || $variantIds === []) {
            return null;
        }

        $query = self::VARIANT_CLASS::find()
            ->id($variantIds)
            ->orderBy(['elements.dateUpdated' => SORT_DESC, 'elements.id' => SORT_DESC]);

        if ($siteId !== null) {
            $query->siteId($siteId);
        }

        return $query->one();
    }

    private function getLinkedCommerceElements(Asset $asset): array
    {
        $siteId = (int) $asset->siteId;

        $empty = ['product' => null, 'variant' => null];
        if (!$this->isCommerceAvailable()) {
            return $empty;
        }

        $variantIds = $this->getLinkedProductVariantIdsToAsset($asset);
        $variant = $this->getLatestVariantByIds($variantIds, $siteId);
        if ($variant !== null) {
            $product = $this->getProductForVariant($variant, $siteId);
            return [
                'product' => $product,
                'variant' => $variant,
            ];
        }

        $productIds = $this->getLinkedProductIdsToAsset($asset);
        $product = $this->getLatestProductByIds($productIds, $siteId);
        return [
            'product' => $product,
            'variant' => null,
        ];
    }

    private function getProductForVariant(object $variant, int $siteId): ?object
    {
        $productId = isset($variant->productId) ? (int) $variant->productId : 0;

        if ($productId === 0) {
            return method_exists($variant, 'getProduct') ? $variant->getProduct() : null;
        }

        $query = self::PRODUCT_CLASS::find()->id($productId);

        if ($siteId > 0) {
            $query->siteId($siteId);
        }

        return $query->one();
    }

    private function resolveCommerceProductNameForAsset(array $elements, string $nameSource): string
    {
        $product = $elements['product'] ?? null;
        $variant = $elements['variant'] ?? null;

        if ($product === null) {
            return '';
        }

        if ($nameSource === 'variant' && $variant !== null) {
            return $this->getVariantTitle($variant);
        }

        return $this->getProductTitle($product);
    }

    private function resolveProductAttribute(array $elements, string $source, string $field): string
    {
        if ($field === '') {
            return '';
        }

        $product = $elements['product'] ?? null;
        $variant = $elements['variant'] ?? null;

        if ($source === 'variant') {
            if ($variant === null) {
                return '';
            }
            return $this->getFieldValue($variant, $field);
        }

        if ($product === null) {
            return '';
        }
        return $this->getFieldValue($product, $field);
    }

    private function getFieldValue(?object $element, string $fieldHandle): string
    {
        if ($element === null || $fieldHandle === '' || !method_exists($element, 'getFieldLayout')) {
            return '';
        }

        $fieldLayout = $element->getFieldLayout();
        if ($fieldLayout === null || $fieldLayout->getFieldByHandle($fieldHandle) === null) {
            return '';
        }

        $value = $element->getFieldValue($fieldHandle);
        if ($value === null) {
            return '';
        }

        $stringValue = trim((string)$value);
        return $stringValue !== '' ? $stringValue : '';
    }

    private function getProductTitle(object $product): string
    {
        return isset($product->title)
            ? trim((string)$product->title)
            : '';
    }

    private function getVariantTitle(object $variant): string
    {
        return isset($variant->title)
            ? trim((string) $variant->title)
            : '';
    }
    private function isCommerceAvailable(): bool
    {
        return Craft::$app->plugins->isPluginEnabled('commerce');
    }
}