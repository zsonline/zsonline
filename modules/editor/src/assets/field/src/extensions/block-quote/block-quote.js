import { Node } from "@tiptap/core";
import { Plugin } from "prosemirror-state";

import { getBlockNodeView, getParentNodeFromPos } from "@extensions/helpers.js";

const BlockQuote = Node.create({
  name: "blockQuote",

  group: "block",

  content: "quote caption",

  draggable: true,

  isolating: true,

  parseHTML() {
    return [
      {
        tag: "figure[data-block='quote']",
      },
    ];
  },

  renderHTML() {
    return ["figure", { "data-block": "quote" }, 0];
  },

  addCommands() {
    return {
      // InsertQuote inserts a quote node after the currently selected node.
      insertQuote:
        () =>
        ({ commands }) => {
          return commands.insertNodeAfter({
            type: this.name,
            content: [
              {
                type: "quote",
              },
              {
                type: "caption",
              },
            ],
          });
        },
    };
  },

  addNodeView() {
    return () => {
      return getBlockNodeView({ className: "block-quote", label: "Quote" });
    };
  },

  addKeyboardShortcuts() {
    return {
      // When backspace is pressed, delete the node if it is empty and the start
      // of the node is selected.
      Backspace: ({ editor }) => {
        const { $anchor } = editor.state.selection;

        // Check that it is a quote node
        const { node } = getParentNodeFromPos($anchor);
        if (node.type.name != this.name) {
          return false;
        }

        // Check that the cursor is at the beginning of the quote node
        const isAtStart = $anchor.parentOffset == 0;
        if ($anchor.parent.type.name != "quote" || !isAtStart) {
          return false;
        }

        // Check that the child nodes are empty
        const quote = node.content.content[0];
        const caption = node.content.content[1];
        const empty = quote.content.size == 0 && caption.content.size == 0;
        if (!empty) {
          return false;
        }

        // Delete node
        return editor.commands.deleteNode(this.name);
      },
    };
  },

  addProseMirrorPlugins() {
    return [
      // The plugin prevent child nodes from being dragged out of their parent
      // node.
      new Plugin({
        props: {
          handleDOMEvents: {
            dragstart: (view, event) => {
              if (!event.target) {
                return false;
              }

              const pos = view.posAtDOM(event.target, 0);
              const $pos = view.state.doc.resolve(pos);

              if ($pos.parent.type === this.type) {
                event.preventDefault();
              }

              return false;
            },
          },
        },
      }),
    ];
  },
});

export default BlockQuote;
