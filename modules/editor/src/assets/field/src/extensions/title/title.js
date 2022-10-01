import { Node } from "@tiptap/core";

const Title = Node.create({
  name: "title",

  content: "text*",

  marks: "subscript superscript",

  selectable: false,

  draggable: false,

  parseHTML() {
    return [
      {
        tag: "h5",
      },
    ];
  },

  renderHTML() {
    return ["h5", {}, 0];
  },
});

export default Title;
