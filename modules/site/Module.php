<?php

namespace site;

use Craft;
use Twig\Extra\Html\HtmlExtension;
use yii\base\Module as BaseModule;

class Module extends BaseModule
{
    public function init(): void
    {
        Craft::setAlias('@site', __DIR__);

        if (!Craft::$app->getRequest()->getIsConsoleRequest()) {
            $this->controllerNamespace = 'site\\controllers';
        }

        parent::init();

        if (Craft::$app->request->getIsSiteRequest()) {
            // Register the twig HTML extension, which adds a html_classes function
            // https://twig.symfony.com/doc/3.x/functions/html_classes.html
            Craft::$app->view->registerTwigExtension(new HtmlExtension());
        }
    }
}
