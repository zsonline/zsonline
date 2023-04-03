<?php

namespace editor\fields;

use Craft;
use craft\elements\Asset;
use Exception;
use Illuminate\Support\Collection;
use JsonException;
use Twig\Markup;

class EditorContent extends Markup
{
    private Collection $assets;
    private array $templates;
    private array $value;

    /**
     * Parses the value into an array.
     *
     * @param mixed $value
     * @param array|null $templates
     * @throws JsonException
     */
    public function __construct(mixed $value, ?array $templates = [])
    {
        // Parse value
        if (empty($value)) {
            $value = [];
        }

        if (!is_array($value)) {
            $value = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        }

        $this->value = $value;

        // Initialise templates variable as a [ key => value ] array
        $this->templates = array_reduce($templates, static function ($carry, $item) {
            if (array_key_exists("node", $item) && array_key_exists("template", $item)) {
                $carry[$item["node"]] = $item["template"];
            }

            return $carry;
        }, []);

        parent::__construct(null, null);
    }

    /**
     * Returns the value as an HTML string.
     *
     * This method is called when accessing the field variable in a Twig
     * template.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->html();
    }

    /**
     * Returns the value as a string, without any markup.
     *
     * @return string
     */
    public function text(): string
    {
        return $this->traverse($this->value, function ($node, $children) {
            if(count($children) > 0) {
                return array_reduce($children, fn($carry, $item) => $carry . $item, '');
            }

            if(array_key_exists('text', $node)) {
                return $node['text'];
            }

            return '';
        });
    }

    /**
     * Returns the same data as was passed into the constructor as a JSON
     * string.
     *
     * @return string
     * @throws JsonException
     */
    public function json(): string
    {
        if(empty($this->value)) {
            return '';
        }

        return json_encode($this->value, JSON_THROW_ON_ERROR);
    }

    /**
     * Returns the value as an HTML string.
     *
     * @return string
     */
    public function html(): string
    {
        // Collect asset IDs from content
        $assetIds = $this->traverse($this->value, function ($node, $children) {
            $assetIds = [];

            if ((($node['type'] ?? '') == 'asset') && array_key_exists('id', $node['attrs'] ?? [])) {
                $assetIds[] = $node['attrs']['id'];
            }

            return array_merge($assetIds, ...$children);
        });

        // Eager-load assets
        $this->assets = Asset::find()
            ->id($assetIds)
            ->with([
                'contributors.other',
                'contributors.user:user'
            ])
            ->withTransforms([
                // Image
                [ 'width' => 400 ],
                [ 'width' => 650 ],
                [ 'width' => 1024 ],
                [ 'width' => 1440 ],

                // Gallery
                [ 'height' => 300 ],
                [ 'height' => 400 ],
                [ 'height' => 500 ],

                // Lightbox
                [
                    'mode' => 'fit',
                    'height' => 400,
                    'width' => 400
                ],
                [
                    'mode' => 'fit',
                    'height' => 650,
                    'width' => 650
                ],
                [
                    'mode' => 'fit',
                    'height' => 1024,
                    'width' => 1024
                ],
                [
                    'mode' => 'fit',
                    'height' => 1440,
                    'width' => 1440
                ]
            ])
            ->collect();

        return $this->renderNode($this->value);
    }

    /**
     * Recursively transforms a given $node into an HTML string and returns it.
     *
     * @param array $node
     * @return string
     */
    private function renderNode(array $node): string
    {
        // Verify that the node array has the required keys
        if (!array_key_exists("type", $node) || !array_key_exists("content", $node)) {
            return '';
        }

        // Render the children recursively and surround the result with the corresponding HTML tags
        return match ($node["type"]) {
            'blockEmbed' => $this->renderEmbed($node),
            'blockFile' => $this->renderFile($node),
            'blockGallery' => $this->renderGallery($node),
            'blockImage' => $this->renderImage($node),
            'blockNote' => $this->renderNote($node),
            'blockQuote' => $this->renderQuote($node),
            "bulletList" => '<ul>' . $this->renderNodes($node['content']) . '</ul>',
            "heading" => match ($node["attrs"]["level"] ?? 3) {
                3 => '<h3>' . $this->renderInline($node['content']) . '</h3>',
                4 => '<h4>' . $this->renderInline($node['content']) . '</h4>',
            },
            "listItem" => '<li>' . $this->renderNodes($node['content']) . '</li>',
            "orderedList" => '<ol>' . $this->renderNodes($node['content']) . '</ol>',
            "paragraph" => '<p>' . $this->renderInline($node['content']) . '</p>',
            default => $this->renderNodes($node['content']),
        };
    }

