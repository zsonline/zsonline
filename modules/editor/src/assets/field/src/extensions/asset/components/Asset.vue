<script setup>
import { computed, ref, watch } from "vue";
import { nodeViewProps, NodeViewWrapper } from "@tiptap/vue-3";

import Button from "@components/Button.vue";
import { getParentNodeFromPos } from "@extensions/helpers.js";

const props = defineProps(nodeViewProps);

// ID gets and sets the asset ID.
const id = computed({
  get() {
    return props.node.attrs.id;
  },
  set(value) {
    props.updateAttributes({
      id: value,
    });
  },
});

// Parent returns the node type of the shallowest parent node.
const parent = computed(() => {
  const { node } = getParentNodeFromPos(
    props.editor.view.state.doc.resolve(props.getPos())
  );

  return node.type.name.replace(/^block/, "").toLowerCase();
});

// Criteria computes the asset selection criteria based on the parent node.
const criteria = computed(() => {
  switch (parent.value) {
    case "embed":
      return {
        kind: "json",
      };
    case "image":
      return {
        kind: "image",
      };
  }

  return null;
});

// Select opens a Craft CMS selection modal for letting a user select an asset.
// Once selected, the asset ID is updated.
const select = () => {
  Craft.createElementSelectorModal("craft\\elements\\Asset", {
    storageKey: `Editor.Choose${parent.value}`,
    multiSelect: false,
    sources: props.extension.options.sources[parent.value],
    criteria: criteria.value,
    onSelect: (data) => {
      if (data.length !== 1) {
        return;
      }

      id.value = data[0]?.id;
    },
  });
};

// Deselect deselects the currently selected asset.
const deselect = () => {
  id.value = null;
};

/*
 * Preview
 *
 * Craft CMS does not expose an API for fetching asset data. Instead, we use a
 * custom module endpoint for retrieving a thumbnail URL as well as the asset
 * filename and caption.
 */

// Store preview data about the asset. The data is fetched from the module
// controller endpoint.
const assetCaption = ref(null);
const assetFilename = ref(null);
const assetUrl = ref(null);

// ResetAsset resets the asset preview data.
const resetAsset = () => {
  assetCaption.value = null;
  assetFilename.value = null;
  assetUrl.value = null;
};

// FetchAsset fetches the title and preview URL for given asset ID.
const fetchAsset = async (id) => {
  let response;

  try {
    response = await Craft.sendActionRequest(
      "POST",
      "editor/field/preview-asset",
      {
        data: {
          assetId: id,
        },
      }
    );
  } catch (e) {
    resetAsset();
    return;
  }

  assetCaption.value = response?.data?.caption;
  assetFilename.value = response?.data?.filename;
  assetUrl.value = response?.data?.url;
};

// Whenever the asset ID changes, the asset is re-fetched. If the asset ID is
// null, the asset data is reset.
watch(
  id,
  async (newValue, oldValue) => {
    if (!newValue) {
      resetAsset();
      return;
    }

    if (newValue == oldValue) {
      return;
    }

    await fetchAsset(newValue);
  },
  { immediate: true }
);

/*
 * Edit
 *
 * Craft CMS has two actions registered on its asset fields. We support the same
 * behaviour. A single click opens a preview modal. A double click opens an
 * editor slideout.
 */

// Both the click event and the double click event are registered on the same
// element. To prevent the double click event from triggering two click events,
// a timeout is awaited before the single click action (preview) is executed.
// Otherwise, the double click action (edit) would never be reached.
let clickTimeout = null;

// ClearClickTimeout clears the timeout.
const clearClickTimeout = () => {
  if (clickTimeout) {
    clearTimeout(clickTimeout);
    clickTimeout = null;
  }
};

// Preview is triggered by a single click onto a selected asset. It opens the
// Craft CMS preview modal for displaying the asset in larger size. The modal
// lets a user set the focal point.
const preview = () => {
  clearClickTimeout();

  clickTimeout = setTimeout(() => {
    new Craft.PreviewFileModal(id.value);
    clickTimeout = null;
  }, 500);
};

