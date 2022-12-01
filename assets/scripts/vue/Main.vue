<template>
  <div class="btn-group mb-2" role="group" aria-label="Menus and legend">
    <div class="btn-group" role="group">
      <button type="button" class="btn btn-dark dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
        Columns
      </button>
      <ul class="dropdown-menu">
        <ColumnsController :columns=columns />
      </ul>
    </div>

    <button id="filtersButton" type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#filtersModal">
      Filters <span v-if="activeFiltersCount" class="badge rounded-pill text-bg-light">{{ activeFiltersCount }}</span>
    </button>
    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#legendModal">
      Legend
    </button>

    <!-- TODO: Text-based search, see CSS.searchable -->
  </div>

  <table id="artisans" class="table table-striped table-sm table-hover">
    <thead class="table-dark">
      <tr>
        <th class="name text-start">Fursuit maker /&nbsp;studio name</th>

        <th v-for="(label, name, index) of columns.columns"
            v-show="columns.isVisible(name)"
            :class="[name, index === columns.count() - 1 ? 'text-end' : 'text-center']">{{ label }}</th>
        <th class="hidden searchable">Data for searching</th>
      </tr>
    </thead>

    <tbody>
      <tr v-for="(artisan, index) of artisans" :data-index=index
          :id="artisan.makerId ? artisan.makerId : null"
          class="fursuit-maker artisan-data"
          :class="{inactive: artisan.inactiveReason}"
      >
        <td class="name" data-bs-toggle="modal" data-bs-target="#artisanDetailsModal">
          <span class="flag-icon" :class="'flag-icon-' + artisan.lcCountry"></span>

          {{ artisan.name }}

          <template v-if="artisan.inactiveReason">
            [inactive]<!-- grep-inactive-mark -->
          </template>

          <span class="text-nowrap">
            <i v-for="item in agesClasses(artisan)" class="ages" :class="item"/>

            <span v-if="artisan.isNew" class="new-artisan">
              <i class="fa-solid fa-leaf"></i> recently added
            </span>
          </span>
        </td>

        <td class="makerId" v-show="columns.isVisible('makerId')"
            data-bs-toggle="modal" data-bs-target="#artisanDetailsModal">
          {{ artisan.makerId }}
        </td>

        <td class="state" v-show="columns.isVisible('state')"
            data-bs-toggle="modal" data-bs-target="#artisanDetailsModal">
          {{ artisan.state }}
        </td>

        <td class="languages" v-show="columns.isVisible('languages')"
            data-bs-toggle="modal" data-bs-target="#artisanDetailsModal">
          {{ commaSeparated(artisan.languages) }}
        </td>

        <td class="productionModels" v-show="columns.isVisible('productionModels')"
            data-bs-toggle="modal" data-bs-target="#artisanDetailsModal">
          {{ commaSeparated(artisan.productionModels) }}
        </td>

        <td class="styles" v-show="columns.isVisible('styles')"
            data-bs-toggle="modal" data-bs-target="#artisanDetailsModal">
          {{ commaSeparatedOther(artisan.styles, artisan.otherStyles) }}
        </td>

        <td class="types" v-show="columns.isVisible('types')"
            data-bs-toggle="modal" data-bs-target="#artisanDetailsModal">
          {{ commaSeparatedOther(artisan.orderTypes, artisan.otherOrderTypes) }}
        </td>

        <td class="features" v-show="columns.isVisible('features')"
            data-bs-toggle="modal" data-bs-target="#artisanDetailsModal">
          {{ commaSeparatedOther(artisan.features, artisan.otherFeatures) }}
        </td>

        <td class="species" v-show="columns.isVisible('species')"
            data-bs-toggle="modal" data-bs-target="#artisanDetailsModal">
          <ul v-if="artisan.gotSpeciesInfo">
            <li v-for="item in artisan.speciesDoes" class="yes"><i class="fas fa-check"></i>&nbsp;{{ item }}</li>

            <li v-for="item in artisan.speciesDoesnt" class="no"><i class="fas fa-times"></i>&nbsp;{{ item }}</li>
          </ul>
        </td>

        <td class="commissions" v-show="columns.isVisible('commissions')"
            data-bs-toggle="modal" data-bs-target="#artisanDetailsModal">
          <ul v-if="artisan.isTracked">
            <li v-if="artisan.cstIssueText" class="inaccurate"><i class="far fa-question-circle"></i>&nbsp;{{ artisan.cstIssueText }}</li>

            <li v-for="item in artisan.openFor" class="yes"><i class="fas fa-check"></i>&nbsp;{{ item }}</li>

            <li v-for="item in artisan.closedFor" class="no"><i class="fas fa-times"></i>&nbsp;{{ item }}</li>
          </ul>
        </td>

        <td class="links" v-show="columns.isVisible('links')">
          <div class="btn-group artisan-links" role="group" aria-label="Links to websites">
