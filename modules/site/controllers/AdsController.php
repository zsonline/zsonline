<?php

namespace site\controllers;

use craft\elements\Entry;
use craft\web\Controller;
use yii\db\Expression;
use yii\web\Response;

class AdsController extends Controller
{
    protected int|bool|array $allowAnonymous = true;

    /**
     * Returns the urls of two active ads in random order.
     *
     * @return Response
     */
    public function actionRandom()
    {
        // Query ads
        $ads = Entry::find()
            ->section('ads')
            ->with([
                ['adImage', [
                    'withTransforms'=> [
                        [
                            'height' => 250,
                            'width' => 300
                        ],
                        [
                            'height' => 500,
                            'width' => 600
                        ]
                    ]
                ]]
            ])
            ->orderBy(new Expression('rand()'))
            ->limit(2)
            ->collect();

        // Get urls of the asset transforms
        $output = [];
        foreach($ads as $ad) {
            $asset = $ad->adImage->first();
            if(!$asset) {
                continue;
            }

            $output[] = [
                'images' => [
                    '1x' => [
                        'height' => min($asset->height, 250),
                        'width' => min($asset->width, 300),
                        'url' => $asset->getUrl([
                            'height' => 250,
                            'width' => 300
                        ])
                    ],
                    '2x' => [
                        'height' => min($asset->height, 500),
                        'width' => min($asset->width, 600),
                        'url' => $asset->getUrl([
                            'height' => 500,
                            'width' => 600
                        ])
                    ],
                ],
                'slug' => $ad->slug,
                'url' => $ad->adLink,
            ];
        }

        return $this->asJson($output);
    }
}
