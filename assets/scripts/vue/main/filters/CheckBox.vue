<template>
  <input class="form-check-input" type="checkbox" :id="id" :name="groupName + '[]'" :value="value"
         data-label="{{ label }}"/>

  <label class="form-check-label" :for="id">
    <span v-if="'' !== labelHtmlPrefix" v-html="labelHtmlPrefix"></span>
    {{ label }}
    <span v-if="'' !== labelHtmlSuffix" v-html="labelHtmlSuffix"></span>

    <span v-if="null !== count" class="count">({{ count }})</span>
  </label>
</template>

<script lang="ts">
import FilterState from '../../../main/FilterState';
import getUniqueInt from '../../../class/Counter';
import {Options, Vue} from 'vue-class-component';

@Options({
  props: {
    count: {type: Number, required: true}, // FIXME: Nullable
    groupName: {type: String, required: true},
    label: {type: String, required: true},
    labelHtmlPrefix: {type: String, required: false},
    labelHtmlSuffix: {type: String, required: false},
    state: {type: FilterState, required: true},
    value: {type: String, required: true},
  }
})
export default class CheckBox extends Vue {
  private id: string = 'checkbox' + getUniqueInt();
}
</script>
