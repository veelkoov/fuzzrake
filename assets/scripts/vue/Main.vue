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
      <!-- Caption is dynamic -->
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
<!--   TODO         {{ describeAgesShort artisan }}-->

            <template v-if="artisan.isNew">
              <span class="new-artisan"><i class="fa-solid fa-leaf"></i> recently added</span>
            </template>
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
              <button :id="'drpdwnmn' + index " type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"></button>

              <ul class="dropdown-menu" :aria-labelledby="'drpdwnmn' + index ">
                <li>
                  <a class="u-tbl u-ab dropdown-item" :href="'https://bewares.getfursu.it/#search:' + artisan.abSearchJson" target="_blank">
                    <i class="fa-solid fa-spell-check"></i> Check on Artists Beware
                  </a>
                </li>

                <li v-if="artisan.fursuitReviewUrl">
                  <a class="u-tbl u-fsr dropdown-item" :href="artisan.fursuitReviewUrl" target="_blank">
                    <i class="fas fa-balance-scale"></i> FursuitReview
                  </a>
                </li>

                <li v-if="artisan.websiteUrl">
                  <a class="u-tbl u-website dropdown-item" :href="artisan.websiteUrl" target="_blank">
                    <i class="fas fa-link"></i> Official website
                  </a>
                </li>

                <li v-for="item in artisan.pricesUrls">
                  <a class="u-tbl u-prices dropdown-item" :href="item" target="_blank">
                    <i class="fas fa-dollar-sign"></i> Prices
                  </a>
                </li>

                <li v-if="artisan.faqUrl">
                  <a class="u-tbl u-faq dropdown-item" :href="artisan.faqUrl" target="_blank">
                    <i class="fas fa-comments"></i> FAQ
                  </a>
                </li>

                <li v-if="artisan.queueUrl">
                  <a class="u-tbl u-queue dropdown-item" :href="artisan.queueUrl" target="_blank">
                    <i class="fas fa-clipboard-list"></i> Queue
                  </a>
                </li>

                <li v-if="artisan.furAffinityUrl">
                  <a class="u-tbl u-fa dropdown-item" :href="artisan.furAffinityUrl" target="_blank">
                    <i class="fas fa-image"></i> FurAffinity
                  </a>
                </li>

                <li v-if="artisan.deviantArtUrl">
                  <a class="u-tbl u-da dropdown-item" :href="artisan.deviantArtUrl" target="_blank">
                    <i class="fab fa-deviantart"></i> DeviantArt
                  </a>
                </li>

                <li v-if="artisan.twitterUrl">
                  <a class="u-tbl u-twitter dropdown-item" :href="artisan.twitterUrl" target="_blank">
                    <i class="fab fa-twitter"></i> Twitter
                  </a>
                </li>

                <li v-if="artisan.facebookUrl">
                  <a class="u-tbl u-facebook dropdown-item" :href="artisan.facebookUrl" target="_blank">
                    <i class="fab fa-facebook"></i> Facebook
                  </a>
                </li>

                <li v-if="artisan.tumblrUrl">
                  <a class="u-tbl u-tumblr dropdown-item" :href="artisan.tumblrUrl" target="_blank">
                    <i class="fab fa-tumblr"></i> Tumblr
                  </a>
                </li>

                <li v-if="artisan.youtubeUrl">
                  <a class="u-tbl u-youtube dropdown-item" :href="artisan.youtubeUrl" target="_blank">
                    <i class="fab fa-youtube"></i> YouTube
                  </a>
                </li>

                <li v-if="artisan.instagramUrl">
                  <a class="u-tbl u-instagram dropdown-item" :href="artisan.instagramUrl" target="_blank">
                    <i class="fab fa-instagram"></i> Instagram
                  </a>
                </li>

                <li v-if="artisan.etsyUrl">
                  <a class="u-tbl u-etsy dropdown-item" :href="artisan.etsyUrl" target="_blank">
                    <i class="fab fa-etsy"></i> Etsy
                  </a>
                </li>

                <li v-if="artisan.theDealersDenUrl">
                  <a class="u-tbl u-tdd dropdown-item" :href="artisan.theDealersDenUrl" target="_blank">
                    <i class="fas fa-shopping-cart"></i> The Dealers Den
                  </a>
                </li>

                <li v-if="artisan.otherShopUrl">
                  <a class="u-tbl u-shop dropdown-item" :href="artisan.otherShopUrl" target="_blank">
                    <i class="fas fa-shopping-cart"></i> On-line shop
                  </a>
                </li>

                <li v-if="artisan.furryAminoUrl">
                  <a class="u-tbl u-amino dropdown-item" :href="artisan.furryAminoUrl" target="_blank">
                    <i class="fas fa-paw"></i> Furry Amino
                  </a>
                </li>

                <li v-if="artisan.scritchUrl">
                  <a class="u-tbl u-scritch dropdown-item" :href="artisan.scritchUrl" target="_blank">
                    <i class="fas fa-camera"></i> Scritch
                  </a>
                </li>

                <li v-if="artisan.furtrackUrl">
                  <a class="u-tbl u-furtrack dropdown-item" :href="artisan.furtrackUrl" target="_blank">
                    <i class="fas fa-camera"></i> Furtrack
                  </a>
                </li>

                <li v-if="artisan.linklistUrl">
                  <a class="u-tbl u-links dropdown-item" :href="artisan.linklistUrl" target="_blank">
                    <i class="fas fa-link"></i> List of links
                  </a>
                </li>

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
import { Options, Vue } from 'vue-class-component';
import ColumnsController from './ColumnsController.vue';
import ColumnsManager from '../main/ColumnsManager';
import Artisan from '../class/Artisan';
import {DataRow} from '../main/DataManager';
import {getMessageBus} from '../main/MessageBus';
import {artisanFromArray} from "../main/utils";

const messageBus = getMessageBus();

@Options({
  components: {
    ColumnsController,
  },
})
export default class Main extends Vue {
  private readonly columns: ColumnsManager;
  private artisans: Artisan[] = [];

  constructor(...args: any[]) {
    super(...args);

    this.columns = new ColumnsManager();
    this.columns.load();

    messageBus.listenDataChanges((newData: DataRow[]) => this.updateWith(newData));
  }

  private updateWith(data: DataRow[]): void {
    let newArtisans = [];

    data.forEach((item) => newArtisans.push(artisanFromArray(item)));

    this.artisans = newArtisans;
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
}
</script>
