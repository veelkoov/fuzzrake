<template>
  <input class="form-check-input" type="checkbox" :id="id" :name="filter.groupName + '[]'" :value="value"
         data-label="{{ label }}"/>

  <label class="form-check-label" :for="id">
    <span v-if="'' !== labelHtmlPrefix" v-html="labelHtmlPrefix"></span>
    {{ label }}
    <span v-if="'' !== labelHtmlSuffix" v-html="labelHtmlSuffix"></span>

    <span v-if="null !== count" class="count">({{ count }})</span>
  </label>
</template>

<script lang="ts">
import Filter from '../../../main/Filter';
import getUniqueInt from '../../../class/Counter';
import {Options, Vue} from 'vue-class-component';

@Options({
  props: {
    count: {type: Number, required: false}, // FIXME: Nullable
    filter: {type: Filter, required: true},
    label: {type: String, required: true},
    labelHtmlPrefix: {type: String, required: false},
    labelHtmlSuffix: {type: String, required: false},
    value: {type: String, required: true},
  }
})
export default class CheckBox extends Vue {
  private id: string = 'checkbox' + getUniqueInt();
}
</script>
