<template>
  <span class="allNoneInvert" :class="this.class" aria-hidden="true">
        select: <a href="#" @click.prevent="$emit('all')">all</a>
        &bull; <a href="#" @click.prevent="$emit('none')">none</a>
        &bull; <a href="#" @click.prevent="$emit('invert')">invert</a>
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
}
</script>
