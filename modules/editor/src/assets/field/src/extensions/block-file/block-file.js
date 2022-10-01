import { Node } from "@tiptap/core";
import { Plugin } from "prosemirror-state";

import { getBlockNodeView } from "@extensions/helpers.js";

const BlockFile = Node.create({
  name: "blockFile",

  group: "block",

  content: "asset",

  draggable: true,

  isolating: true,

  parseHTML() {
    return [
      {
        tag: "figure[data-block='file']",
      },
    ];
  },

  renderHTML() {
    return [
      "figure",
      {
        "data-block": "file",
      },
      0,
    ];
  },

  addCommands() {
    return {
      // InsertFile inserts an file node after the currently selected node.
      insertFile:
        () =>
        ({ commands }) => {
          return commands.insertNodeAfter({
            type: this.name,
            content: [
              {
                type: "asset",
              },
            ],
          });
        },
    };
  },

  addNodeView() {
    return () => {
      return getBlockNodeView({
        className: "block-file",
        label: "File",
      });
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

export default BlockFile;
