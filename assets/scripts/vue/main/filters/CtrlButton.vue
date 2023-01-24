<template>
  <div :id="'filter-ctrl-' + filter.groupName" class="btn-group my-1" role="group">
    <button :data-bs-target="'#filter-body-' + filter.groupName" class="btn btn-outline-secondary" data-bs-toggle="collapse">
      {{ filter.label }}: {{ filter.state.value.description }}
    </button>
    <button v-if="filter.state.value.isActive" type="button" class="filter-ctrl-remove btn btn-outline-danger" @click="filter.state.value.reset()">
      <i class="fas fa-trash-alt"></i>
    </button>
  </div>
</template>

<script lang="ts">
import Filter from '../../../main/Filter';
import {Options, Vue} from 'vue-class-component';

@Options({
  props: {
    filter: {type: Filter, required: true},
  }
})
export default class CtrlButton extends Vue {
}
</script>
