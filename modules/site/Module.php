<?php

namespace site;

use Craft;
use craft\base\Element;
use craft\elements\User;
use craft\events\RegisterElementExportersEvent;
use craft\i18n\PhpMessageSource;
use site\exporters\PrintingExporter;
use Twig\Extra\Html\HtmlExtension;
use yii\base\Event;
use yii\base\Module as BaseModule;

class Module extends BaseModule
{
    public function init(): void
    {
        Craft::setAlias('@site', __DIR__);

        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            $this->controllerNamespace = 'site\\console';
        } else {
            $this->controllerNamespace = 'site\\controllers';
        }

        parent::init();

        if (Craft::$app->request->getIsSiteRequest()) {
            // Register the twig HTML extension, which adds a html_classes function
            // https://twig.symfony.com/doc/3.x/functions/html_classes.html
            Craft::$app->view->registerTwigExtension(new HtmlExtension());
        }

        Craft::$app->i18n->translations['site'] = [
            'class' => PhpMessageSource::class,
            'sourceLanguage' => 'en',
            'basePath' => __DIR__ . '/translations',
            'allowOverrides' => true,
        ];

        Event::on(
            User::class,
            Element::EVENT_REGISTER_EXPORTERS,
            static function(RegisterElementExportersEvent $event) {
                $event->exporters[] = PrintingExporter::class;
            }
        );
    }
}
