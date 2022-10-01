// GetMarksToNodes returns an object containing a mapping from marks to node
// names.
const getMarksToNodes = (editor) => {
  const nodes = editor.schema.nodes;
  const marks = getAllMarks(editor);

  const mapping = {};
  for (let mark of marks) {
    mapping[mark] = [];
  }

  for (const [name, node] of Object.entries(nodes)) {
    // Node supports no marks
    if (node.spec.marks == "") {
      continue;
    }

    // Node supports all marks
    if (
      node.spec.marks == undefined ||
      node.spec.marks == null ||
      node.spec.marks == "_"
    ) {
      for (let mark of marks) {
        mapping[mark].push(name);
      }
      continue;
    }

    // Node supports specific marks
    for (let mark of node.spec.marks.split(" ")) {
      mapping[mark].push(name);
    }
  }

  return mapping;
};

// GetNodesToMarks returns an object containing a mapping from node names to
// mark names.
const getNodesToMarks = (editor) => {
  const nodes = editor.schema.nodes;

  const mapping = {};
  for (const [name, node] of Object.entries(nodes)) {
    // Node supports no marks
    if (node.spec.marks == "") {
      mapping[name] = [];
      continue;
    }

    // Node supports all marks
    if (
      node.spec.marks == undefined ||
      node.spec.marks == null ||
      node.spec.marks == "_"
    ) {
      mapping[name] = getAllMarks(editor);
      continue;
    }

    // Node supports specific marks
    mapping[name] = node.spec.marks.split(" ");
  }

  return mapping;
};

// GetAllMarks returns an array of all marks registered in the editor.
const getAllMarks = (editor) => {
  return Object.keys(editor.schema.marks);
};

// GetAvailableMarks returns an array of marks available for the current content
// selection.
const getAvailableMarks = (editor, state = null) => {
  if (state == null) {
    state = editor.state;
  }

  const from = state.selection.$from.pos;
  const to = state.selection.$to.pos;

  const nodesToMarks = getNodesToMarks(editor);

  let availableMarks = getAllMarks(editor);
  state.doc.nodesBetween(from, to, (node) => {
    availableMarks = availableMarks.filter((value) => {
      return nodesToMarks[node.type.name].includes(value);
    });
  });

  return availableMarks;
};

export default () => {
  return { getAllMarks, getAvailableMarks };
};
