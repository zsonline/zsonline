import DefaultListItem from "@tiptap/extension-list-item";

// The list item node is identical to the default list item node except that
// it only accepts paragraphs, but no nested lists or other blocks.
const ListItem = DefaultListItem.extend({
  content: "paragraph+",
});

export default ListItem;
