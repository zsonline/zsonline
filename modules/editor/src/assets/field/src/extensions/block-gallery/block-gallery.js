import { Node } from "@tiptap/core";
import { Plugin } from "prosemirror-state";

import { getBlockNodeView, getParentNodeFromPos } from "@extensions/helpers.js";

const BlockGallery = Node.create({
  name: "blockGallery",

  group: "block",

  content: "asset+",

  draggable: true,

  isolating: true,

  parseHTML() {
    return [
      {
        tag: "figure[data-block='gallery']",
      },
    ];
  },

  renderHTML() {
    return [
      "figure",
      {
        "data-block": "gallery",
      },
      0,
    ];
  },

  addCommands() {
    return {
      // InsertGallery inserts a gallery node after the currently selected node.
      insertGallery:
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
      // AddGalleryAsset inserts an asset node at the end of the blockGallery
      // node.
      addGalleryAsset:
        () =>
        ({ commands, editor }) => {
          // Abort command if no blockGallery node is selected
          if (
            editor?.state?.selection?.node?.type?.name !== this.name &&
            editor.state.selection.$anchor.node()?.type?.name !== this.name
          ) {
            return;
          }

          // Step into node
          const { end } = getParentNodeFromPos(
            editor.view.state.doc.resolve(
              editor.state.selection.$anchor.pos + 1
            )
          );

          // Insert new child node at the end
          commands.insertContentAt(
            { from: end, to: end },
            {
              type: "asset",
            }
          );
        },
    };
  },

  addNodeView() {
    return ({ editor, getPos }) => {
      const actions = [
        {
          label: "Add",
          action: () => {
            editor.commands.setNodeSelection(getPos());
            editor.commands.addGalleryAsset();
            editor.commands.focus();
          },
          active: () => false,
        },
      ];

      return getBlockNodeView({
        className: "block-gallery",
        label: "Gallery",
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

export default BlockGallery;
