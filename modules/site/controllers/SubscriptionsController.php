<?php

namespace site\controllers;

use Craft;
use DateTime;
use craft\elements\Address;
use craft\elements\Entry;
use craft\elements\User;
use craft\records\Field;
use craft\records\EntryType;
use craft\records\Section;
use craft\web\Controller;
use site\models\Subscription;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class SubscriptionsController extends Controller
{
    protected int|bool|array $allowAnonymous = Controller::ALLOW_ANONYMOUS_LIVE;

    /**
     * Create a new subscription.
     *
     * @return null|Response
     * @throws BadRequestHttpException
     */
    public function actionCreate(): ?Response
    {
        $this->requirePostRequest();

        // Populate model
        $subscription = new Subscription();
        $subscription->plan = Craft::$app->getRequest()->getParam('plan');
        $subscription->endDate = (int)Craft::$app->getRequest()->getParam('endDate') ?: null;
        if($subscription->plan == 'regular') {
            // Set endDate to a date 'infinitely' far in the future
            $subscription->endDate = 2122;
        }
        $subscription->email = Craft::$app->getRequest()->getParam('email');
        $subscription->name = Craft::$app->getRequest()->getParam('name');
        $subscription->addressLine1 = Craft::$app->getRequest()->getParam('addressLine1');
        $subscription->addressLine2 = Craft::$app->getRequest()->getParam('addressLine2');
        $subscription->postalCode = (int)Craft::$app->getRequest()->getParam('postalCode') ?: null;
        $subscription->locality = Craft::$app->getRequest()->getParam('locality');

        // Validate form data
        if (!$subscription->validate()) {
            return $this->asModelFailure(
                $subscription,
                '',
                'subscription'
            );
        }

        // Save entry
        $entry = new Entry();
        $entry->sectionId = Section::find()->where(['handle' => 'subscriptions'])->one()->id;
        $entry->typeId = EntryType::find()->where(['handle' => 'subscription'])->one()->id;
        $entry->authorId = User::find()->email('redaktion@zsonline.ch')->one()->id;
        $entry->expiryDate = DateTime::createFromFormat('Y-m-d', ($subscription->endDate + 1) . '-01-01');
        $entry->setFieldValue('subscriptionEmail', $subscription->email);
        $entry->setFieldValue('subscriptionPlan', $subscription->plan);

        if (!Craft::$app->getElements()->saveElement($entry)) {
            return $this->asModelFailure(
                $subscription,
                '',
                'subscription'
            );
        }

        // Save address
        $address = new Address();
        $address->ownerId = $entry->id;
        $address->fieldId = Field::find()->where(['handle' => 'subscriptionAddress'])->one()->id;
        $address->title = 'Lieferadresse';
        $address->fullName = $subscription->name;
        $address->countryCode = 'CH';
        $address->addressLine1 = $subscription->addressLine1;
        $address->addressLine2 = $subscription->addressLine2;
        $address->postalCode = $subscription->postalCode;
        $address->locality = $subscription->locality;

        if (!Craft::$app->getElements()->saveElement($address)) {
            Craft::$app->elements->deleteElement($entry, true);

            return $this->asModelFailure(
                $subscription,
                '',
                'subscription'
            );
        }

        // Resave to populate computed fields
        if (!Craft::$app->getElements()->saveElement($entry)) {
            return $this->asModelFailure(
                $subscription,
                '',
                'subscription'
            );
        }

        return $this->asModelSuccess(
            $subscription,
            '',
            'subscription'
        );
    }
}
