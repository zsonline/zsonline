<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\elements\Entry;
use craft\records\Field;
use craft\records\EntryType;
use yii\base\Exception;

class m240602_212623_content extends Migration
{
    public function safeUp(): bool
    {
        $entryTypeFile = EntryType::find()->where(['handle' => 'contentFile'])->one();
        $entryTypeGallery = EntryType::find()->where(['handle' => 'contentGallery'])->one();
        $entryTypeImage = EntryType::find()->where(['handle' => 'contentImage'])->one();
        $entryTypeNote = EntryType::find()->where(['handle' => 'contentNote'])->one();
        $entryTypeQuote = EntryType::find()->where(['handle' => 'contentQuote'])->one();

        $fieldArticle = Field::find()->where(['handle' => 'articleContent'])->one();

        foreach(Entry::find()->type('article')->all() as $entry) {
            $content = (new Content($entry->getFieldValue('contentContent')->json(), [
               'entryTypeFile' => $entryTypeFile,
               'entryTypeGallery' => $entryTypeGallery,
               'entryTypeImage' => $entryTypeImage,
               'entryTypeNote' => $entryTypeNote,
               'entryTypeQuote' => $entryTypeQuote,
               'field' => $fieldArticle,
               'owner' => $entry,
               'author' => $entry->author
            ]))->convert();

            $entry->setFieldValue('articleContent', $content);

            if (!Craft::$app->elements->saveElement($entry)) {
                throw new Exception("Couldn't save article content: " . implode(', ', $entry->getFirstErrors()));
            }
        }

        $fieldPage = Field::find()->where(['handle' => 'pageContent'])->one();

        foreach(Entry::find()->type('page')->all() as $entry) {
            $content = (new Content($entry->getFieldValue('contentContent')->json(), [
               'entryTypeFile' => $entryTypeFile,
               'entryTypeGallery' => $entryTypeGallery,
               'entryTypeImage' => $entryTypeImage,
               'entryTypeNote' => $entryTypeNote,
               'entryTypeQuote' => $entryTypeQuote,
               'field' => $fieldPage,
               'owner' => $entry,
               'author' => $entry->author
            ]))->convert();

            $entry->setFieldValue('pageContent', $content);

            if (!Craft::$app->elements->saveElement($entry)) {
                throw new Exception("Couldn't save page content: " . implode(', ', $entry->getFirstErrors()));
            }
        }

        return true;
    }

    public function safeDown(): bool
    {
        echo "m240602_212623_content cannot be reverted.\n";
        return false;
    }
}

class Content
{
    private array $value;

    /**
     * Parses the value into an array.
     *
     * @param mixed $value
     * @throws JsonException
     */
    public function __construct(mixed $value, array $context)
    {
        // Parse value
        if (empty($value)) {
            $value = [];
        }

        if (!is_array($value)) {
            $value = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        }

        $this->value = $value;

        $this->entryTypeFile = $context['entryTypeFile'];
        $this->entryTypeGallery = $context['entryTypeGallery'];
        $this->entryTypeImage = $context['entryTypeImage'];
        $this->entryTypeNote = $context['entryTypeNote'];
        $this->entryTypeQuote = $context['entryTypeQuote'];
        $this->field = $context['field'];
        $this->owner = $context['owner'];
        $this->author = $context['author'];
    }

