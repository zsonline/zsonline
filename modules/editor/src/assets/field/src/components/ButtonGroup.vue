<script setup>
import { computed } from "vue";

const props = defineProps({
  label: {
    type: String,
    default: "",
  },
});

const hasLabel = computed(() => {
  return !!props.label;
});
</script>

<template>
  <div class="button-group">
    <span v-if="hasLabel" class="label">{{ props.label }}:</span>
    <div class="buttons">
      <slot />
    </div>
  </div>
</template>

<style lang="scss" scoped>
.button-group {
  display: flex;
  flex-direction: row;
  gap: 0.75rem;
  align-items: center;

  .label {
    font-size: 0.75rem;
  }

  .buttons {
    display: flex;
    flex-direction: row;
    gap: 0.5rem;
    align-items: center;
  }
}
</style>
