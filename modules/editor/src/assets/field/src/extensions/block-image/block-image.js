import { Node } from "@tiptap/core";
import { Plugin } from "prosemirror-state";

import { getBlockNodeView } from "@extensions/helpers.js";

const BlockImage = Node.create({
  name: "blockImage",

  addAttributes() {
    return {
      size: {
        default: "medium",
      },
    };
  },

  group: "block",

  content: "asset",

  draggable: true,

  isolating: true,

  parseHTML() {
    return [
      {
        tag: "figure[data-block='image']",
        getAttrs: (dom) => {
          return {
            size: dom.dataset.size,
          };
        },
      },
    ];
  },

  renderHTML({ node }) {
    return [
      "figure",
      {
        "data-block": "image",
        "data-size": node.attrs.size,
      },
      0,
    ];
  },

  addCommands() {
    return {
      // InsertImage inserts an image node after the currently selected node.
      insertImage:
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
      // SetImageSize updates the size attribute of the selected node.
      setImageSize:
        (size) =>
        ({ commands }) => {
          return commands.updateAttributes("blockImage", {
            size: size,
          });
        },
    };
  },

  addNodeView() {
    return ({ editor, getPos, node }) => {
      const actions = [
        {
          label: "Small",
          action: () => {
            editor.commands.setNodeSelection(getPos());
            editor.commands.setImageSize("small");
            editor.commands.focus();
          },
          active: () => node.attrs.size == "small",
        },
        {
          label: "Medium",
          action: () => {
            editor.commands.setNodeSelection(getPos());
            editor.commands.setImageSize("medium");
            editor.commands.focus();
          },
          active: () => node.attrs.size == "medium",
        },
        {
          label: "Large",
          action: () => {
            editor.commands.setNodeSelection(getPos());
            editor.commands.setImageSize("large");
            editor.commands.focus();
          },
          active: () => node.attrs.size == "large",
        },
      ];

      return getBlockNodeView({
        className: "block-image",
        label: "Image",
        actions: actions,
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

export default BlockImage;
