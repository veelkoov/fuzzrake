<template>
  <span class="allNoneInvert" :class="this.class" aria-hidden="true">
        select: <a data-action="all" href="">all</a>
        &bull; <a data-action="none" href="">none</a>
        &bull; <a data-action="invert" href="">invert</a>
    </span>
</template>

<script lang="ts">
import {Options, Vue} from 'vue-class-component';

@Options({
  props: {
    class: String,
  },
})
export default class AllNoneInvertLinks extends Vue {
}
</script>