    /**
     * Iterates over an array of nodes and renders each one recursively into an
     * HTML string. Returns the concatenation of these HTML strings.
     *
     * @param array $nodes
     * @return string
     */
    private function renderNodes(array $nodes): string
    {
        return array_reduce(
            $nodes,
            fn ($carry, $item) => $carry . $this->renderNode($item),
            ''
        );
    }

    /**
     * Renders a blockQuote node into an HTML string. If the node is invalid or
     * if the template cannot be loaded, an empty string is returned.
     *
     * @param array $node
     * @return string
     */
    private function renderQuote(array $node): string
    {
        // Verify validity
        if (
            (count($node['content']) == 0) ||
            (count($node['content']) > 2) ||
            (count($node['content']) == 1 && ($node['content'][0]['type'] != 'quote')) ||
            (count($node['content']) == 2 && ($node['content'][0]['type'] != 'quote' || $node['content'][1]['type'] != 'caption'))
        ) {
            return '';
        }

        // Get template
        $template = $this->templates['quote'] ?? null;
        if(!$template) {
            return '';
        }

        $quote = $node['content'][0]['content'] ?? [];
        $caption = $node['content'][1]['content'] ?? [];

        // Render template
        try {
            $result = Craft::$app->view->renderTemplate($template, [
                'quote' => $this->renderInline($quote),
                'caption' => $this->renderInline($caption)
            ]);
        } catch (Exception) {
            $result = '';
        }

        return $result;
    }

    /**
     * Renders a blockNote node into an HTML string. If the node is invalid or
     * if the template cannot be loaded, an empty string is returned.
     *
     * @param array $node
     * @return string
     */
    private function renderNote(array $node): string
    {
        // Verify validity
        if (
            count($node['content']) <= 1 ||
            $node['content'][0]['type'] != 'title'
        ) {
            return '';
        }

        // Get template
        $template = $this->templates['note'] ?? null;
        if(!$template) {
            return '';
        }

        $title = $node['content'][0]['content'] ?? [];
        $body = array_slice($node['content'], 1);

        // Render template
        try {
            $result = Craft::$app->view->renderTemplate($template, [
                'title' => $this->renderInline($title),
                'body' => $this->renderNodes($body)
            ]);
        } catch (Exception) {
            $result = '';
        }

        return $result;
    }

    /**
     * Renders a blockImage node into an HTML string. If the node is invalid or
     * if the template cannot be loaded, an empty string is returned.
     *
     * @param array $node
     * @return string
     */
    private function renderImage(array $node): string
    {
        // Verify validity
        if (
            (count($node['content']) != 1) ||
            $node['content'][0]['type'] != 'asset'
        ) {
            return '';
        }

        // Get template
        $template = $this->templates['image'] ?? null;
        if(!$template) {
            return '';
        }

        // Get eager-loaded image element
        $assetId = $node['content'][0]['attrs']['id'] ?? null;
        $asset = $this->assets->filter(fn($item) => $item->id == $assetId && $item->kind == 'image')->first();
        if(!$asset) {
            return '';
        }

        // Render template
        try {
            $result = Craft::$app->view->renderTemplate($template, [
                'image' => $asset,
                'size' => $node['attrs']['size'] ?? 'medium'
            ]);
        } catch (Exception) {
            $result = '';
        }

        return $result;
    }

    /**
     * Renders a blockGallery node into an HTML string. If the node is invalid or
     * if the template cannot be loaded, an empty string is returned.
     *
     * @param array $node
     * @return string
     */
    private function renderGallery(array $node): string
    {
        // Verify validity
        if (
            (count($node['content']) == 0) ||
            array_reduce($node['content'], static fn($carry, $item) => $carry || $item['type'] != 'asset', false)
        ) {
            return '';
        }

        // Get template
        $template = $this->templates['gallery'] ?? null;
        if(!$template) {
            return '';
        }

        // Get eager-loaded image elements
        $imageIds = array_map(static fn($item) => $item['attrs']['id'], $node['content']);

        $assets = collect(array_map(fn($id) => $this->assets->filter(fn($asset) => $asset->id == $id && $asset->kind == 'image')->first(), $imageIds));
        $assets = $assets->filter();  // Remove null items

        if($assets->count() == 0) {
            return '';
        }

        // Render template
        try {
            $result = Craft::$app->view->renderTemplate($template, [
                'images' => $assets
            ]);
        } catch (Exception) {
            $result = '';
        }

        return $result;
    }

