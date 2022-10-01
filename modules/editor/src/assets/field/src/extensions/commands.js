import { Extension } from "@tiptap/core";

import { getParentNodeFromPos } from "@extensions/helpers.js";

const Commands = Extension.create({
  addCommands() {
    return {
      // InsertNodeAfter inserts a node after the shallowest parent node that is
      // currently selected.
      insertNodeAfter:
        (node) =>
        ({ commands, editor }) => {
          let position = editor.state.selection.$anchor;

          const { after } = getParentNodeFromPos(position);

          return commands.insertContentAt({ from: after, to: after }, node);
        },
    };
  },
});

export default Commands;
