<script setup>
import { ref, watch } from "vue";

import { useEditor, EditorContent } from "@tiptap/vue-3";

// Nodes
import Asset from "@extensions/asset/asset.js";
import BlockEmbed from "@extensions/block-embed/block-embed.js";
import BlockFile from "@extensions/block-file/block-file.js";
import BlockGallery from "@extensions/block-gallery/block-gallery.js";
import BlockImage from "@extensions/block-image/block-image.js";
import BlockNote from "@extensions/block-note/block-note.js";
import BlockQuote from "@extensions/block-quote/block-quote.js";
import BulletList from "@tiptap/extension-bullet-list";
import Caption from "@extensions/caption/caption.js";
import Document from "@tiptap/extension-document";
import HardBreak from "@tiptap/extension-hard-break";
import Heading from "@extensions/heading/heading.js";
import ListItem from "@extensions/list-item/list-item.js";
import OrderedList from "@extensions/ordered-list/ordered-list.js";
import Paragraph from "@tiptap/extension-paragraph";
import Quote from "@extensions/quote/quote.js";
import Text from "@tiptap/extension-text";
import Title from "@extensions/title/title.js";

// Marks
import Bold from "@tiptap/extension-bold";
import Italic from "@tiptap/extension-italic";
import Link from "@extensions/link/link.js";
import Subscript from "@extensions/subscript/subscript.js";
import Superscript from "@extensions/superscript/superscript.js";

// Other
import BubbleMenu from "@tiptap/extension-bubble-menu";
import Commands from "@extensions/commands.js";
import Dropcursor from "@tiptap/extension-dropcursor";
import Gapcursor from "@tiptap/extension-gapcursor";
import Handle from "@extensions/handle/handle.js";
import History from "@tiptap/extension-history";
import MarksMenu from "@components/MarksMenu.vue";
import Placeholder from "@tiptap/extension-placeholder";
import TopMenu from "@components/TopMenu.vue";
import Typography from "@tiptap/extension-typography";

