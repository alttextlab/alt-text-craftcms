<?php
namespace alttextlab\AltTextLab\migrations;

use Craft;
use craft\db\Migration;

class m260221_120000_add_site_id_to_history extends Migration
{
    public function safeUp(): bool
    {
        $assetTable = '{{%alttextlab_asset}}';
        $logTable = '{{%alttextlab_log}}';

        if (!$this->db->columnExists($assetTable, 'siteId')) {
            $this->addColumn($assetTable, 'siteId', $this->integer()->null());
            $this->createIndex('idx_alttextlab_asset_siteId', $assetTable, ['siteId']);
        }

        if (!$this->db->columnExists($logTable, 'siteId')) {
            $this->addColumn($logTable, 'siteId', $this->integer()->null());
            $this->createIndex('idx_alttextlab_log_siteId', $logTable, ['siteId']);
        }

        $primarySiteId = Craft::$app->getSites()->getPrimarySite()?->id;

        if ($primarySiteId) {
            $primarySiteId = (int)$primarySiteId;

            $this->update($assetTable, ['siteId' => $primarySiteId], ['siteId' => null]);
            $this->update($logTable, ['siteId' => $primarySiteId], ['siteId' => null]);
        }

        return true;
    }

    public function safeDown(): bool
    {
        $assetTable = '{{%alttextlab_asset}}';
        $logTable = '{{%alttextlab_log}}';

        if ($this->db->columnExists($assetTable, 'siteId')) {
            $this->dropIndex('idx_alttextlab_asset_siteId', $assetTable);
            $this->dropColumn($assetTable, 'siteId');
        }

        if ($this->db->columnExists($logTable, 'siteId')) {
            $this->dropIndex('idx_alttextlab_log_siteId', $logTable);
            $this->dropColumn($logTable, 'siteId');
        }

        return true;
    }
}