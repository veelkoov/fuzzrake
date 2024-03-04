<template>
  <div id="data-table-container-closest">
    <table id="artisans" class="table table-striped table-sm table-hover">
      <thead class="table-dark">
        <tr>
          <th class="name text-start">
            Fursuit maker /&nbsp;studio name
          </th>

          <th
            v-for="(label, name, index) of columns.columns" v-show="columns.isVisible(name)" :key="name"
            :class="[name, index === columns.count() - 1 ? 'text-end' : 'text-center']"
          >
            {{ label }}
          </th>
        </tr>
      </thead>

      <tbody>
        <template v-for="(artisan, index) of artisans" :key="artisan.lastMakerId">
          <tr
            :id="artisan.makerId ? artisan.makerId : null"
            :data-index="index"
            class="fursuit-maker artisan-data"
            :class="{
              'hidden':           !matchesText(artisan),
              'inactive':         artisan.inactiveReason,
              'matched-maker-id': matchedMakerId(artisan),
            }"
          >
            <td class="name" data-bs-toggle="modal" data-bs-target="#artisanDetailsModal" @click="setSubject(artisan)">
              <span class="flag-icon" :class="'flag-icon-' + artisan.lcCountry" />

              {{ artisan.name }}

              <template v-if="artisan.inactiveReason">
                [hidden] <!-- grep-inactive-mark -->
              </template>

              <span class="text-nowrap">
                <AgesDescription :add-text="false" :artisan="artisan" />

                <span v-if="artisan.isNew" class="new-artisan">
                  <i class="fa-solid fa-leaf" /> recently added
                </span>
              </span>
            </td>

            <td
              v-show="columns.isVisible('makerId')" class="makerId"
              data-bs-toggle="modal" data-bs-target="#artisanDetailsModal"
              @click="setSubject(artisan)"
            >
              {{ artisan.makerId }}
            </td>

            <td
              v-show="columns.isVisible('state')" class="state"
              data-bs-toggle="modal" data-bs-target="#artisanDetailsModal"
              @click="setSubject(artisan)"
            >
              {{ artisan.state }}
            </td>

            <td
              v-show="columns.isVisible('languages')" class="languages"
              data-bs-toggle="modal" data-bs-target="#artisanDetailsModal"
              @click="setSubject(artisan)"
            >
              {{ commaSeparated(artisan.languages) }}
            </td>

            <td
              v-show="columns.isVisible('productionModels')" class="productionModels"
              data-bs-toggle="modal" data-bs-target="#artisanDetailsModal"
              @click="setSubject(artisan)"
            >
              {{ commaSeparated(artisan.productionModels) }}
            </td>

            <td
              v-show="columns.isVisible('styles')" class="styles"
              data-bs-toggle="modal" data-bs-target="#artisanDetailsModal"
              @click="setSubject(artisan)"
            >
              {{ commaSeparatedOther(artisan.styles, artisan.otherStyles) }}
            </td>

            <td
              v-show="columns.isVisible('types')" class="types"
              data-bs-toggle="modal" data-bs-target="#artisanDetailsModal"
              @click="setSubject(artisan)"
            >
              {{ commaSeparatedOther(artisan.orderTypes, artisan.otherOrderTypes) }}
            </td>

            <td
              v-show="columns.isVisible('features')" class="features"
              data-bs-toggle="modal" data-bs-target="#artisanDetailsModal"
              @click="setSubject(artisan)"
            >
              {{ commaSeparatedOther(artisan.features, artisan.otherFeatures) }}
            </td>


            <td
              v-show="columns.isVisible('species')" class="species"
              data-bs-toggle="modal" data-bs-target="#artisanDetailsModal"
              @click="setSubject(artisan)"
            >
              <ul v-if="artisan.gotSpeciesInfo">
                <li v-for="item in artisan.speciesDoes" :key="item" class="yes">
                  <i class="fas fa-check" />&nbsp;{{ item }}
                </li>

                <li v-for="item in artisan.speciesDoesnt" :key="item" class="no">
                  <i class="fas fa-times" />&nbsp;{{ item }}
                </li>
              </ul>
            </td>

            <td
              v-show="columns.isVisible('commissions')" class="commissions"
              data-bs-toggle="modal" data-bs-target="#artisanDetailsModal"
              @click="setSubject(artisan)"
            >
              <ul v-if="artisan.isTracked">
                <li v-if="artisan.cstIssueText" class="inaccurate">
                  <i class="far fa-question-circle" />&nbsp;{{ artisan.cstIssueText }}
                </li>

                <li v-for="item in artisan.openFor" :key="item" class="yes">
                  <i class="fas fa-check" />&nbsp;{{ item }}
                </li>

                <li v-for="item in artisan.closedFor" :key="item" class="no">
                  <i class="fas fa-times" />&nbsp;{{ item }}
                </li>
              </ul>
            </td>

            <td v-show="columns.isVisible('links')" class="links">
              <div class="btn-group artisan-links" role="group" aria-label="Links to websites">
                <a v-if="isDevEnv()" class="btn btn-warning" :href="Static.getArtisanEditPath(artisan.lastMakerId)"><i class="fas fa-edit" /></a>

                <a v-if="artisan.fursuitReviewUrl" class="btn btn-secondary" :href="artisan.fursuitReviewUrl" target="_blank"><i class="fas fa-balance-scale" /></a>

                <a class="btn btn-secondary" :href="'https://bewares.getfursu.it/#search:' + artisan.abSearchJson" target="_blank"><i class="fa-solid fa-spell-check" /></a>

                <a v-if="artisan.websiteUrl" class="btn btn-secondary" :href="artisan.websiteUrl" target="_blank"><i class="fas fa-link" /></a>

                <div class="btn-group" role="group">
                  <button :id="'drpdwnmn' + index.toString()" type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" />

                  <ul class="dropdown-menu" :aria-labelledby="'drpdwnmn' + index.toString()">
                    <TblLink
                      :url="'https://bewares.getfursu.it/#search:' + artisan.abSearchJson"
                      icon-class="fa-solid fa-spell-check" label="Check on Artists Beware"
                    />
                    <TblLink :url="artisan.fursuitReviewUrl" icon-class="fas fa-balance-scale" label="FursuitReview" />
                    <TblLink :url="artisan.websiteUrl" icon-class="fas fa-link" label="Official website" />
                    <TblLink v-for="item in artisan.pricesUrls" :key="item" :url="item" icon-class="fas fa-dollar-sign" label="Prices" />
                    <TblLink :url="artisan.faqUrl" icon-class="fas fa-comments" label="FAQ" />
                    <TblLink :url="artisan.queueUrl" icon-class="fas fa-clipboard-list" label="Queue" />
                    <TblLink :url="artisan.furAffinityUrl" icon-class="fas fa-image" label="FurAffinity" />
                    <TblLink :url="artisan.deviantArtUrl" icon-class="fab fa-deviantart" label="DeviantArt" />
                    <TblLink :url="artisan.mastodonUrl" icon-class="fa-brands fa-mastodon" label="Mastodon" />
                    <TblLink :url="artisan.twitterUrl" icon-class="fab fa-twitter" label="Twitter" />
                    <TblLink :url="artisan.facebookUrl" icon-class="fab fa-facebook" label="Facebook" />
                    <TblLink :url="artisan.tumblrUrl" icon-class="fab fa-tumblr" label="Tumblr" />
                    <TblLink :url="artisan.youtubeUrl" icon-class="fab fa-youtube" label="YouTube" />
                    <TblLink :url="artisan.instagramUrl" icon-class="fab fa-instagram" label="Instagram" />
                    <TblLink :url="artisan.etsyUrl" icon-class="fab fa-etsy" label="Etsy" />
                    <TblLink :url="artisan.theDealersDenUrl" icon-class="fas fa-shopping-cart" label="The Dealers Den" />
                    <TblLink :url="artisan.otherShopUrl" icon-class="fas fa-shopping-cart" label="On-line shop" />
                    <TblLink :url="artisan.furryAminoUrl" icon-class="fas fa-paw" label="Furry Amino" />
                    <TblLink :url="artisan.scritchUrl" icon-class="fas fa-camera" label="Scritch" />
                    <TblLink :url="artisan.furtrackUrl" icon-class="fas fa-camera" label="Furtrack" />
                    <TblLink :url="artisan.linklistUrl" icon-class="fas fa-link" label="List of links" />

                    <li>
                      <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#artisanUpdatesModal" @click="setSubject(artisan)">
                        <i class="fas fa-exclamation-triangle" /> Data outdated/inaccurate?
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
            </td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>

  <p id="artisans-table-count" class="small">
    Displaying {{ artisans.length }} out of {{ Static.getTotalArtisansCount() }} fursuit makers in the database.
  </p>
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
