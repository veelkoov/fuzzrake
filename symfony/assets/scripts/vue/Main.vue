<template>
  <CheckList v-if="!aasDismissed" @dismissed="onChecklistDismissal()" />
  <FiltersPopUp :state="state" />
  <UpdatesPopUp :state="state" />
  <CardPopUp :state="state" />

  <div id="data-table-content-container" :style="{'display': aasDismissed ? 'block' : 'none'}">
    <div v-if="aasConfig.getMakerMode()" class="card border-danger mb-3">
      <div class="card-header">
        Filters disabled
      </div>
      <div class="card-body">
        <p class="card-text">
          All filtering has been temporarily disabled to ease searching the whole database. Use the below button to restore them.
        </p>
        <a id="btn-reenable-filters" :href="Static.getMainPath()" class="btn btn-light btn-outline-danger" @click="disableMakerMode">Re-enable filters</a>
      </div>
    </div>

    <div id="data-table-container">
      <div class="row">
        <div class="col-md-6">
          <div class="btn-group mb-2" role="group" aria-label="Menus and legend">
            <div class="btn-group" role="group">
              <button type="button" class="btn btn-dark dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                Columns
              </button>
              <ul class="dropdown-menu">
                <ColumnsController :columns="columns" />
              </ul>
            </div>

            <button id="filtersButton" type="button" class="btn btn-success text-nowrap" data-bs-toggle="modal" data-bs-target="#filtersModal">
              Filters <span v-if="state.activeFiltersCount" class="badge rounded-pill text-bg-light">{{ state.activeFiltersCount }}</span>
            </button>
            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#legendModal">
              Legend
            </button>
          </div>
        </div>

        <div class="col-md-6 text-md-end">
          <input id="search-text-field" v-model="state.search.text" class="my-1" type="text" placeholder="Search">
        </div>
      </div>

      <DataTable :columns="columns" :state="state" />
    </div>
  </div>
</template>

<script lang="ts">
import AgeAndSfwConfig from '../class/AgeAndSfwConfig';
import CardPopUp from './main/card/CardPopUp.vue';
import CheckList from './main/CheckList.vue';
import ColumnsController from './main/ColumnsController.vue';
import ColumnsManager from './main/ColumnsManager';
import DataTable from './main/table/DataTable.vue';
import FiltersPopUp from './main/filters/FiltersPopUp.vue';
import MainState from './main/MainState';
import MessageBus, {getMessageBus} from '../main/MessageBus';
import Static from '../Static';
import UpdatesPopUp from './main/UpdatesPopUp.vue';
import {makerIdHashRegexp} from '../consts';
import {nextTick} from 'vue';
import {Options, Vue} from 'vue-class-component';

@Options({
  computed: {
    Static() {
      return Static;
    },
  },
  components: {CardPopUp, CheckList, ColumnsController, DataTable, FiltersPopUp, UpdatesPopUp},
})
export default class Main extends Vue {
  private readonly state = new MainState();
  private readonly aasConfig: AgeAndSfwConfig = AgeAndSfwConfig.getInstance();
  private readonly columns: ColumnsManager = new ColumnsManager();
  private readonly messageBus: MessageBus = getMessageBus();
  private aasDismissed: boolean = this.aasConfig.getMakerMode();

  public created(): void {
    this.columns.load();

    this.messageBus.listenSetupFinished(() => this.onSetupFinished());
  }

  private onChecklistDismissal(): void {
    this.aasDismissed = true;

    nextTick(() => { // Checklist causes the user to be at the bottom of the table when it shows up
      // FIXME: https://github.com/veelkoov/fuzzrake/pull/187/files
      // eslint-disable-next-line no-undef
      const offset = jQuery('#data-table-content-container').offset() || {'top': 5};
      window.scrollTo(0, offset.top - 5);
    });

    this.messageBus.requestDataLoad(this.state.query, false);
  }

  private disableMakerMode(): void {
    this.aasConfig.setMakerMode(false);
    this.aasConfig.save();
  }

  private onSetupFinished(): void {
    if (this.aasConfig.getMakerMode()) {
      this.messageBus.requestDataLoad('', false);
    }

    if (window.location.hash.match(makerIdHashRegexp)) {
      this.state.openCardForMakerId = window.location.hash.slice(1);

      if (!this.aasConfig.getMakerMode()) {
        this.messageBus.requestDataLoad('wantsSfw=0&isAdult=1&inactive[]=.&makerId=' + this.state.openCardForMakerId, true);
      }
    }
  }
}
</script>
