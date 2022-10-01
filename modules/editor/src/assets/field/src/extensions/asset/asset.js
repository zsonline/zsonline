import { Node } from "@tiptap/core";
import { VueNodeViewRenderer } from "@tiptap/vue-3";

import AssetVue from "@extensions/asset/components/Asset.vue";

const Asset = Node.create({
  name: "asset",

  marks: "",

  addOptions() {
    return {
      sources: {},
    };
  },

  addAttributes() {
    return {
      id: {
        default: null,
      },
    };
  },

  selectable: true,

  draggable: false,

  parseHTML() {
    return [
      {
        tag: "div[class='asset']",
        getAttrs: (dom) => {
          return {
            id: dom.dataset.id,
          };
        },
      },
    ];
  },

  renderHTML({ node }) {
    return [
      "div",
      {
        class: "asset",
        "data-id": node.attrs.id,
      },
    ];
  },

  addNodeView() {
    return VueNodeViewRenderer(AssetVue);
  },
});

export default Asset;
