<?php
namespace alttextlab\AltTextLab\migrations;

use Craft;
use craft\db\Migration;

class m250914_120000_rename_plugin_tables extends Migration
{
    public function safeUp(): bool
    {
        $tables = [
            'alt-text-lab_asset' => 'alttextlab_asset',
            'alt-text-lab_log' => 'alttextlab_log',
            'alt-text-lab_bulk_generation' => 'alttextlab_bulk_generation',
        ];

        foreach ($tables as $oldName => $newName) {
            $old = Craft::$app->db->tablePrefix . $oldName;
            $new = Craft::$app->db->tablePrefix . $newName;

            if ($this->db->tableExists($old)) {
                if (!$this->db->tableExists($new)) {
                    $this->renameTable($old, $new);
                }
            }
        }

        return true;
    }

    public function safeDown(): bool
    {
        return false;
    }

}