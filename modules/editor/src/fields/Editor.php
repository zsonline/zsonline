<?php

namespace editor\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\Html;
use craft\helpers\Json;
use editor\Module;
use yii\db\Schema;

class Editor extends Field
{
    public static function displayName(): string
    {
        return Craft::t('editor', 'Editor');
    }

    public string|array|null $embedVolumes = '*';
    public string|array|null $fileVolumes = '*';
    public string|array|null $imageVolumes = '*';

    public array $templates = [];

    public function getContentColumnType(): array|string
    {
        return Schema::TYPE_TEXT;
    }

    public function getInputHtml(mixed $value, ?ElementInterface $element = null): string
    {
        return $this->getFieldHtml($value);
    }

    public function getStaticHtml(mixed $value, ElementInterface $element): string
    {
        return $this->getFieldHtml($value, false);
    }

    public function normalizeValue(mixed $value, ?ElementInterface $element = null): EditorContent
    {
        if ($value instanceof EditorContent) {
            return $value;
        }

        return new EditorContent($value, $this->templates);
    }

    public function serializeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        if ($value instanceof EditorContent) {
            return $value->json();
        }

        return $value;
    }

    public function getSettingsHtml(): ?string
    {
        $templates = [
            "quote" => [
                "label" => Craft::t("editor", "Quote"),
                "node" => "quote",
                "template" => null
            ],
            "note" => [
                "label" => Craft::t("editor", "Note"),
                "node" => "note",
                "template" => null
            ],
            "image" => [
                "label" => Craft::t("editor", "Image"),
                "node" => "image",
                "template" => null
            ],
            "gallery" => [
                "label" => Craft::t("editor", "Gallery"),
                "node" => "gallery",
                "template" => null
            ],
            "embed" => [
                "label" => Craft::t("editor", "Embed"),
                "node" => "embed",
                "template" => null
            ],
            "file" => [
                "label" => Craft::t("editor", "File"),
                "node" => "file",
                "template" => null
            ],
        ];

        foreach ($this->templates as $template) {
            if (!array_key_exists("node", $template) || !array_key_exists("template", $template)) {
                continue;
            }

            if (array_key_exists($template["node"], $templates)) {
                $templates[$template["node"]]["template"] = $template["template"];
            }
        }

        return Craft::$app->getView()->renderTemplate('editor/_settings.twig', [
            'field' => $this,
            'availableEmbedVolumes' => array_map(static fn($volume) => [
                'label' => $volume->name,
                'value' => $volume->uid,
            ], $this->getPrivateVolumes()),
            'availableFileVolumes' => array_map(static fn($volume) => [
                'label' => $volume->name,
                'value' => $volume->uid,
            ], $this->getPublicVolumes()),
            'availableImageVolumes' => array_map(static fn($volume) => [
                'label' => $volume->name,
                'value' => $volume->uid,
            ], $this->getPublicVolumes()),
            'templates' => array_values($templates)
        ]);
    }

    public function getSearchKeywords(mixed $value, ElementInterface $element): string
    {
        if ($value instanceof EditorContent) {
            return $value->text();
        }

        return parent::getSearchKeywords($value, $element);
    }

    private function getFieldHtml(mixed $value, bool $editable = true): string
    {
        $view = Craft::$app->getView();

        // Register asset bundle
        Module::getInstance()->getVite()->register('field/src/index.js');

        // Register editor construction script
        $name = $this->handle;
        $id = Html::id($this->handle);

        $props = [
            'id' => $view->namespaceInputId($id),
            'name' => $view->namespaceInputName($name),
            'settings' => [
                'editable' => $editable,
                'sources' => [
                    'embed' => $this->getAllowedVolumes($this->embedVolumes, $this->getPrivateVolumes()),
                    'file' => $this->getAllowedVolumes($this->fileVolumes, $this->getPublicVolumes()),
                    'gallery' => $this->getAllowedVolumes($this->imageVolumes, $this->getPublicVolumes()),
                    'image' => $this->getAllowedVolumes($this->imageVolumes, $this->getPublicVolumes()),
                ]
            ],
            'value' => $value->json()
        ];

        // We distinguish two cases:
        //   1. If this script is called *after* the editor script has been
        //      loaded, we call it right away.
        //   2. If this script is called *before* the editor script has been
        //      loaded, we call it only after receiving the editor-loaded event.
        $view->registerJs(
            'if (Craft.Editor) {
                new Craft.Editor(' . Json::encode($props) . ');
            } else {
                document.addEventListener("editor-loaded", function(e) {
                    new Craft.Editor(' . Json::encode($props) . ');
                });
            }'
        );

        // The loader element is removed, once the Vue component is mounted
        $loader = Html::tag('div', Craft::t("editor", 'Loading') . '...', [
            'style' => 'padding: 10px;  border-radius: 5px; background-color: hsla(0deg, 0%, 50%, 0.25);',
        ]);

        return Html::tag('div', $loader, [
            'id' => $id,
        ]);
    }

    private function getPublicVolumes(): array
    {
        $availableVolumes = [];

        foreach (Craft::$app->getVolumes()->getAllVolumes() as $volume) {
            if ($volume->getFs()->hasUrls) {
                $availableVolumes[] = $volume;
            }
        }

        return $availableVolumes;
    }

    private function getPrivateVolumes(): array
    {
        $availableVolumes = [];

        foreach (Craft::$app->getVolumes()->getAllVolumes() as $volume) {
            if (!$volume->getFs()->hasUrls) {
                $availableVolumes[] = $volume;
            }
        }

        return $availableVolumes;
    }

    private function getAllowedVolumes(string|array|null $volumes, array $availableVolumes): array
    {
        if (!$volumes) {
            return [];
        }

        $userService = Craft::$app->getUser();

        $allowedVolumes = [];

        foreach ($availableVolumes as $volume) {
            $allowedBySettings = $volumes === '*' ||
                (is_array($volumes) && in_array($volume->uid, $volumes, true));

            if ($allowedBySettings && $userService->checkPermission("viewAssets:$volume->uid")) {
                $allowedVolumes[] = "volume:$volume->uid";
            }
        }

        return $allowedVolumes;
    }
}
