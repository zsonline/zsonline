import { Node } from "@tiptap/core";
import { Plugin } from "prosemirror-state";

import { getBlockNodeView, getParentNodeFromPos } from "@extensions/helpers.js";

const Note = Node.create({
  name: "blockNote",

  group: "block",

  content: "title (paragraph|list)+",

  draggable: true,

  isolating: true,

  parseHTML() {
    return [
      {
        tag: "div[data-block='note']",
      },
    ];
  },

  renderHTML() {
    return ["div", { "data-block": "note" }, 0];
  },

  addCommands() {
    return {
      // InsertNote inserts a note node after the currently selected node.
      insertNote:
        () =>
        ({ commands }) => {
          return commands.insertNodeAfter({
            type: this.name,
            content: [
              {
                type: "title",
              },
              {
                type: "paragraph",
              },
            ],
          });
        },
    };
  },

  addNodeView() {
    return () => {
      return getBlockNodeView({ className: "block-note", label: "Note" });
    };
  },

  addKeyboardShortcuts() {
    return {
      // When backspace is pressed, delete the node if it is empty and the start
      // of the node is selected.
      Backspace: ({ editor }) => {
        const { $anchor } = this.editor.state.selection;

        // Check that it is a note node
        const { node } = getParentNodeFromPos($anchor);
        if (node.type.name != this.name) {
          return false;
        }

        // Check that the cursor is at the beginning of the title node
        const isAtStart = $anchor.parentOffset == 0;
        if ($anchor.parent.type.name != "title" || !isAtStart) {
          return false;
        }

        // Check that the child nodes are empty
        const title = node.content.content[0];
        const empty =
          node.content.content.length == 2 &&
          title.content.size == 0 &&
          node.content.content[1].content.size == 0;
        if (!empty) {
          return false;
        }

        // Delete node
        return editor.commands.deleteNode(this.name);
      },
      // When enter is pressed, escape from the node if the currently selected
      // child node is an empty paragraph node that is positioned at the end.
      Enter: ({ editor }) => {
        const { $anchor } = editor.state.selection;

        // Check that it is a note node
        const { node, start, end, after } = getParentNodeFromPos($anchor);
        if (node.type.name != this.name) {
          return false;
        }

        // Check that the cursor is in the last child node
        const isInLastChildNode = $anchor.pos == end - 1;
        if (!isInLastChildNode) {
          return false;
        }

        // Check that the last child node is an empty paragraph node
        const lastChild = node.content.content[node.content.content.length - 1];
        const empty = lastChild.content.size == 0;
        if (!empty || lastChild.type.name != "paragraph") {
          return false;
        }

        // Insert new paragraph node below
        return editor.commands.insertNodeAfter({ type: "paragraph" });
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

export default Note;
