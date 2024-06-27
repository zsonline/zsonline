<?php

namespace site\exporters;

use Craft;
use craft\base\ElementExporter;
use craft\elements\db\ElementQueryInterface;

class PrintingExporter extends ElementExporter
{
    public static function displayName(): string
    {
        return Craft::t('site', 'Print');
    }

    /**
     * Export the subscription data in the format that is required by the
     * printing company.
     *
     * @param ElementQueryInterface $query
     * @return mixed
     */
    public function export(ElementQueryInterface $query): mixed
    {
        $query->with(['subscriptionAddress']);
        $query->limit(null);

        $results = [];
        foreach ($query->each() as $subscription) {
            if($subscription->status != 'live' || $subscription->subscriptionAddress->count() == 0) {
                continue;
            }

            $address = $subscription->subscriptionAddress->first();

            $results[] = [
                'firstName' => $address->firstName,
                'lastName' => $address->lastName,
                'addressLine1' => $address->addressLine1,
                'addressLine2' => $address->addressLine2,
                'postalCode' => $address->postalCode,
                'locality' => $address->locality
            ];
        }

        return $results;
    }
}
