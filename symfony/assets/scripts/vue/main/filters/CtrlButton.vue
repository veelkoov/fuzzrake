<template>
  <div :id="'filter-ctrl-' + filter.groupName" class="btn-group my-1" role="group">
    <button :data-bs-target="'#filter-body-' + filter.groupName" class="btn btn-outline-secondary" data-bs-toggle="collapse">
      {{ filter.label }}: {{ filter.state.description }}
    </button>
    <button v-if="filter.state.isActive" type="button" class="filter-ctrl-remove btn btn-outline-danger" @click="filter.state.reset()">
      <i class="fas fa-trash-alt" />
    </button>
  </div>
</template>

<script lang="ts">
import Filter from './Filter';
import {Options, Vue} from 'vue-class-component';

@Options({
  props: {
    filter: {type: Filter, required: true},
  }
})
export default class CtrlButton extends Vue {
}
</script>
