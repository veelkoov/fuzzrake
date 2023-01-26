<template>
  <FiltersPopUp @active-count-changed="(newCount: number) => this.activeFiltersCount = newCount"/>
  <UpdatesPopUp />
  <CardPopUp />

  <div id="data-table-content-container" style="display: none;">
    <div v-if="config.getMakerMode()" class="card border-danger mb-3">
      <div class="card-header">
        Filters disabled
      </div>
      <div class="card-body">
        <p class="card-text">All filtering has been temporarily disabled to ease searching the whole database. Use the below button to restore them.</p>
        <a :href=Static.getMainUrl() id="btn-reenable-filters" class="btn btn-light btn-outline-danger" @click=disableMakerMode>Re-enable filters</a>
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
          <input class="my-1" type="text" @input="event => search.text = event.target.value" placeholder="Search">
        </div>
      </div>

      <DataTable :search=search :columns=columns />
    </div>
  </div>
</template>

<script lang="ts">
import AgeAndSfwConfig from '../class/AgeAndSfwConfig';
import CardPopUp from './main/card/CardPopUp.vue';
import ColumnsController from './main/ColumnsController.vue';
import ColumnsManager from '../main/ColumnsManager';
import DataTable from './main/table/DataTable.vue';
import FiltersPopUp from './main/filters/FiltersPopUp.vue';
import Search from '../main/Search';
import Static from '../Static';
import UpdatesPopUp from './main/UpdatesPopUp.vue';
import {Options, Vue} from 'vue-class-component';

@Options({
  computed: {
    Static() {
      return Static;
    },
  },
  components: {
    CardPopUp,
    ColumnsController,
    DataTable,
    FiltersPopUp,
    UpdatesPopUp,
  },
})
export default class Main extends Vue {
  private readonly columns: ColumnsManager = new ColumnsManager();
  private activeFiltersCount: number = 0;
  private readonly config: AgeAndSfwConfig = AgeAndSfwConfig.getInstance();
  private search: Search = new Search();

  public created(): void {
    this.columns.load();
  }

  private disableMakerMode(): void {
    this.config.setMakerMode(false);
    this.config.save();
  }
}
</script>
