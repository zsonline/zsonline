<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\elements\Entry;
use yii\base\Exception;

class m240616_180821_issues extends Migration
{
    public function safeUp(): bool
    {
        foreach(Entry::find()->type('issueSection')->all() as $entry) {
            $entry->title = $entry->getFieldValue('issueSectionTitle') ?: 'Alle';
            if (!Craft::$app->elements->saveElement($entry)) {
                throw new Exception("Couldn't save issue section: " . implode(', ', $entry->getFirstErrors()));
            }
        }

        return true;
    }

    public function safeDown(): bool
    {
        echo "m240616_180821_issues cannot be reverted.\n";
        return true;
    }
}
