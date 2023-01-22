<template>
  <WrappedCheckBox v-for="item in items" :group-name="groupName" :value="item.value" :count="item.count"
                   :label="item.label" :label-html-prefix="getHtmlPrefix(item)" :state="state"/>
</template>

<script lang="ts">
import WrappedCheckBox from './WrappedCheckBox.vue';
import {Options, Vue} from 'vue-class-component';
import {PropType} from 'vue';
import {SpecialItem, SpecialItems} from '../../../Static';
import FilterState from '../../../main/FilterState';

@Options({
  components: {WrappedCheckBox},
  props: {
    groupName: {type: String, required: true},
    items: {type: Object as PropType<SpecialItems>, required: true},
    state: {type: FilterState, required: true},
  }
})
export default class WrappedSpecialItems extends Vue {
  private getHtmlPrefix(item: SpecialItem): string {
    return `<i class="${item.faIcon}"></i>`;
  }
}
</script>
