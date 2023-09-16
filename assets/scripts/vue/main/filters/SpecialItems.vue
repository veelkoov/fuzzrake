<template>
  <div v-for="item in filter.options.specialItems" :key="item.value" class="form-check form-check-inline">
    <CheckBox
      :filter="filter" :value="item.value" :count="item.count"
      :label="item.label" :label-html-prefix="getHtmlPrefix(item)"
    />
  </div>
</template>

<script lang="ts">
import CheckBox from './CheckBox.vue';
import Filter from './Filter';
import {Options, Vue} from 'vue-class-component';
import {SpecialItem} from '../../../Static';

@Options({
  components: {CheckBox},
  props: {
    filter: {type: Filter, required: true},
  }
})
export default class SpecialItems extends Vue {
  private getHtmlPrefix(item: SpecialItem): string {
    return `<i class="${item.faIcon}"></i>`;
  }
}
</script>
