<?php

namespace site;

use Craft;
use craft\base\Element;
use craft\elements\Entry;
use craft\elements\User;
use craft\events\DefineRulesEvent;
use craft\events\RegisterElementExportersEvent;
use craft\helpers\StringHelper;
use craft\i18n\PhpMessageSource;
use craft\records\Section;
use site\exporters\PrintingExporter;
use Twig\Extra\Html\HtmlExtension;
use yii\base\Event;
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

        // Disable list indentation
        if (Craft::$app->getRequest()->getIsCpRequest()) {
            Craft::$app->getView()->registerJs('{
                const editors = document.querySelectorAll(".ck-editor__editable");
                for (const editor of editors) {
                    editor.ckeditorInstance.commands.get("indentList")?.forceDisabled("noListIndent");
                }
            }');
        }

        Craft::$app->i18n->translations['site'] = [
            'class' => PhpMessageSource::class,
            'sourceLanguage' => 'en',
            'basePath' => __DIR__ . '/translations',
            'allowOverrides' => true,
        ];

        Event::on(
            Entry::class,
            Entry::EVENT_REGISTER_EXPORTERS,
            static function(RegisterElementExportersEvent $event) {
                $section = Section::find()->where(['handle' => 'subscriptions'])->one();
                if ($event->source == "section:$section->uid") {
                    $event->exporters = [
                        PrintingExporter::class
                    ];
                    return;
                }

            $event->exporters = [];
            }
        );

        Event::on(User::class, User::EVENT_BEFORE_SAVE, function (Event $event) {
            $user = $event->sender;
            if (!$user->firstSave) {
                return;
            }

            $user->username = StringHelper::slugify($user->fullName);
            $user->email = str_replace('-', '.', $user->username) . '@zsonline.ch';
        });

        Event::on(User::class, User::EVENT_AFTER_SAVE, function (Event $event) {
            $user = $event->sender;
            if (!$user->firstSave) {
                return;
            }

            Craft::$app->users->deactivateUser($user);
        });
    }
}
