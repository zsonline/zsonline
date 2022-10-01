import { Node } from "@tiptap/core";
import { Plugin } from "prosemirror-state";

import { getBlockNodeView } from "@extensions/helpers.js";

const BlockEmbed = Node.create({
  name: "blockEmbed",

  group: "block",

  content: "asset",

  draggable: true,

  isolating: true,

  parseHTML() {
    return [
      {
        tag: "figure[data-block='embed']",
      },
    ];
  },

  renderHTML() {
    return [
      "figure",
      {
        "data-block": "embed",
      },
      0,
    ];
  },

  addCommands() {
    return {
      // InsertEmbed inserts an image node after the currently selected node.
      insertEmbed:
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
        className: "block-embed",
        label: "Embed",
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

export default BlockEmbed;