const props = defineProps({
  content: {
    type: Object,
    required: true,
  },
  settings: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["update:content"]);

const editor = useEditor({
  content: props.content,
  editable: props.settings.editable,
  extensions: [
    // Nodes
    Asset.configure({
      sources: props.settings.sources,
    }),
    BlockEmbed,
    BlockFile,
    BlockGallery,
    BlockImage,
    BlockNote,
    BlockQuote,
    BulletList,
    Document,
    Caption,
    HardBreak,
    Heading.configure({
      levels: [3, 4],
    }),
    ListItem,
    OrderedList,
    Paragraph,
    Quote,
    Text,
    Title,

    // Marks
    Bold,
    Italic,
    Link.configure({
      openOnClick: false,
    }),
    Subscript,
    Superscript,

    // Other
    BubbleMenu,
    Commands,
    Dropcursor,
    Gapcursor,
    ...(props.settings.editable ? [Handle] : []),
    History,
    Placeholder.configure({
      placeholder: ({ node }) => {
        switch (node.type.name) {
          case "caption":
            return "Caption";
          case "heading":
            return "Heading";
          case "paragraph":
            return "Paragraph";
          case "quote":
            return "Quote";
          case "title":
            return "Title";
          default:
            return "";
        }
      },
      includeChildren: true,
      showOnlyCurrent: false,
    }),
    Typography,
  ],
  onBlur: () => {
    isFocused.value = false;
  },
  onFocus: () => {
    isFocused.value = true;
  },
  onUpdate: ({ editor }) => {
    emit("update:content", editor.getJSON());
  },
});

watch(props.content, (value) => {
  if (JSON.stringify(editor.getJSON()) === JSON.stringify(value)) {
    return;
  }

  editor.commands.setContent(value, false);
});

const isFocused = ref(false);
</script>

<template>
  <div id="editor" class="editor" :class="{ focused: isFocused }">
    <TopMenu v-if="props.settings.editable" :editor="editor" />
    <MarksMenu v-if="props.settings.editable" :editor="editor" />

    <EditorContent :editor="editor" />
  </div>
</template>

<style lang="scss" scoped>
.editor {
  // Content
  --content-foreground: hsl(0deg, 0%, 1%);
  --content-foreground-subdued: hsl(0deg, 0%, 75%);
  --content-background: hsl(0deg, 0%, 99%);
  --content-background-subdued: hsl(0deg, 0%, 95%);

  // User interface
  --interface-foreground: hsl(0deg, 0%, 1%);
  --interface-foreground-alt: hsl(0deg, 0%, 5%);
  --interface-foreground-subdued: hsl(0deg, 0%, 5%);
  --interface-foreground-subdued-alt: hsl(0deg, 0%, 10%);
  --interface-background: hsl(0deg, 0%, 97%);
  --interface-background-alt: hsl(0deg, 0%, 90%);
  --interface-background-subdued: hsl(0deg, 0%, 90%);
  --interface-background-subdued-alt: hsl(0deg, 0%, 80%);
  --interface-border: hsl(0deg, 0%, 90%);

  // Drag handle
  --handle-foreground: hsl(0deg, 0%, 75%);
  --handle-foreground-alt: hsl(0deg, 0%, 25%);
  --handle-background: transparent;
  --handle-background-alt: var(--interface-background);

  // Asset
  --asset-foreground: hsl(0deg, 0%, 30%);
  --asset-foreground-subdued: hsl(0deg, 0%, 60%);
  --asset-background: var(--content-background-subdued);
  --asset-border: var(--interface-border);

  border: solid 1px var(--interface-border);
  border-radius: 3px;

  &.focused {
    box-shadow: var(--focus-ring); // Inherited from Craft
  }
}

:deep(.ProseMirror) {
  position: relative;
  min-height: 500px;
  padding: 30px max(50px, calc(0.5 * (100% - 500px)));
  font-family: serif;
  font-size: 16px;
  line-height: 160%;
  color: var(--content-foreground);
  background-color: transparent;
  background-color: var(--content-background);
  outline: none;
  box-shadow: none;

  .is-empty::before {
    float: left;
    height: 0;
    color: var(--content-foreground-subdued);
    pointer-events: none;
    content: attr(data-placeholder);
  }

  a {
    text-decoration: underline;
  }

  sup,
  sub {
    position: relative;
    padding: 0 0.1em;
    font-size: 0.7em;
    vertical-align: baseline;
  }

  sup {
    top: -0.75em;
  }

  sub {
    top: 0.25em;
  }

  h3,
  h4,
  h5 {
    line-height: 160%;
    font-weight: 700;
  }

  h3 {
    margin: 1.25em 0 0.5em 0;
    font-size: 1.3em;
  }

  h4 {
    margin: 1.25em 0 0.5em 0;
    font-size: 1.1em;
  }

  p,
  ul,
  ol {
    margin: 0.5em 0;
  }

  h3,
  h4,
  p {
    &:first-child {
      margin-top: 0;
    }

    &:last-child {
      margin-bottom: 0;
    }
  }

  ul,
  ol {
    padding-left: 1em;

    li {
      padding-left: 0.5em;
    }

    li,
    p {
      margin: 0.25em 0;
    }
  }

  ul {
    li {
      list-style-type: "\2013";
    }
  }

  figcaption {
    line-height: 150%;
  }

  .block {
    margin: 1em 0;
    border: solid 1px var(--interface-border);
    border-radius: 3px;

    .block-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 10px 15px;
      font-family: sans-serif;
      font-size: 0.75em;
      background-color: var(--interface-background);
      border-bottom: solid 1px var(--interface-border);
    }

    .block-title {
      font-weight: 700;
      color: var(--interface-foreground);
      text-transform: uppercase;
      font-size: 1.25em;
      letter-spacing: 0.1em;
    }

    .block-menu {
      display: flex;
      gap: 1em;
      padding: 0;
      margin: 0;

      li {
        padding: 0;
        margin: 0;
        list-style: none;

        button {
          padding: 0 0.75em;
          color: var(--interface-foreground-subdued);
          background-color: var(--interface-background-subdued);
          border-radius: 3px;

          &:hover,
          &:focus,
          &:active,
          &.active {
            color: var(--interface-foreground-subdued-alt);
            background-color: var(--interface-background-subdued-alt);
          }
        }
      }
    }

    .block-content {
      padding: 15px;
    }
  }

  .block-note {
    font-family: sans-serif;
    font-size: 0.9em;

    h5 {
      color: var(--content-foreground);
    }
  }

  .block-gallery {
    display: flex;
    flex-direction: column;

    > * {
      &:not(:first-child) {
        padding-top: 1em;
      }

      &:not(:last-child) {
        border-bottom: solid 1px var(--asset-border);
        padding-bottom: 1em;
      }
    }
  }

  .block-quote {
    blockquote {
      margin-bottom: 0.25em;
      font-size: 1.5em;
      line-height: 140%;
    }

    figcaption {
      font-family: sans-serif;
      font-size: 0.9em;
    }
  }
}
</style>
