<?php

namespace editor\assets\field;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\web\assets\prismjs\PrismJsAsset;

class FieldAsset extends AssetBundle
{
    public function init(): void
    {
        $this->sourcePath = __DIR__ . '/dist/';

        $this->depends = [
            CpAsset::class,
            PrismJsAsset::class
        ];

        parent::init();
    }
}