    /**
     * Returns the value as an HTML string.
     *
     * @return string
     */
    public function convert(): string
    {
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
        if (!array_key_exists('type', $node) || !array_key_exists('content', $node)) {
            return '';
        }

        // Render the children recursively and surround the result with the corresponding HTML tags
        return match ($node['type']) {
            // blockEmbed nodes need to be migrated manually
            'blockEmbed' => '',
            'blockFile' => $this->renderFile($node),
            'blockGallery' => $this->renderGallery($node),
            'blockImage' => $this->renderImage($node),
            'blockNote' => $this->renderNote($node),
            'blockQuote' => $this->renderQuote($node),
            'bulletList' => '<ul>' . $this->renderNodes($node['content']) . '</ul>',
            'heading' => match ($node['attrs']['level'] ?? 3) {
                3 => '<h3>' . $this->renderInline($node['content']) . '</h3>',
                // Convert h4 tags to h3
                4 => '<h3>' . $this->renderInline($node['content']) . '</h3>',
            },
            // Unwrap listItems from paragraphs
            'listItem' => '<li>' . str_replace(['<p></p>', '<p>', '</p>'], ['<br />', '', ''], $this->renderNodes($node['content'])) . '</li>',
            'orderedList' => '<ol>' . $this->renderNodes($node['content']) . '</ol>',
            'paragraph' => '<p>' . $this->renderInline($node['content']) . '</p>',
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
     * Creates a quote entry for each blockQuote node and returns a craft-entry
     * tag referencing the entry. If the node is invalid, no entry is created
     * and an empty string is returned.
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

        $quote = $node['content'][0]['content'] ?? [];
        $caption = $node['content'][1]['content'] ?? [];

        // Create element
        $entry = new Entry();
        $entry->typeId = $this->entryTypeQuote->id;
        $entry->fieldId = $this->field->id;
        $entry->ownerId = $this->owner->id;
        $entry->authorId = $this->author->id;

        // Quotes and captions no longer support markup
        $entry->setFieldValue('contentQuoteQuote', trim(strip_tags($this->renderInline($quote))));
        $entry->setFieldValue('contentQuoteCaption', trim(strip_tags($this->renderInline($caption))));
     
        if (!Craft::$app->elements->saveElement($entry)) {
            throw new Exception("Couldn't save quote: " . implode(', ', $entry->getFirstErrors()));
        }
        
        // Render markup
        return '<craft-entry data-entry-id="' . $entry->id . '"> </craft-entry>';
    }

    /**
     * Creates a note entry for each blockNote node and returns a craft-entry
     * tag referencing the entry. If the node is invalid, no entry is created
     * and an empty string is returned.
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

        $title = $node['content'][0]['content'] ?? [];
        $body = array_slice($node['content'], 1);

        // Create element
        $entry = new Entry();
        $entry->typeId = $this->entryTypeNote->id;
        $entry->fieldId = $this->field->id;
        $entry->ownerId = $this->owner->id;
        $entry->authorId = $this->author->id;

        // Note titles no longer support markup
        $entry->setFieldValue('contentNoteTitle', trim(strip_tags($this->renderInline($title))));
        $entry->setFieldValue('contentNoteContent', $this->renderNodes($body));

        if (!Craft::$app->elements->saveElement($entry)) {
            throw new Exception("Couldn't save note: " . implode(', ', $entry->getFirstErrors()));
        }
        
        // Render markup
        return '<craft-entry data-entry-id="' . $entry->id . '"> </craft-entry>';
    }

    /**
     * Creates an image entry for each blockImage node and returns a craft-entry
     * tag referencing the entry. If the node is invalid, no entry is created
     * and an empty string is returned.
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

        $imageId = $node['content'][0]['attrs']['id'] ?? null;
        if (!$imageId) {
            return '';
        }
        $size = $node['attrs']['size'] ?? 'medium';

        // Create element
        $entry = new Entry();
        $entry->typeId = $this->entryTypeImage->id;
        $entry->fieldId = $this->field->id;
        $entry->ownerId = $this->owner->id;
        $entry->authorId = $this->author->id;

        $entry->setFieldValue('contentImageImage', [$imageId]);
        $entry->setFieldValue('contentImageSize', $size);

        if (!Craft::$app->elements->saveElement($entry)) {
            throw new Exception("Couldn't save image: " . implode(', ', $entry->getFirstErrors()));
        }
        
        // Render markup
        return '<craft-entry data-entry-id="' . $entry->id . '"> </craft-entry>';
    }

    /**
     * Creates a gallery entry for each blockGallery node and returns a craft-entry
     * tag referencing the entry. If the node is invalid, no entry is created
     * and an empty string is returned.
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

        $imageIds = array_map(static fn($item) => $item['attrs']['id'], $node['content']);
        $imageIds = array_filter($imageIds);  // Remove null items
        if(!count($imageIds)) {
            return '';
        }

        // Create element
        $entry = new Entry();
        $entry->typeId = $this->entryTypeGallery->id;
        $entry->fieldId = $this->field->id;
        $entry->ownerId = $this->owner->id;
        $entry->authorId = $this->author->id;

        $entry->setFieldValue('contentGalleryImages', $imageIds);

        if (!Craft::$app->elements->saveElement($entry)) {
            throw new Exception("Couldn't save gallery: " . implode(', ', $entry->getFirstErrors()));
        }
        
        // Render markup
        return '<craft-entry data-entry-id="' . $entry->id . '"> </craft-entry>';
    }

    /**
     * Creates a file entry for each blockFile node and returns a craft-entry
     * tag referencing the entry. If the node is invalid, no entry is created
     * and an empty string is returned.
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

        // Get eager-loaded image element
        $fileId = $node['content'][0]['attrs']['id'] ?? null;
        if(!$fileId) {
            return '';
        }

        // Create element
        $entry = new Entry();
        $entry->typeId = $this->entryTypeFile->id;
        $entry->fieldId = $this->field->id;
        $entry->ownerId = $this->owner->id;
        $entry->authorId = $this->author->id;

        $entry->setFieldValue('contentFileFile', [$fileId]);

        if (!Craft::$app->elements->saveElement($entry)) {
            throw new Exception("Couldn't save file: " . implode(', ', $entry->getFirstErrors()));
        }
        
        // Render markup
        return '<craft-entry data-entry-id="' . $entry->id . '"> </craft-entry>';
    }

    /**
     * Renders an array of inline nodes into an HTML string by recursively
     * applying a different mark on each recursion level.
     *
     * @param array $nodes
     * @param array $marks
     * @return string
     */
    private function renderInline(array $nodes, array $marks = ['link', 'italic', 'bold', 'subscript', 'superscript']): string
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
            $nodeMarkKey = array_search($mark, array_column($node['marks'] ?? [], 'type'), true);
            $nodeMark = $nodeMarkKey !== false ? $node['marks'][$nodeMarkKey] : [];

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
                            'text' => $item['text'],
                            'hardBreak' => '<br />',
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
        return match ($mark['type'] ?? '') {
            'link' => '<a href="' . ($mark['attrs']['href'] ?? '') . '">' . $children . '</a>',
            'bold' => '<strong>' . $children . '</strong>',
            'italic' => '<i>' . $children . '</i>',
            // Subscript is no longer supported
            'subscript' => $children,
            // Superscript is no longer supported
            'superscript' => $children,
            default => $children,
        };
    }
}