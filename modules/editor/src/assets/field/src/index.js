import { createApp } from "vue";

import Field from "@/Field.vue";

// Attach editor constructor to Craft
Craft.Editor = Garnish.Base.extend({
  init: (props) => {
    // Remove null value prop to prevent type error in the field component
    // because it expects a string
    if (!props.value) {
      delete props.value;
    }

    const app = createApp(Field, props);
    // Disable verbose warnings.
    // TODO: Remove in Vue 3.3
    app.config.unwrapInjectedRef = true;
    app.mount(`#${props.id}`);
  },
});

// In case the instantiation script is executed before the constructor has been
// registered, we dispatch this event to signal, that the editor is now ready
// to be constructed.
document.dispatchEvent(new CustomEvent("editor-loaded"));

// Enable hot module replacement
if (import.meta.hot) {
  import.meta.hot.accept();
}
