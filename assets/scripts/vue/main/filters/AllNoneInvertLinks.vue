<script lang="ts">
import CheckBox from './CheckBox.vue';
import {defineComponent} from 'vue';

export default defineComponent({
  props: {
    class: {type: String, required: true},
    checkboxes: {type: Array<typeof CheckBox>, required: true},
  },

  methods: {
    withAllCheckBoxes(method: (checkbox: typeof CheckBox) => void): void {
      this.checkboxes.forEach((item) => method(item));
    },
  }
})
</script>

<template>
  <span
    class="allNoneInvert"
    :class="this.class"
    aria-hidden="true"
  >
    select: <a
      href="#"
      @click.prevent="withAllCheckBoxes(it => it.check())"
    >all</a>

    &bull; <a
      href="#"
      @click.prevent="withAllCheckBoxes(it => it.uncheck())"
    >none</a>-

    &bull; <a
      href="#"
      @click.prevent="withAllCheckBoxes(it => it.invert())"
    >invert</a>
  </span>
</template>

<style scoped lang="scss">
.allNoneInvert {
  font-size: 90%;
  font-weight: normal;
  color: #aaa;
}

.allNoneInvert.countries {
  padding-left: 1em;
}

.allNoneInvert.simple {
  float: right;
}
</style>
