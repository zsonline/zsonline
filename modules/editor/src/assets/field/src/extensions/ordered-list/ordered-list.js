import DefaultOrderedList from "@tiptap/extension-ordered-list";

// The ordered list node is identical to the default ordered list node except
// that it has no start attribute for defining the first item number.
const OrderedList = DefaultOrderedList.extend({
  addAttributes() {
    return {};
  },
});

export default OrderedList;
