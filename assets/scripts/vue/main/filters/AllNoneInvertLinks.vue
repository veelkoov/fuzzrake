<script lang="ts">
export default {
  props: {
    class: {type: String, required: true},
  },
  emits: ['all', 'none', 'invert'],
}
</script>

<template>
  <span
    class="allNoneInvert"
    :class="this.class"
    aria-hidden="true"
  >
    select: <a
      href="#"
      @click.prevent="$emit('all')"
    >all</a>

    &bull; <a
      href="#"
      @click.prevent="$emit('none')"
    >none</a>-

    &bull; <a
      href="#"
      @click.prevent="$emit('invert')"
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
