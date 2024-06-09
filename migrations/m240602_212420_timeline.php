<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\elements\Entry;
use yii\base\Exception;

class m240602_212420_timeline extends Migration
{
    public function safeUp(): bool
    {
        foreach(Entry::find()->type('timelineEvent')->all() as $entry) {
            $entry->title = $entry->getFieldValue('timelineEventTitle');
            if (!Craft::$app->elements->saveElement($entry)) {
                throw new Exception("Couldn't save timeline event: " . implode(', ', $entry->getFirstErrors()));
            }
        }

        foreach(Entry::find()->type('timelineEventArticle')->all() as $entry) {
            $entry->title = $entry->getFieldValue('timelineEventArticleTitle');
            $entry->setFieldValue(
                'timelineEventArticleLead',
                trim(strip_tags($entry->getFieldValue('timelineEventArticleDescription')))
            );
            if (!Craft::$app->elements->saveElement($entry)) {
                throw new Exception("Couldn't save timeline event article: " . implode(', ', $entry->getFirstErrors()));
            }
        }

        return true;
    }

    public function safeDown(): bool
    {
        echo "m240602_212420_timeline cannot be reverted.\n";
        return false;
    }
}
