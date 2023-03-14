<?php

return [
    'abo'                       => ['template' => '_pages/subscription.twig'],
    'artikel'                   => ['template' => '_pages/articles.twig'],
    'autor-innen'               => ['template' => '_pages/users.twig'],
    'autor-innen/<slug:{slug}>' => ['template' => '_pages/user.twig'],
    'feed.atom'                 => ['template' => '_pages/feed.twig'],
    'print'                     => ['template' => '_pages/issues.twig'],
    'print/<year:\d+>'          => ['template' => '_pages/issues.twig'],
    'rubriken'                  => ['template' => '_pages/formats.twig'],
    'suche'                     => ['template' => '_pages/search.twig'],
    'sitemap.xml'               => ['template' => '_pages/sitemap.twig'],
    'themen'                    => ['template' => '_pages/topics.twig']
];
