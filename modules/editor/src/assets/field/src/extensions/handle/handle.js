import { createApp } from "vue";
import { Extension } from "@tiptap/core";
import { NodeSelection, Plugin } from "prosemirror-state";

import { getParentNodeFromPos } from "@extensions/helpers.js";

import HandleComponent from "@extensions/handle/components/Handle.vue";

// GetParentNode returns the shallowest parent node of a DOM node within the
// editor.
const getParentNode = (node) => {
  while (node && node.parentNode) {
    if (
      node.classList?.contains("ProseMirror") ||
      node.parentNode.classList?.contains("ProseMirror")
    ) {
      break;
    }
    node = node.parentNode;
  }

  return node;
};

// Rectangle represents the bounding box of a node. It takes a DOM node as input
// and computes the bounding rectangle for that node.
class Rectangle {
  constructor(node, debug = false) {
    this.width = 0;
    this.height = 0;
    this.top = 0;
    this.left = 0;

    this.update(node);

    if (debug) {
      this.drawOverlay();
    }
  }

  // Update computes the bounding box for the given node and stores its
  // coordinates as attributes.
  update(node) {
    const rect = node.getBoundingClientRect();
    const editorRect = document
      .getElementById("editor")
      .getBoundingClientRect();
    if (!rect || !editorRect) {
      return;
    }

    this.width = rect.width;
    this.height = rect.height;
    this.top = rect.top - editorRect.top;
    this.left = rect.left - editorRect.left;
  }

  // IsInside returns true if this rectangle starts within the other rectangle.
  isInside(other) {
    return other.top <= this.top && other.left <= this.left;
  }

  // DrawOverlay helps debugging by drawing a rectangle around the currently
  // hovered node.
  drawOverlay() {
    const overlay = document.createElement("div");

    overlay.style.position = "absolute";
    overlay.style.width = this.width + "px";
    overlay.style.height = this.height + "px";
    overlay.style.top = this.top + "px";
    overlay.style.left = this.left + "px";

    overlay.style.backgroundColor = "red";
    overlay.style.opacity = "25%";

    document.getElementById("editor").appendChild(overlay);

    setTimeout(() => {
      document.getElementById("editor").removeChild(overlay);
    }, 1000);
  }
}

// Handle represents the common functionality between all handles. It is
// extended by the specific handle subclasses.
class Handle {
  constructor(editor, offset) {
    this.offset = offset;
    this.editor = editor;
    this.pos = null;

    // Wrapper acts as an outer DOM element for holding the Vue component and
    // holds the drag event listener (see Handle.bind())
    this.wrapper = document.createElement("div");
    this.wrapper.setAttribute("draggable", "true");
    this.wrapper.style.position = "absolute";
    this.bind(editor.view);

    // App stores the Vue application, containing the actions menu
    this.app = null;
    // IsActive describes whether the action menu is open
    this.isActive = false;
  }

  // Add adds the handle to the DOM.
  add(dom) {
    dom.appendChild(this.wrapper);

    this.hide();
  }

  // Remove removes the handle from the DOM.
  remove() {
    this.unmount();

    if (!this.wrapper || !this.wrapper.parentNode) {
      return;
    }

    this.wrapper.parentNode.removeChild(this.wrapper);

    this.wrapper = null;
  }

  // Mount mounts the Vue component to the handle DOM element.
  mount() {
    if (this.app) {
      return;
    }

    this.app = createApp(HandleComponent, {
      getPos: () => this.pos,
      editor: this.editor,
      onActivate: () => (this.isActive = true),
      onDeactivate: () => (this.isActive = false),
      onHide: () => {
        this.isActive = false;
        this.hide();
      },
    });
    this.app.mount(this.wrapper);
  }

  // Unmount unmounts the Vue component from the handle DOM element and resets
  // the component attributes.
  unmount() {
    if (!this.app) {
      return;
    }

    this.app.unmount();
    this.app = null;
    this.isActive = false;
  }

  // Bind adds an event listener that manages the drag operation to the wrapper.
  bind(view) {
    // On mousedown, select the corresponding node
    this.wrapper.addEventListener("mousedown", (e) => {
      // Convert the current cursor coordinates to the corresponding position
      // within the editor
      const coords = {
        left: e.clientX + this.offset,
        top: e.clientY,
      };
      const position = view.posAtCoords(coords);
      if (!position) {
        return;
      }

      // Retrieve the position of the shallowest node
      this.pos = view.state.doc.resolve(position.pos);
      const { before } = getParentNodeFromPos(this.pos);

      // Select the node at the retrieved position
      view.dispatch(
        view.state.tr.setSelection(NodeSelection.create(view.state.doc, before))
      );
    });

    // On dragstart, insert into dragging state
    this.wrapper.addEventListener("dragstart", () => {
      // Set the selected nodes to be moved (not copied)
      view.dragging = {
        slice: view.state.selection.content(),
        move: true,
      };

      this.hide();
    });
  }

  // Update updates the position of the handle.
  update(x, y) {
    this.wrapper.style.left = x - this.offset + "px";
    this.wrapper.style.top = y + "px";
  }

  // Show shows the handle.
  show() {
    this.mount();
    this.wrapper.style.visibility = "visible";
  }

  // Hide hides the handle.
  hide() {
    this.wrapper.style.visibility = "hidden";
    // Unmounting the component each time the handle is hidden is necessary for
    // resetting its state
    this.unmount();
  }
}

const HandleExtension = Extension.create({
  name: "handle",

  addProseMirrorPlugins() {
    let handle = null;
    const offset = 40;

    return [
      new Plugin({
        // Create new handle objects and initialise the event listener.
        view: () => {
          handle = new Handle(this.editor, offset);
          handle.add(document.getElementById("editor"));

          return {
            destroy() {
              handle.remove();
              handle = null;
            },
          };
        },
        props: {
          handleDOMEvents: {
            click() {
              handle.hide();
            },
            input() {
              handle.hide();
            },
            // On mousemove, compute the new position of the handles.
            mousemove(view, event) {
              // If handle menu is open, do not change position
              if (handle.isActive) {
                return;
              }

              // Get current mouse position
              const position = view.posAtCoords({
                left: event.clientX + offset,
                top: event.clientY,
              });
              if (!position) {
                handle.hide();
                return;
              }

              // Get the node at the current mouse position
              const node = getParentNode(view.nodeDOM(position.inside));
              if (!node || node.classList.contains("ProseMirror")) {
                handle.hide();
                return;
              }

              // Check if rect is within editor
              const rect = new Rectangle(node);
              const prosemirrorRect = new Rectangle(view.dom);
              if (!rect.isInside(prosemirrorRect)) {
                handle.hide();
                return;
              }

              // Update dragHandle and deleteHandle positions and show both
              // handles
              handle.update(rect.left, rect.top);
              handle.show();
            },
          },
        },
      }),
    ];
  },
});

export default HandleExtension;
