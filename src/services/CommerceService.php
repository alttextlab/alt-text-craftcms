<?php

namespace alttextlab\AltTextLab\services;

use Craft;
use craft\elements\Asset;

class CommerceService
{
    private const PRODUCT_CLASS = 'craft\\commerce\\elements\\Product';
    private const VARIANT_CLASS = 'craft\\commerce\\elements\\Variant';

    public function getLinkedProductIdsToAsset(Asset $asset): array
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

    public function getLinkedProductVariantIdsToAsset(Asset $asset): array
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

    public function getLatestProductByIds(array $productIds, ?int $siteId = null): ?object
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

    public function getLatestVariantByIds(array $variantIds, ?int $siteId = null): ?object
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

    public function getLinkedVariantIdsForAssetAndProduct(Asset $asset, int $productId): array
    {
        if (!$this->isCommerceAvailable() || $productId <= 0) {
            return [];
        }

        $siteId = (int) $asset->siteId;
        $query = self::VARIANT_CLASS::find()
            ->relatedTo($asset)
            ->productId($productId);

        if ($siteId > 0) {
            $query->siteId($siteId);
        }

        return array_map('intval', $query->ids());
    }

    public function getLinkedCommerceElements(Asset $asset): array
    {
        $siteId = (int)$asset->siteId;

        $variantIds = $this->getLinkedProductVariantIdsToAsset($asset);
        $productIds = $this->getLinkedProductIdsToAsset($asset);

        $product = $this->getLatestProductByIds($productIds, $siteId);
        $commonVariant = $this->getLatestVariantByIds($variantIds, $siteId);

        $productVariant = null;

        if ($product !== null) {
            $variantIdsInProduct = $this->getLinkedVariantIdsForAssetAndProduct($asset, $product->id);
            $productVariant = $this->getLatestVariantByIds($variantIdsInProduct, $siteId);
        }

        return [
            'product' => $product,
            'commonVariant' => $commonVariant,
            'productVariant' => $productVariant
        ];
    }

    public function resolveCommerceProductNameForAsset($elements, string $nameSource): string
    {
        $product = $elements['product'];
        $commonVariant = $elements['commonVariant'];
        $productVariant = $elements['productVariant'];

        if ($product !== null) {
            if ($productVariant !== null) {
                return $this->getVariantTitle($productVariant);
            }
            return $this->getProductTitle($product);
        }

        if ($commonVariant !== null) {
            if ($nameSource === 'product') {
                $parentProduct = $commonVariant->getProduct();
                return $parentProduct !== null ? $this->getProductTitle($parentProduct) : '';
            }
            return $this->getVariantTitle($commonVariant);
        }

        return '';
    }

    public function resolveCommerceBrandNameForAsset($elements, string $brandField): string
    {
        $product = $elements['product'] ?: ($elements['commonVariant'] ? $elements['commonVariant']->getProduct() : null);

        return $this->getFieldValue($product, $brandField);
    }

    public function resolveCommerceProductColorForAsset($elements, string $colorSource, string $colorField): string
    {
        return $this->resolveProductAttribute($elements, $colorSource, $colorField);
    }

    public function resolveCommerceProductMaterialForAsset($elements, string $materialSource, string $materialField): string
    {
        return $this->resolveProductAttribute($elements, $materialSource, $materialField);
    }

    private function resolveProductAttribute($elements, string $source, string $field): string
    {
        $product = $elements['product'];
        $commonVariant = $elements['commonVariant'];
        $productVariant = $elements['productVariant'];

        if ($product !== null) {
            if ($productVariant !== null && $source === 'variant') {
                return $this->getFieldValue($productVariant, $field);
            }
            if ($source === 'product') {
                return $this->getFieldValue($product, $field);
            }
        }

        if ($commonVariant !== null) {
            if ($source === 'product') {
                return $this->getFieldValue($commonVariant->getProduct(), $field);
            }
            return $this->getFieldValue($commonVariant, $field);
        }

        return '';
    }

    public function getFieldValue(?object $element, string $fieldHandle): string
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

    public function getProductTitle(object $product): string
    {
        return isset($product->title)
            ? trim((string)$product->title)
            : '';
    }

    public function getVariantTitle(object $variant): string
    {
        return isset($variant->title)
            ? trim((string) $variant->title)
            : '';
    }
    public function isCommerceAvailable(): bool
    {
        return Craft::$app->plugins->isPluginEnabled('commerce');
    }
}