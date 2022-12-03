<template>
  <UpdatesPopUp />
  <ArtisanCardPopUp />

  <div id="data-table-content-container" style="display: none;">
    <div v-if="config.getMakerMode()" class="card border-danger mb-3">
      <div class="card-header">
        Filters disabled
      </div>
      <div class="card-body">
        <p class="card-text">All filtering has been temporarily disabled to ease searching the whole database. Use the below button to restore them.</p>
        <a :href=DataBridge.getMainUrl() id="btn-reenable-filters" class="btn btn-light btn-outline-danger" @click=disableMakerMode>Re-enable filters</a>
      </div>
    </div>

    <div id="data-table-container" style="display: none;">
      <div class="row">
        <div class="col-md-6">
          <div class="btn-group mb-2" role="group" aria-label="Menus and legend">
            <div class="btn-group" role="group">
              <button type="button" class="btn btn-dark dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                Columns
              </button>
              <ul class="dropdown-menu">
                <ColumnsController :columns=columns />
              </ul>
            </div>

            <button id="filtersButton" type="button" class="btn btn-success text-nowrap" data-bs-toggle="modal" data-bs-target="#filtersModal">
              Filters <span v-if="activeFiltersCount" class="badge rounded-pill text-bg-light">{{ activeFiltersCount }}</span>
            </button>
            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#legendModal">
              Legend
            </button>
          </div>
        </div>

        <div class="col-md-6 text-md-end">
          <input class="my-1" type="text" @input="event => searchTrimmedLc = event.target.value.trim().toLowerCase()" />
        </div>
      </div>

      <Table :searchTrimmedLc=searchTrimmedLc :columns=columns />
    </div>
  </div>
</template>

<script lang="ts">
import AgeAndSfwConfig from '../class/AgeAndSfwConfig';
import ArtisanCardPopUp from './ArtisanCardPopUp.vue';
import ColumnsController from './ColumnsController.vue';
import ColumnsManager from '../main/ColumnsManager';
import DataBridge from '../data/DataBridge';
import Table from './Table.vue';
import UpdatesPopUp from './UpdatesPopUp.vue';
import {getMessageBus} from '../main/MessageBus';
import {Options, Vue} from 'vue-class-component';

@Options({
  computed: {
    DataBridge() {
      return DataBridge
    }
  },
  components: {
    ArtisanCardPopUp,
    UpdatesPopUp,
    ColumnsController,
    Table,
  },
})
export default class Main extends Vue {
  private readonly columns: ColumnsManager;
  private activeFiltersCount: number = 0;
  private readonly config: AgeAndSfwConfig;
  private searchTrimmedLc: string = '';

  constructor(...args: any[]) {
    super(...args);

    this.config = AgeAndSfwConfig.getInstance();
    this.columns = new ColumnsManager();
    this.columns.load();

    getMessageBus().listenQueryUpdates((_: string, newCount: number) => this.activeFiltersCount = newCount);
  }

  private disableMakerMode(): void {
    this.config.setMakerMode(false);
    this.config.save();
  }
}
</script>
