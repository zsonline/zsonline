<?php

namespace site\console;

use Craft;
use craft\console\Controller;
use craft\elements\Address;
use craft\elements\User;
use site\models\Subscription;
use Throwable;
use yii\console\ExitCode;

class SubscriptionsController extends Controller
{
    public ?string $file = null;

    public function options($actionID): array
    {
        $options = parent::options($actionID);

        $options[] = 'file';

        return $options;
    }

    public function actionImport(): int
    {
        if (!$this->file) {
            return ExitCode::TEMPFAIL;
        }

        $rows   = array_map('str_getcsv', file($this->file));
        $header_row = array_shift($rows);

        $data = [];
        foreach($rows as $row) {
            if(!empty($row)){
                $data[] = array_combine($header_row, $row);
            }
        }

        foreach($data as $item) {
            $subscription = new Subscription();
            $subscription->plan = $item['plan'];
            $subscription->endDate = $item['endDate'];
            $subscription->email = $item['email'];
            $subscription->name = $item['name'];
            $subscription->addressLine1 = $item['addressLine1'];
            $subscription->addressLine2 = $item['addressLine2'];
            $subscription->postalCode = $item['postalCode'];
            $subscription->locality = $item['town'];

            // Validate form data
            if (!$subscription->validate()) {
                $this->stdout("Validation failed: " . json_encode($item) . "\n");
                continue;
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
            if (!$success) {
                $this->stdout("User creation failed: " . json_encode($item) . "\n");
                continue;
            }

            // Assign user group
            $userGroup = Craft::$app->userGroups->getGroupByHandle('subscriber');
            if ($userGroup) {
                try {
                    $success = Craft::$app->getUsers()->assignUserToGroups($user->id, [$userGroup->id]);
                } catch (Throwable $e) {
                    $success = false;
                }
                if (!$success) {
                    $this->stdout("User group association failed: " . json_encode($item) . "\n");
                    continue;
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
            if (!$success) {
                $this->stdout("Address creation failed: " . json_encode($item) . "\n");
                continue;
            }
        }

        return ExitCode::OK;
    }
}