// Edit is triggered by a double click onto a selected asset. It opens the Craft
// CMS asset element slideout, which lets a user edit asset attributes and
// modify the asset.
const edit = () => {
  clearClickTimeout();

  Craft.createElementEditor(this.elementType, {
    elementId: id.value,
  }).on("submit", async () => {
    // Re-fetch asset in case the asset has been changed
    await fetchAsset(id.value);
  });
};

/*
 * Gallery
 *
 * Asset nodes can occur within blockGallery nodes. blockGallery nodes can
 * contain multiple asset nodes. In order to support reordering, blockGallery
 * nodes require additional actions.
 */

// MoveUp switches this image with the previous asset.
const moveUp = () => {
  const positionBefore = props.editor.view.state.doc.resolve(
    props.getPos() - 1
  );

  // Check if a sibling above exists
  if (
    !positionBefore.nodeAfter ||
    positionBefore.nodeAfter.type.name !== props.node.type.name
  ) {
    return;
  }

  // Set the sibling's ID to this ID
  props.editor.commands.command(({ tr }) => {
    tr.setNodeMarkup(props.getPos() - 1, undefined, {
      id: id.value,
    });
    return true;
  });

  // Set this ID to the sibling's ID
  id.value = positionBefore.nodeAfter.attrs.id;
};

// MoveDown switches this image with the next asset.
const moveDown = () => {
  const positionAfter = props.editor.view.state.doc.resolve(props.getPos() + 1);
  // Check if a sibling below exists
  if (
    !positionAfter.nodeAfter ||
    positionAfter.nodeAfter.type.name !== props.node.type.name
  ) {
    return;
  }

  // Set the sibling's ID to this ID
  props.editor.commands.command(({ tr }) => {
    tr.setNodeMarkup(props.getPos() + 1, undefined, {
      id: id.value,
    });
    return true;
  });

  // Set this ID to the sibling's ID
  id.value = positionAfter.nodeAfter.attrs.id;
};
</script>

<template>
  <NodeViewWrapper>
    <div class="wrapper">
      <figure class="asset">
        <div class="placeholder">
          <Button v-if="!id" :action="select" label="Select" />
          <div v-else class="image" @click="preview" @dblclick="edit">
            <img v-if="assetUrl" :src="assetUrl" />
          </div>
        </div>

        <figcaption v-if="assetCaption || assetFilename">
          <div v-if="assetCaption" class="caption">{{ assetCaption }}</div>
          <div v-if="assetFilename" class="filename">{{ assetFilename }}</div>
        </figcaption>
      </figure>

      <div class="controls">
        <Button
          v-if="parent === 'gallery'"
          tooltip="Move Up"
          icon="up"
          :action="moveUp"
          size="small"
        />
        <Button
          v-if="parent === 'gallery'"
          tooltip="Move Down"
          icon="down"
          :action="moveDown"
          size="small"
        />
        <Button
          tooltip="Delete"
          icon="delete"
          :action="props.deleteNode"
          size="small"
        />
      </div>
    </div>
  </NodeViewWrapper>
</template>

<style lang="scss" scoped>
.wrapper {
  display: flex;
  justify-content: space-between;
  gap: 2em;
}

.asset {
  display: flex;
  flex-direction: column;
  gap: 0.5em;

  figcaption {
    font-size: 0.9em;
  }
}

.placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 150px;
  height: 150px;
  background-color: var(--asset-background);

  .image {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    cursor: pointer;
  }

  img {
    width: 150px;
    height: 150px;
    object-fit: contain;
  }
}

.caption {
  font-family: sans-serif;
  color: var(--asset-foreground);
}

.filename {
  font-family: monospace;
  font-size: 0.9em;
  color: var(--asset-foreground-subdued);
}

.controls {
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  gap: 0.5em;
}
</style>
