<?php
namespace alttextlab\AltTextLab\migrations;

use Craft;
use craft\db\Migration;

/**
 * Safety-net migration for installs where `siteId` was never added to the plugin
 * tables.
 *
 * On a fresh plugin install Craft runs only Install.php and marks every other
 * migration (including m260221_120000_add_site_id_to_history) as applied without
 * running it. Installs created before Install.php carried the `siteId` columns
 * therefore have the m260221 migration recorded as applied but no `siteId`
 * column, so bumping schemaVersion cannot re-run it. This migration is not
 * recorded anywhere yet, so it runs on update and back-fills the columns.
 *
 * Fully idempotent: does nothing when the columns already exist.
 */
class m260909_120000_ensure_site_id_columns extends Migration
{
    private const TABLES = [
        '{{%alttextlab_asset}}' => 'idx_alttextlab_asset_siteId',
        '{{%alttextlab_log}}' => 'idx_alttextlab_log_siteId',
    ];

    public function safeUp(): bool
    {
        foreach (self::TABLES as $table => $indexName) {
            if (!$this->db->columnExists($table, 'siteId')) {
                $this->addColumn($table, 'siteId', $this->integer()->null());
                $this->createIndex($indexName, $table, ['siteId']);
            }
        }

        $primarySiteId = Craft::$app->getSites()->getPrimarySite()?->id;

        if ($primarySiteId) {
            $primarySiteId = (int)$primarySiteId;

            foreach (array_keys(self::TABLES) as $table) {
                $this->update($table, ['siteId' => $primarySiteId], ['siteId' => null]);
            }
        }

        return true;
    }

    public function safeDown(): bool
    {
        // The lifecycle of the `siteId` columns is owned by
        // m260221_120000_add_site_id_to_history; nothing to reverse here.
        return true;
    }
}
