<template>
  <template v-if="0 !== items.length">
    {{ items.join(', ') }}
  </template>

  <UnknownValue v-else />
</template>

<script lang="ts">
import UnknownValue from './UnknownValue.vue';
import {Options, Vue} from 'vue-class-component';
import {PropType} from 'vue';

@Options({
  components: {UnknownValue},
  props: {
    items: {
      type: Object as PropType<string[]>,
      required: true,
    },
  },
})
export default class Optional extends Vue {
}
</script>
