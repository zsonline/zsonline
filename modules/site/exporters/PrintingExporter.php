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
        $query->with(['addresses']);

        $results = [];
        foreach ($query->each() as $user) {
            if($user->addresses->count() == 0) {
                continue;
            }

            $address = $user->addresses->first();

            $results[] = [
                'firstName' => $user->firstName,
                'lastName' => $user->lastName,
                'addressLine1' => $address->addressLine1,
                'addressLine2' => $address->addressLine2,
                'postalCode' => $address->postalCode,
                'locality' => $address->locality
            ];
        }

        return $results;
    }
}
