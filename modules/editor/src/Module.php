<?php

namespace editor;

use Craft;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\i18n\PhpMessageSource;
use craft\services\Fields;
use craft\web\View;
use editor\fields\Editor;
use editor\assets\field\FieldAsset;
use nystudio107\pluginvite\services\VitePluginService;
use yii\base\Event;
use yii\base\Module as BaseModule;

class Module extends BaseModule
{
    public function init(): void
    {
        Craft::setAlias('@editor', __DIR__);

        parent::init();

        $this->registerFields();
        $this->registerServices();
        $this->registerTemplates();
        $this->registerTranslations();
    }

    public function getVite(): VitePluginService
    {
        return $this->get('vite');
    }

    private function registerFields(): void
    {
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            static function(RegisterComponentTypesEvent $event) {
                $event->types[] = Editor::class;
            }
        );
    }

    private function registerServices(): void
    {
        $this->setComponents([
            'vite'  => [
                'class' => VitePluginService::class,
                'assetClass' => FieldAsset::class,
                'useDevServer' => true,
                'devServerPublic' => Craft::getAlias('@web') . ':5173',
                'serverPublic' => Craft::getAlias('@web'),
                'errorEntry' => 'field/src/index.js',
                'devServerInternal' => 'http://localhost:5173',
                'checkDevServer' => true,
            ],
        ]);
    }

    private function registerTemplates(): void
    {
        Event::on(
            View::class,
            View::EVENT_REGISTER_CP_TEMPLATE_ROOTS,
            static function(RegisterTemplateRootsEvent $event) {
                $event->roots['editor'] = __DIR__ . '/templates';
            }
        );
    }

    private function registerTranslations(): void
    {
        Craft::$app->i18n->translations['editor'] = [
            'class' => PhpMessageSource::class,
            'sourceLanguage' => 'en',
            'basePath' => __DIR__ . '/translations',
            'allowOverrides' => true,
        ];
    }
}
