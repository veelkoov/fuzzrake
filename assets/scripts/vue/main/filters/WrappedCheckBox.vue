<template>
  <div class="form-check form-check-inline">
    <CheckBox :filter="filter" :value="value" :count="count" :label-html-prefix="labelHtmlPrefix" :label="label"/>
  </div>
</template>

<script lang="ts">
import CheckBox from './CheckBox.vue';
import Filter from '../../../main/Filter';
import {Options, Vue} from 'vue-class-component';

@Options({
  components: {CheckBox},
  props: {
    count: {type: Number, required: false}, // FIXME: Nullable
    filter: {type: Filter, required: true},
    label: {type: String, required: true},
    labelHtmlPrefix: {type: String, required: false},
    value: {type: String, required: true},
  }
})
export default class WrappedCheckBox extends Vue {
}
</script>
