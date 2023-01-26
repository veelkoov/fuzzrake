<template>
  <input class="form-check-input" type="checkbox" :id="id" :name="filter.groupName + '[]'" :data-label="label"
         :value="value" @input="changed" :checked="isChecked"/>

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
import {AnyOptions} from '../../../Static';
import {Options, Vue} from 'vue-class-component';

@Options({
  props: {
    count: {type: Number, required: false}, // TODO: #76 Species count, should not be nullable
    filter: {type: Filter, required: true},
    label: {type: String, required: true},
    labelHtmlPrefix: {type: String, required: false},
    labelHtmlSuffix: {type: String, required: false},
    value: {type: String, required: true},
  },
})
export default class CheckBox extends Vue {
  private id: string = 'checkbox' + getUniqueInt();
  private filter!: Filter<AnyOptions>;
  private label!: string;
  private value!: string;

  private changed(event: Event): void {
    const checkbox = event.target as HTMLInputElement;

    this.filter.state.value.set(this.value, this.label, checkbox.checked);
  }

  get isChecked(): boolean {
    return this.filter.state.value.get(this.value);
  }
}
</script>
