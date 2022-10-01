<?php

use craft\config\GeneralConfig;
use craft\helpers\App;

return GeneralConfig::create()
    // System
    ->aliases([
        '@web' => App::env('SITE_URL')
    ])
    ->allowAdminChanges(App::env('DEV_MODE') ?? false)
    ->defaultCpLanguage('de-CH')
    ->defaultSearchTermOptions([
        'subLeft' => true,
        'subRight' => true,
    ])
    ->defaultWeekStartDay(1)
    ->devMode(App::env('DEV_MODE') ?? false)
    ->disallowRobots(App::env('DEV_MODE') ?? false)
    ->errorTemplatePrefix('_pages/')
    ->limitAutoSlugsToAscii(true)

    // Routing
    ->omitScriptNameInUrls()
    ->pageTrigger('?seite')

    // Assets
    ->convertFilenamesToAscii(true)

    // Image Handling
    ->preserveImageColorProfiles(false)
    ->upscaleImages(false)

    // GraphQL
    ->enableGql(false)
;
