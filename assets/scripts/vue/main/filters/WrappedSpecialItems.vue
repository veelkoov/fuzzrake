<template>
  <WrappedCheckBox v-for="item in filter.options.specialItems" :filter="filter" :value="item.value" :count="item.count"
                   :label="item.label" :label-html-prefix="getHtmlPrefix(item)"/>
</template>

<script lang="ts">
import Filter from '../../../main/Filter';
import WrappedCheckBox from './WrappedCheckBox.vue';
import {Options, Vue} from 'vue-class-component';
import {SpecialItem} from '../../../Static';

@Options({
  components: {WrappedCheckBox},
  props: {
    filter: {type: Filter, required: true},
  }
})
export default class WrappedSpecialItems extends Vue {
  private getHtmlPrefix(item: SpecialItem): string {
    return `<i class="${item.faIcon}"></i>`;
  }
}
</script>
