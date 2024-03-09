<template>
  <template v-if="null !== item && '' !== item">
    {{ item }}
  </template>

  <UnknownValue v-else />
</template>

<script lang="ts">
import UnknownValue from './UnknownValue.vue';
import {Options, Vue} from 'vue-class-component';

@Options({
  components: {UnknownValue},
  props: {
    item: {
      type: String,
      required: true,
    },
  },
})
export default class OptionalText extends Vue {
}
</script>
