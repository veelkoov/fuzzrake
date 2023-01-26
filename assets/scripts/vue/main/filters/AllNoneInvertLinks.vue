<template>
  <span class="allNoneInvert" :class="this.class" aria-hidden="true">
        select: <a data-action="all" href="#" @click="emit">all</a>
        &bull; <a data-action="none" href="#" @click="emit">none</a>
        &bull; <a data-action="invert" href="#" @click="emit">invert</a>
    </span>
</template>

<script lang="ts">
import {Options, Vue} from 'vue-class-component';

@Options({
  props: {
    class: {type: String, required: true},
  },
  emits: ['all', 'none', 'invert']
})
export default class AllNoneInvertLinks extends Vue {
  private emit(event: Event): void {
    event.preventDefault();

    this.$emit((event.target as HTMLElement).dataset['action']);
  }
}
</script>
