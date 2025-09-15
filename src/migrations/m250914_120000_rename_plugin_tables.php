<?php
namespace alttextlab\AltTextLab\migrations;

use Craft;
use craft\db\Migration;

class m250914_120000_rename_plugin_tables extends Migration
{
    private array $tables = [
        'alt-text-lab_asset' => 'alttextlab_asset',
        'alt-text-lab_log' => 'alttextlab_log',
        'alt-text-lab_bulk_generation' => 'alttextlab_bulk_generation',
    ];

    public function safeUp(): bool
    {
        foreach ($this->tables as $oldName => $newName) {
            $old = Craft::$app->db->tablePrefix . $oldName;
            $new = Craft::$app->db->tablePrefix . $newName;

            if ($this->db->tableExists($old) && !$this->db->tableExists($new)) {
                $this->renameTable($old, $new);
            }
        }
        return true;
    }

    public function safeDown(): bool
    {
        foreach ($this->tables as $oldName => $newName) {
            $old = Craft::$app->db->tablePrefix . $oldName;
            $new = Craft::$app->db->tablePrefix . $newName;

            if ($this->db->tableExists($new) && !$this->db->tableExists($old)) {
                $this->renameTable($new, $old);
            }
        }
        return true;
    }

}