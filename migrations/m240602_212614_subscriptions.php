<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\elements\Address;
use craft\elements\Entry;
use craft\elements\User;
use craft\records\Field;
use craft\records\EntryType;
use craft\records\Section;
use yii\base\Exception;

class m240602_212614_subscriptions extends Migration
{
    public function safeUp(): bool
    {
        $section = Section::find()->where(['handle' => 'subscriptions'])->one();
        $entryType = EntryType::find()->where(['handle' => 'subscription'])->one();
        $field = Field::find()->where(['handle' => 'subscriptionAddress'])->one();

        $defaultUser = User::find()->email('redaktion@zsonline.ch')->one();
        if(!$defaultUser) {
            return false;
        }

        foreach(User::find()->group('subscriber')->all() as $user) {
            $existingAddress = $user->addresses->one();

            $entry = new Entry();
            $entry->sectionId = $section->id;
            $entry->typeId = $entryType->id;
            $entry->authorId = $defaultUser->id;

            $entry->postDate = $user->dateCreated;
            $entry->expiryDate = $user->getFieldValue('subscriptionEndDate');
            $entry->setFieldValue('subscriptionEmail', $user->email);
            $entry->setFieldValue('subscriptionPlan', $user->getFieldValue('subscriptionPlan'));

            if (!Craft::$app->elements->saveElement($entry)) {
                throw new Exception("Couldn't save subscriber: " . implode(', ', $entry->getFirstErrors()));
            }

            $address = new Address();
            $address->setOwner($entry);
            $address->fieldId = $field->id;
            $address->title = 'Lieferadresse';
            $address->fullName = $user->fullName;
            $address->countryCode = $existingAddress->countryCode;
            $address->addressLine1 = $existingAddress->addressLine1;
            $address->postalCode = $existingAddress->postalCode;
            $address->locality = $existingAddress->locality;

            if (!Craft::$app->elements->saveElement($address)) {
                throw new Exception("Couldn't save address: " . implode(', ', $address->getFirstErrors()));
            }
            if (!Craft::$app->elements->saveElement($entry)) {
                throw new Exception("Couldn't resave subscriber: " . implode(', ', $entry->getFirstErrors()));
            }

            $contributions = Entry::find()->type('contribution')->relatedTo($user)->all();
            $group = Craft::$app->userGroups->getGroupByHandle('contributors');

            if (count($contributions)) {
                foreach ($user->addresses->all() as $address) {
                    if (!Craft::$app->elements->deleteElement($address, true)) {
                        throw new Exception("Couldn't delete address: " . implode(', ', $address->getFirstErrors()));
                    }
                }

                if (!Craft::$app->users->assignUserToGroups($user->id, [$group->id])) {
                    throw new Exception("Couldn't assign user to group: " . implode(', ', $user->getFirstErrors()));
                }

                $user->setFieldValue('subscriptionPlan', null);
                $user->setFieldValue('subscriptionEndDate', null);

                if (!Craft::$app->elements->saveElement($user)) {
                    throw new Exception("Couldn't save user: " . implode(', ', $user->getFirstErrors()));
                }
            } else {
                if (!Craft::$app->elements->deleteElement($user, true)) {
                    throw new Exception("Couldn't delete user: " . implode(', ', $user->getFirstErrors()));
                }
            }
        }

        return true;
    }

    public function safeDown(): bool
    {
        echo "m240602_212614_subscriptions cannot be reverted.\n";
        return false;
    }
}
