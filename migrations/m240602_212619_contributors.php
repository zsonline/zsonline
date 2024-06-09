<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\elements\Asset;
use craft\elements\User;
use craft\helpers\StringHelper;
use craft\records\Field;
use yii\base\Exception;

class m240602_212619_contributors extends Migration
{
    public function safeUp(): bool
    {
        foreach(User::find()->all() as $user) {
            if($user->admin || $user->isInGroup('editors') || $user->isInGroup('sales')) {
                continue;
            }

            $user->username = StringHelper::slugify($user->fullName);
            $user->email = str_replace('-', '.', $user->username) . '@zsonline.ch';

            if (!Craft::$app->elements->saveElement($user)) {
                throw new Exception("Couldn't save contributor: " . implode(', ', $user->getFirstErrors()));
            }

            try {
                Craft::$app->users->deactivateUser($user);
            } catch (Exception $e) {
                throw new Exception("Couldn't deactivate contributor: " . implode(', ', $e));
            }
        }

        $field = Field::find()->where(['handle' => 'imageContributions'])->one();

        foreach(Asset::find()->volume('images')->all() as $asset) {
            foreach ($asset->getFieldValue('articleContributions')->all() as $contribution) {
                $contribution->fieldId = $field->id;

                if (!Craft::$app->elements->saveElement($contribution)) {
                    throw new Exception("Couldn't save image contribution: " . implode(', ', $contribution->getFirstErrors()));
                }
            }
        }

        return true;
    }

    public function safeDown(): bool
    {
        echo "m240602_212619_contributors cannot be reverted.\n";
        return false;
    }
}
