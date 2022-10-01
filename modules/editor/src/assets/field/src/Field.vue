<script setup>
import { ref } from "vue";

import Editor from "@components/Editor.vue";

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
  name: {
    type: String,
    required: true,
  },
  settings: {
    type: Object,
    required: true,
  },
  value: {
    type: String,
    default: JSON.stringify({
      type: "doc",
      content: [
        {
          type: "paragraph",
        },
      ],
    }),
  },
});

const content = ref(JSON.parse(props.value));

// Show raw input field in debug mode
const showField = import.meta.env.DEV;
</script>

<template>
  <Editor v-model:content="content" :settings="props.settings" />

  <textarea
    :id="props.id"
    :name="props.name"
    :value="JSON.stringify(content, null, 2)"
    class="field"
    :hidden="!showField"
  />
</template>

<style lang="scss" scoped>
.field {
  width: 100%;
  height: 500px;
  margin-top: 20px;
  border: solid 1px lightgray;
}
</style>
