<template>
<span></span>
</template>

<script lang="ts">
import AgesDescription from '../AgesDescription.vue';
import Artisan from '../../../class/Artisan';
import ColumnsManager from '../ColumnsManager';
import MainState from '../MainState';
import MessageBus, {getMessageBus} from '../../../main/MessageBus';
import Static from '../../../Static';
import TblLink from './TblLink.vue';
import {DataRow} from '../../../main/DataManager';
import {nextTick} from 'vue';
import {Options, Vue} from 'vue-class-component';

@Options({
  computed: {
    Static() {
      return Static;
    }
  },
  components: {
    AgesDescription, TblLink,
  },
  props: {
    columns: {type: ColumnsManager, required: true},
    state: {type: MainState, required: true},
  },
})
export default class Table extends Vue {
  private state!: MainState;
  private artisans: Artisan[] = [];
  private messageBus: MessageBus = getMessageBus();

  public created(): void {
    this.messageBus.listenDataChanges(newData => this.onDataChanged(newData));
  }

  private matchesText(artisan: Artisan): boolean {
    if ('' === this.state.search.textLc) {
      return true;
    }

    return artisan.searchableText.includes(this.state.search.textLc);
  }

  private matchedMakerId(artisan: Artisan): boolean {
    return this.state.search.isMakerId && artisan.hasMakerId(this.state.search.textUc);
  }

  private setSubject(newSubject: Artisan): void {
    this.state.subjectArtisan = newSubject;
  }

  private commaSeparated(list: string[]): string {
    return list.join(', ');
  }

  private commaSeparatedOther(list: string[], other: string[]): string {
    if (0 !== other.length) {
      list = list.concat(['Other'])
    }

    return list.join(', ').replace(/ \([^)]+\)/g, ''); // FIXME: #171 Glossary
  }

  private isDevEnv(): boolean {
    return 'dev' === Static.getEnvironment();
  }

  private onDataChanged(newData: DataRow[]): void {
    this.artisans = newData.map(item => Artisan.fromArray(item));

    this.handleCardOpeningOnMakerIdInUrlsHash();

    Static.hideLoadingIndicator();
  }

  private handleCardOpeningOnMakerIdInUrlsHash() {
    if ('' === this.state.openCardForMakerId) {
      return;
    }

    const makerId = this.state.openCardForMakerId;
    this.state.openCardForMakerId = '';

    if (1 !== this.artisans.length || !this.artisans[0].hasMakerId(makerId)) {
      console.log(`Failed opening card for ${this.state.openCardForMakerId}, loaded ${this.artisans.length} records`);
    } else {
      // FIXME: https://github.com/veelkoov/fuzzrake/pull/187/files
      // eslint-disable-next-line no-undef
      nextTick(() => jQuery('#artisans tbody tr:first-child td:first-child').trigger('click'));
    }
  }
}
</script>
