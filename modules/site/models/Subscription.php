<?php
namespace site\models;

use Craft;
use craft\base\Model;

class Subscription extends Model
{
    public ?string $plan = null;
    public ?string $endDate = null;

    public ?string $email = null;
    public ?string $name = null;
    public ?string $addressLine1 = null;
    public ?string $addressLine2 = null;
    public ?string $postalCode = null;
    public ?string $locality = null;

    public function attributeLabels()
    {
        return [
            'plan' => Craft::t('site', 'Plan'),
            'endDate' => Craft::t('site', 'Graduation date'),
            'email' => Craft::t('site', 'Email address'),
            'name' => Craft::t('site', 'Name'),
            'addressLine1' => Craft::t('site', 'Address line'),
            'addressLine2' => Craft::t('site', 'Additional address line'),
            'postalCode' => Craft::t('site', 'Postal code'),
            'locality' => Craft::t('site', 'Locality'),
        ];
    }

    public function rules(): array
    {
        return [
            [['plan'], 'in', 'range' => ['regular', 'student']],

            [['endDate'], 'date',
                'format' => 'Y-m-d',
                'min'  => date('Y-m-d', strtotime('+1 year')),
                'when' => fn($model) => $model->plan == 'regular'
            ],
            [['endDate'], 'date',
                'format' => 'Y-m-d',
                'min'  => date('Y-m-d'),
                'max'  => date('Y-m-d', strtotime('+6 year')),
                'when' => fn($model) => $model->plan == 'student'
            ],

            [['email'], 'email'],
            [['email'], 'match',
                'pattern' => '/ethz\.ch$/',
                'message' => Craft::t('site', 'Your email address must end with ethz.ch.'),
                'when' => fn($model) => $model->plan == 'student'
            ],

            [['name', 'addressLine1', 'addressLine2', 'postalCode', 'locality'], 'string',
                'length' => [1]
            ],
            [['name', 'addressLine1', 'addressLine2', 'postalCode', 'locality'], 'trim'],
            [['postalCode'], 'match',
                'pattern' => '/^\d+$/',
            ],

            [['plan', 'endDate', 'email', 'name', 'addressLine1', 'postalCode', 'locality'], 'required'],
        ];
    }
}
