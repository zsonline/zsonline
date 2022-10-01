// getBlockNodeView returns the node view of a block.
const getBlockNodeView = ({
  className = "",
  label = "Block",
  actions = [],
}) => {
  const block = document.createElement("div");
  block.classList.add("block");

  const header = document.createElement("div");
  header.classList.add("block-header");
  header.contentEditable = false;

  const title = document.createElement("h6");
  title.classList.add("block-title");
  title.append(label);

  const menu = document.createElement("ul");
  menu.classList.add("block-menu");
  for (const action of actions) {
    const item = document.createElement("li");

    const button = document.createElement("button");
    button.append(action.label);
    if (action.active()) {
      button.classList.add("active");
    }
    button.addEventListener("click", (e) => {
      e.preventDefault();
      action.action();
    });

    item.append(button);
    menu.append(item);
  }

  header.append(title);
  header.append(menu);

  block.append(header);

  const content = document.createElement("div");
  content.classList.add("block-content");
  if (className) {
    content.classList.add(className);
  }

  block.append(content);

  return {
    dom: block,
    contentDOM: content,
  };
};

// GetParentNodeFromPos returns the shallowest node at a given position.
const getParentNodeFromPos = (position) => {
  const depth = Math.min(1, position.depth);
  const node = position.node(depth);

  const start = position.start(depth);
  const end = position.end(depth);

  let before = start;
  let after = end;
  if (depth > 0) {
    before = position.before(depth);
    after = position.after(depth);
  }

  return {
    node,
    start,
    end,
    before,
    after,
  };
};

export { getBlockNodeView, getParentNodeFromPos };
