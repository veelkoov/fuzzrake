<template>
<span></span>
</template>

<script lang="ts">
import BodyContainer from './BodyContainer.vue';
import CtrlButton from './CtrlButton.vue';
import Filter from './Filter';
import MainState from '../MainState';
import Static from '../../../Static';
import {getMessageBus} from '../../../main/MessageBus';
import {Options, Vue} from 'vue-class-component';

@Options({
  components: {BodyContainer, CtrlButton},
  props: {
    state: {type: MainState, required: true},
  }
})
export default class FiltersPopUp extends Vue {
  private state!: MainState;
  private filters = new Array<Filter>();

  public created(): void {
    this.filters.push(...[
    ]);

    this.filters.forEach(filter => filter.restoreChoices());
  }

  public mounted(): void {
    (this.$refs['modal'] as HTMLElement).addEventListener('hidden.bs.modal', () => this.onModalClosed());

    this.updateState();
  }

  private onModalClosed(): void {
    this.filters.forEach(filter => filter.saveChoices());
    this.updateState();
    getMessageBus().requestDataLoad(this.state.query, false);
  }

  private updateState(): void {
    this.state.activeFiltersCount = this.getActiveFiltersCount();
    // FIXME: https://github.com/veelkoov/fuzzrake/pull/187/files
    // eslint-disable-next-line no-undef
    this.state.query = jQuery('#filters').serialize(); // TODO: Optimize to avoid error 413 https://github.com/veelkoov/fuzzrake/issues/185
  }

  private getActiveFiltersCount(): number {
    return this.filters.map(filter => filter.state.isActive ? 1 : 0).reduce((sum: number, val: number) => sum + val, 0);
  }
}
</script>

<style scoped lang="scss">
::v-deep(fieldset legend) {
  font-size: 1rem;
  font-weight: bold;
  margin-bottom: 0;
  padding-top: .5rem;
}

::v-deep(.count) {
  font-size: 90%;
  color: #aaa;
}
</style>
