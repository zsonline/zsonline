<?php

namespace editor\controllers;

use Craft;
use craft\elements\Asset;
use craft\helpers\Assets;
use craft\web\Controller;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class FieldController extends Controller
{
    public function actionPreviewAsset(): Response
    {
        $this->requireCpRequest();
        $this->requireAcceptsJson();

        $assetId = $this->request->getRequiredParam('assetId');

        $asset = Asset::findOne($assetId);
        if (!$asset) {
            throw new BadRequestHttpException("Invalid asset ID: $assetId");
        }

        [$width, $height] = Assets::scaledDimensions((int)$asset->getWidth(), (int)$asset->getHeight(), 150, 150);

        return $this->asJson([
            'caption' => $asset->caption,
            'filename' => $asset->filename,
            'url' => Craft::$app->getAssets()->getThumbUrl($asset, $width, $height)
        ]);
    }
}