    /**
     * Renders a blockEmbed node into an HTML string. If the node is invalid or
     * if the template cannot be loaded, an empty string is returned.
     *
     * @param array $node
     * @return string
     */
    private function renderEmbed(array $node): string
    {
        // Verify validity
        if (
            (count($node['content']) != 1) ||
            $node['content'][0]['type'] != 'asset'
        ) {
            return '';
        }

        // Get template
        $template = $this->templates['embed'] ?? null;
        if(!$template) {
            return '';
        }

        // Get eager-loaded image element
        $assetId = $node['content'][0]['attrs']['id'] ?? null;
        $asset = $this->assets->filter(fn($item) => $item->id == $assetId && $item->kind == 'json')->first();
        if(!$asset) {
            return '';
        }

        // Render template
        try {
            $result = Craft::$app->view->renderTemplate($template, [
                'asset' => $asset
            ]);
        } catch (Exception) {
            $result = '';
        }

        return $result;
    }

    /**
     * Renders a blockFile node into an HTML string. If the node is invalid or
     * if the template cannot be loaded, an empty string is returned.
     *
     * @param array $node
     * @return string
     */
    private function renderFile(array $node): string
    {
        // Verify validity
        if (
            (count($node['content']) != 1) ||
            $node['content'][0]['type'] != 'asset'
        ) {
            return '';
        }

        // Get template
        $template = $this->templates['file'] ?? null;
        if(!$template) {
            return '';
        }

        // Get eager-loaded image element
        $assetId = $node['content'][0]['attrs']['id'] ?? null;
        $asset = $this->assets->filter(fn($item) => $item->id == $assetId && $item->kind == 'pdf')->first();
        if(!$asset) {
            return '';
        }

        // Render template
        try {
            $result = Craft::$app->view->renderTemplate($template, [
                'asset' => $asset
            ]);
        } catch (Exception) {
            $result = '';
        }

        return $result;
    }

    /**
     * Renders an array of inline nodes into an HTML string by recursively
     * applying a different mark on each recursion level.
     *
     * @param array $nodes
     * @param array $marks
     * @return string
     */
    private function renderInline(array $nodes, array $marks = ["link", "bold", "italic", "subscript", "superscript"]): string
    {
        // Check if any marks are left to apply
        if(count($marks) == 0) {
            return '';
        }

        // Pop the first mark
        $mark = array_shift($marks);

        // Stores the HTML output string
        $result = '';

        // Stores all nodes with the same mark
        $bufferMark = [];
        $bufferNodes = [];

        foreach($nodes as $node) {
            // Extract any matching mark from the current node
            $nodeMarkKey = array_search($mark, array_column($node["marks"] ?? [], 'type'), true);
            $nodeMark = $nodeMarkKey !== false ? $node["marks"][$nodeMarkKey] : [];

            if(json_encode($nodeMark) == json_encode($bufferMark)) {
                // If the extracted mark is identical to the buffer mark, add the
                // node to the buffer
                $bufferNodes[] = $node;
            } else {
                // Otherwise, render the current buffer and initialise a new buffer
                // with the current node
                $result .= $this->renderMark($bufferMark, $bufferNodes, $marks);

                $bufferNodes = [ $node ];
                $bufferMark = $nodeMark;
            }
        }

        // Render any node that is still stuck in the buffer
        $result .= $this->renderMark($bufferMark, $bufferNodes, $marks);

        return $result;
    }

    /**
     * Recursively renders an array of inline nodes into an HTML string and
     * surrounds the result with a mark.
     *
     * This function is called by renderInline.
     *
     * @param array $mark
     * @param array $nodes
     * @param array $marks
     * @return string
     */
    private function renderMark(array $mark, array $nodes, array $marks): string
    {
        if(count($marks) == 0) {
            // If no marks are left to apply, iterate over all nodes and
            // concatenate their content
            $children = array_reduce(
                $nodes,
                static function($carry, $item) {
                    return $carry . match ($item['type'] ?? '') {
                            "text" => $item["text"],
                            "hardBreak" => '<br>',
                            default => ''
                        };
                },
                ''
            );
        } else {
            // Otherwise, recursively apply the remaining marks to the nodes
            $children = $this->renderInline($nodes, $marks);
        }

        // Surround the rendered nodes with the mark
        return match ($mark["type"] ?? "") {
            "link" => '<a href="' . ($mark['attrs']['href'] ?? '') . '">' . $children . '</a>',
            "bold" => '<strong>' . $children . '</strong>',
            "italic" => '<em>' . $children . '</em>',
            "subscript" => '<sub>' . $children . '</sub>',
            "superscript" => '<sup>' . $children . '</sup>',
            default => $children,
        };
    }

    /**
     * Traverses the JSON array recursively bottom-up and calls a callback
     * function on each node. The callback function receives the current raw
     * node and the already processed children.
     *
     * @param array $raw
     * @param callable $callback
     * @return mixed
     */
    private function traverse(array $node, callable $callback): mixed
    {
        $children = [];
        if (array_key_exists('content', $node) && is_array($node['content'])) {
            foreach ($node['content'] as $child) {
                $children[] = $this->traverse($child, $callback);
            }
        }

        return $callback($node, $children);
    }
}
