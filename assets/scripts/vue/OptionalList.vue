<template>
  <span v-if="comment" class="nl2br">
    {{ comment }}
  </span>

  <template v-if="items.length || otherItems.length">
    <ul>
      <li v-for="item in items">{{ item }}</li>

      <li v-if="otherItems.length">{{ otherItems.join('; ') }}</li>
    </ul>
  </template>

  <Unknown v-else />
</template>

<script lang="ts">
import Unknown from './Unknown.vue';
import {Options, Vue} from 'vue-class-component';
import {PropType} from 'vue';

@Options({
  components: {Unknown},
  props: {
    comment: {
      type: String,
      required: false,
      default: '',
    },
    items: {
      type: Object as PropType<string[]>,
      required: true,
    },
    otherItems: {
      type: Object as PropType<string[]>,
      required: false,
      default: [],
    },
  }
})
export default class OptionalList extends Vue {
  comment!: string;
  items!: string[];
  otherItems!: string[];
}
</script>

<style scoped>
  .nl2br {
    white-space: pre-wrap;
  }
</style>
