<?php

namespace alttextlab\AltTextLab\actions;

use Craft;
use craft\base\ElementAction;

class BulkGeneration extends ElementAction
{
    public static function displayName(): string
    {
        return Craft::t('alt-text-lab', 'Bulk generation');
    }

    public function getTriggerHtml(): ?string
    {
        Craft::$app->getView()->registerJsWithVars(fn($type) => <<<JS
        (() => {
            new Craft.ElementActionTrigger({
                type: $type,
                bulk: true,
                validateSelection: false,
                activate: function(selectedItems) {
                    const elements = selectedItems.toArray ? selectedItems.toArray() : Array.from(selectedItems);
                    const ids = elements.map(el => el.dataset.id);
                    const uid = crypto.randomUUID();
                
                    fetch(Craft.getCpUrl('alt-text-lab/utility'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': Craft.csrfTokenValue
                    },
                    body: JSON.stringify({ uid, ids })
            }).then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            }).then(data => {
                if (data.success) {
                    window.location.href = Craft.getCpUrl('alt-text-lab/bulk-generation?uid=' + uid);
                } else {
                    console.error('Something went wrong:', data);
                }
            }).catch(error => {
                console.error('Fetch error:', error);
                    });
                }
            });
        })();
    JS, [static::class]);
        return '';
    }
}
