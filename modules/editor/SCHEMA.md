# Schema

Please refer to the [Tiptap](https://tiptap.dev/) documentation for more details.

## Nodes

### `blockEmbed`

_Output:_

```json
{
  "type": "blockEmbed",
  "content": [
    {
      "type": "asset",
      "attrs": {
        "id": 1
      }
    }
  ]
}
```

### `blockFile`

_Output:_

```json
{
  "type": "blockFile",
  "content": [
    {
      "type": "asset",
      "attrs": {
        "id": 1
      }
    }
  ]
}
```

### `blockGallery`

_Output:_

```json
{
  "type": "blockGallery",
  "content": [
    {
      "type": "asset",
      "attrs": {
        "id": 1
      }
    },
    {
      "type": "asset",
      "attrs": {
        "id": 2
      }
    }
  ]
}
```

### `blockImage`

_Attributes:_

- `size`: `small | medium | large`

_Output:_

```json
{
  "type": "blockImage",
  "attrs": {
    "size": "medium"
  },
  "content": [
    {
      "type": "asset",
      "attrs": {
        "id": 1
      }
    }
  ]
}
```

### `blockNote`

_Marks:_

- Bold
- Italic
- Link
- Subscript
- Superscript

_Marks `title`:_

- Subscript
- Superscript

_Output:_

```json
{
  "type": "blockNote",
  "content": [
    {
      "type": "title",
      "content": [
        {
          "type": "text",
          "text": "Note Title"
        }
      ]
    },
    {
      "type": "paragraph",
      "content": [
        {
          "type": "text",
          "text": "This is a note. It can contain"
        }
      ]
    },
    {
      "type": "orderedList",
      "content": [
        {
          "type": "listItem",
          "content": [
            {
              "type": "paragraph",
              "content": [
                {
                  "type": "text",
                  "text": "Paragraphs"
                }
              ]
            }
          ]
        },
        {
          "type": "listItem",
          "content": [
            {
              "type": "paragraph",
              "content": [
                {
                  "type": "text",
                  "text": "Lists"
                }
              ]
            }
          ]
        }
      ]
    }
  ]
}
```

### `blockQuote`

_Marks `quote`:_

- Subscript
- Superscript

_Marks `caption`:_

- Link
- Subscript
- Superscript

_Output:_

```json
{
  "type": "blockQuote",
  "content": [
    {
      "type": "quote",
      "content": [
        {
          "type": "text",
          "text": "«This is a quote.»"
        }
      ]
    },
    {
      "type": "caption",
      "content": [
        {
          "type": "text",
          "text": "Author"
        }
      ]
    }
  ]
}
```

### `bulletList`

`bulletList` supports multiple paragraphs per list item, but not nesting.

_Marks:_

- Bold
- Italic
- Link
- Subscript
- Superscript

_Output:_

```json
{
  "type": "bulletList",
  "content": [
    {
      "type": "listItem",
      "content": [
        {
          "type": "paragraph",
          "content": [
            {
              "type": "text",
              "text": "This is a list item."
            }
          ]
        }
      ]
    },
    {
      "type": "listItem",
      "content": [
        {
          "type": "paragraph",
          "content": [
            {
              "type": "text",
              "text": "This is another list item."
            }
          ]
        }
      ]
    }
  ]
}
```

### `heading`

_Attributes:_

- `level`: `3 | 4`

_Marks:_

- Subscript
- Superscript

_Output:_

```json
{
  "type": "heading",
  "attrs": {
    "level": 3
  },
  "content": [
    {
      "type": "text",
      "text": "This Is a Heading"
    }
  ]
}
```

### `orderedList`

`orderedList` supports multiple paragraphs per list item, but not nesting.

_Marks:_

- Bold
- Italic
- Link
- Subscript
- Superscript

_Output:_

```json
{
  "type": "orderedList",
  "content": [
    {
      "type": "listItem",
      "content": [
        {
          "type": "paragraph",
          "content": [
            {
              "type": "text",
              "text": "This is a list item."
            }
          ]
        }
      ]
    },
    {
      "type": "listItem",
      "content": [
        {
          "type": "paragraph",
          "content": [
            {
              "type": "text",
              "text": "This is another list item."
            }
          ]
        }
      ]
    }
  ]
}
```

### `paragraph`

_Marks:_

- Bold
- Italic
- Link
- Subscript
- Superscript

_Output:_

```json
{
  "type": "paragraph",
  "content": [
    {
      "type": "text",
      "text": "This is a paragraph."
    }
  ]
}
```

## Marks

### `bold`

_Output:_

```json
{
  "type": "paragraph",
  "content": [
    {
      "type": "text",
      "text": "This text is "
    },
    {
      "type": "text",
      "marks": [
        {
          "type": "bold"
        }
      ],
      "text": "bold"
    },
    {
      "type": "text",
      "text": "."
    }
  ]
}
```

### `italic`

_Output:_

```json
{
  "type": "paragraph",
  "content": [
    {
      "type": "text",
      "text": "This text is "
    },
    {
      "type": "text",
      "marks": [
        {
          "type": "italic"
        }
      ],
      "text": "italic"
    },
    {
      "type": "text",
      "text": "."
    }
  ]
}
```

### `link`

_Output:_

```json
{
  "type": "paragraph",
  "content": [
    {
      "type": "text",
      "text": "This text is a "
    },
    {
      "type": "text",
      "marks": [
        {
          "type": "link",
          "attrs": {
            "href": "https://www.zs-online.ch/",
            "target": ""
          }
        }
      ],
      "text": "link"
    },
    {
      "type": "text",
      "text": "."
    }
  ]
}
```

### `subscript`

`subscript` excludes all other marks from being applied to the same selection.

_Output:_

```json
{
  "type": "paragraph",
  "content": [
    {
      "type": "text",
      "text": "This"
    },
    {
      "type": "text",
      "marks": [
        {
          "type": "subscript"
        }
      ],
      "text": "1"
    },
    {
      "type": "text",
      "text": " is a subscript."
    }
  ]
}
```

### `superscript`

`superscript` excludes all other marks from being applied to the same selection.

_Output:_

```json
{
  "type": "paragraph",
  "content": [
    {
      "type": "text",
      "text": "This"
    },
    {
      "type": "text",
      "marks": [
        {
          "type": "superscript"
        }
      ],
      "text": "1"
    },
    {
      "type": "text",
      "text": " is a superscript."
    }
  ]
}
```
