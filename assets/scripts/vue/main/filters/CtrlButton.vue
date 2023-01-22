<template>
  <div :id="'filter-ctrl-' + groupName" class="btn-group my-1" role="group">
    <button :data-bs-target="'#filter-body-' + groupName" class="btn btn-outline-secondary" data-bs-toggle="collapse">
      {{ label }}: {{ state.description }}
    </button>
    <button v-if="state.isActive" type="button" class="filter-ctrl-remove btn btn-outline-danger">
      <i class="fas fa-trash-alt"></i>
    </button>
  </div>
</template>

<script lang="ts">
import FilterState from '../../../main/FilterState';
import {Options, Vue} from 'vue-class-component';

@Options({
  props: {
    groupName: {type: String, required: true},
    label: {type: String, required: true},
    state: {type: FilterState, required: true},
  }
})
export default class CtrlButton extends Vue {
}
</script>
