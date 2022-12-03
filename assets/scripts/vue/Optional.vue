<template>
  <template v-if="0 !== items.length">
    {{ items.join(', ') }}
  </template>

  <template v-else>
    <Unknown />
  </template>
</template>

<script lang="ts">
import Unknown from './Unknown.vue';
import {Options, Vue} from 'vue-class-component';
import {PropType} from 'vue';

@Options({
  components: {Unknown},
  props: {
    items: {
      type: Object as PropType<string[]>,
      required: true,
    },
  },
})
export default class Optional extends Vue {
  private items!: string[];
}
</script>
