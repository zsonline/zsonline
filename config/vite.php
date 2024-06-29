<?php

use craft\helpers\App;

return [
    'useDevServer' => App::env('DEV_MODE'),
    'manifestPath' => '@webroot/dist/.vite/manifest.json',
    'devServerPublic' => Craft::getAlias('@web') . ':5173',
    'serverPublic' => Craft::getAlias('@web') . '/dist',
    'errorEntry' => '',
    'cacheKeySuffix' => '',
    'devServerInternal' => 'http://localhost:5173',
    'checkDevServer' => true,
    'includeReactRefreshShim' => false,
    'includeModulePreloadShim' => false,
    'criticalPath' => '@webroot/dist/criticalcss',
    'criticalSuffix' =>'_critical.min.css',
];
