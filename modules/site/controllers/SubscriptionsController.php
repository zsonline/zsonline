<?php

namespace site\controllers;

use Craft;
use craft\elements\Address;
use craft\elements\User;
use craft\web\Controller;
use site\models\Subscription;
use Throwable;
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
        $subscription->endDate = Craft::$app->getRequest()->getParam('endDate');
        if($subscription->plan == 'regular') {
            // Set endDate to a date 'infinitely' far in the future
            $subscription->endDate = date('Y-m-d', strtotime('2123-01-01'));
        }
        $subscription->email = Craft::$app->getRequest()->getParam('email');
        $subscription->name = Craft::$app->getRequest()->getParam('name');
        $subscription->addressLine1 = Craft::$app->getRequest()->getParam('addressLine1');
        $subscription->addressLine2 = Craft::$app->getRequest()->getParam('addressLine2');
        $subscription->postalCode = Craft::$app->getRequest()->getParam('postalCode');
        $subscription->locality = Craft::$app->getRequest()->getParam('locality');

        // Validate form data
        if (!$subscription->validate()) {
            return $this->asModelFailure(
                $subscription,
                '',
                'subscription'
            );
        }

        // Store user
        $user = new User();
        $user->fullName = $subscription->name;
        $user->email = $subscription->email;
        $user->subscriptionPlan = $subscription->plan;
        $user->subscriptionEndDate = $subscription->endDate;
        try {
            $success = Craft::$app->getElements()->saveElement($user);
        } catch (Throwable $e) {
            $success = false;
        }
        if(!$success) {
            return $this->asModelFailure(
                $subscription,
                '',
                'subscription',
                routeParams: [
                    'abort' => true
                ]
            );
        }

        // Assign user group
        $userGroup = Craft::$app->userGroups->getGroupByHandle('subscriber');
        if ($userGroup) {
            try {
                $success = Craft::$app->getUsers()->assignUserToGroups($user->id, [$userGroup->id]);
            } catch (Throwable $e) {
                $success = false;
            }
            if(!$success) {
                return $this->asModelFailure(
                    $subscription,
                    '',
                    'subscription',
                    routeParams: [
                        'abort' => true
                    ]
                );
            }
        }

        // Add address
        $address = new Address();
        $address->ownerId = $user->id;
        $address->title = 'Shipping address';
        $address->countryCode = 'CH';
        $address->addressLine1 = $subscription->addressLine1;
        $address->addressLine2 = $subscription->addressLine2;
        $address->postalCode = $subscription->postalCode;
        $address->locality = $subscription->locality;

        try {
            $success = Craft::$app->getElements()->saveElement($address);
        } catch (Throwable $e) {
            $success = false;
        }
        if(!$success) {
            return $this->asModelFailure(
                $subscription,
                '',
                'subscription',
                routeParams: [
                    'abort' => true
                ]
            );
        }

        return $this->asModelSuccess(
            $subscription,
            '',
            'subscription',
        );
    }
}
