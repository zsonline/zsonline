<?php
namespace site\models;

use Craft;
use craft\base\Model;

class Subscription extends Model
{
    public ?string $plan = null;
    public ?int $endDate = null;

    public ?string $email = null;
    public ?string $name = null;
    public ?string $addressLine1 = null;
    public ?string $addressLine2 = null;
    public ?int $postalCode = null;
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

            [['email'], 'email'],
            [['endDate'], 'integer',
                'min'  => date('Y'),
                'when' => fn($model) => $model->plan == 'regular'
            ],
            [['endDate'], 'integer',
                'min'  => date('Y'),
                'max'  => date('Y', strtotime('+9 year')),
                'when' => fn($model) => $model->plan == 'student'
            ],


            [['name', 'addressLine1', 'addressLine2', 'locality'], 'string',
                'length' => [1]
            ],
            [['name', 'addressLine1', 'addressLine2', 'locality'], 'trim'],
            [['postalCode'], 'integer',
                'min' => 1000,
                'max' => 9999
            ],

            [['plan', 'endDate', 'email', 'name', 'addressLine1', 'postalCode', 'locality'], 'required'],
        ];
    }
}