<!--  TODO      {{! if isDevOrTestEnv() %} -->
<!--        <a class="btn btn-warning" href="{{! path('mx_artisan_edit', { id: artisan.id }) }"><i class="fas fa-edit"></i></a>-->
<!--        {% endif %}}-->

            <a v-if="artisan.fursuitReviewUrl" class="u-tbl u-fsr btn btn-secondary" :href="artisan.fursuitReviewUrl" target="_blank"><i class="fas fa-balance-scale"></i></a>

            <a class="u-tbl u-ab btn btn-secondary" :href="'https://bewares.getfursu.it/#search:' + artisan.abSearchJson" target="_blank"><i class="fa-solid fa-spell-check"></i></a>

            <a v-if="artisan.websiteUrl" class="u-tbl u-website btn btn-secondary" :href="artisan.websiteUrl" target="_blank"><i class="fas fa-link"></i></a>

            <div class="btn-group" role="group">
              <button :id="'drpdwnmn' + index.toString()" type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"></button>

              <ul class="dropdown-menu" :aria-labelledby="'drpdwnmn' + index.toString()">
                <li>
                  <a class="u-tbl u-ab dropdown-item" :href="'https://bewares.getfursu.it/#search:' + artisan.abSearchJson" target="_blank">
                    <i class="fa-solid fa-spell-check"></i> Check on Artists Beware
                  </a>
                </li>

                <Link :url=artisan.fursuitReviewUrl iconClass="fas fa-balance-scale" label="FursuitReview"/>
                <Link :url=artisan.websiteUrl iconClass="fas fa-link" label="Official website"/>
                <Link v-for="item in artisan.pricesUrls" :url=item iconClass="fas fa-dollar-sign" label="Prices"/>
                <Link :url=artisan.faqUrl iconClass="fas fa-comments" label="FAQ"/>
                <Link :url=artisan.queueUrl iconClass="fas fa-clipboard-list" label="Queue"/>
                <Link :url=artisan.furAffinityUrl iconClass="fas fa-image" label="FurAffinity"/>
                <Link :url=artisan.deviantArtUrl iconClass="fab fa-deviantart" label="DeviantArt"/>
                <Link :url=artisan.twitterUrl iconClass="fab fa-twitter" label="Twitter"/>
                <Link :url=artisan.facebookUrl iconClass="fab fa-facebook" label="Facebook"/>
                <Link :url=artisan.tumblrUrl iconClass="fab fa-tumblr" label="Tumblr"/>
                <Link :url=artisan.youtubeUrl iconClass="fab fa-youtube" label="YouTube"/>
                <Link :url=artisan.instagramUrl iconClass="fab fa-instagram" label="Instagram"/>
                <Link :url=artisan.etsyUrl iconClass="fab fa-etsy" label="Etsy"/>
                <Link :url=artisan.theDealersDenUrl iconClass="fas fa-shopping-cart" label="The Dealers Den"/>
                <Link :url=artisan.otherShopUrl iconClass="fas fa-shopping-cart" label="On-line shop"/>
                <Link :url=artisan.furryAminoUrl iconClass="fas fa-paw" label="Furry Amino"/>
                <Link :url=artisan.scritchUrl iconClass="fas fa-camera" label="Scritch"/>
                <Link :url=artisan.furtrackUrl iconClass="fas fa-camera" label="Furtrack"/>
                <Link :url=artisan.linklistUrl iconClass="fas fa-link" label="List of links"/>

                <li>
                  <a class="u-tbl u-report dropdown-item" data-bs-toggle="modal" data-bs-target="#artisanUpdatesModal">
                    <i class="fas fa-exclamation-triangle"></i> Data outdated/inaccurate?
                  </a>
                </li>
              </ul>
            </div>
          </div>
        </td>

        <td class="hidden">
          {{ artisan.formerly }}
          {{ artisan.formerMakerIds }}
        </td>
      </tr>
    </tbody>
  </table>
</template>

<script lang="ts">
import Artisan from '../class/Artisan';
import ColumnsController from './ColumnsController.vue';
import ColumnsManager from '../main/ColumnsManager';
import Link from './Link.vue';
import {ADULTS, MINORS, MIXED} from '../consts';
import {artisanFromArray} from '../main/utils';
import {DataRow} from '../main/DataManager';
import {getMessageBus} from '../main/MessageBus';
import {Options, Vue} from 'vue-class-component';

const messageBus = getMessageBus();

@Options({
  components: {
    ColumnsController,
    Link,
  },
})
export default class Main extends Vue {
  private readonly columns: ColumnsManager;
  private artisans: Artisan[] = [];
  private activeFiltersCount: number = 0;

  constructor(...args: any[]) {
    super(...args);

    this.columns = new ColumnsManager();
    this.columns.load();

    messageBus.listenDataChanges((newData: DataRow[]) => this.artisans = newData.map(item => artisanFromArray(item)));
    messageBus.listenQueryUpdate((_: string, newCount: number) => this.activeFiltersCount = newCount);
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

  private agesClasses(artisan: Artisan): string[] {
      switch (artisan.ages) {
        case MINORS:
          return ['fa-solid fa-user-minus'];
        case MIXED:
          return ['fa-solid fa-user-plus', 'fa-solid fa-user-minus'];
        case ADULTS:
          return [];
      }

      if (true === artisan.isMinor) {
        return ['fa-solid fa-user-minus'];
      } else if (false === artisan.isMinor) {
        return [];
      }

      return ['fa-solid fa-user'];
  }
}
</script>
