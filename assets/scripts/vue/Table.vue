<template>
  <table id="artisans" class="table table-striped table-sm table-hover">
    <thead class="table-dark">
      <tr>
        <th class="name text-start">Fursuit maker /&nbsp;studio name</th>

        <th v-for="(label, name, index) of columns.columns"
            v-show="columns.isVisible(name)"
            :class="[name, index === columns.count() - 1 ? 'text-end' : 'text-center']">{{ label }}</th>
      </tr>
    </thead>

    <tbody>
      <template v-for="(artisan, index) of artisans"><tr
          v-if="matchesText(artisan)"
          :data-index=index
          :id="artisan.makerId ? artisan.makerId : null"
          class="fursuit-maker artisan-data"
          :class="{
            inactive: artisan.inactiveReason,
            'matched-maker-id': matchedMakerId(artisan),
          }"
      >
        <td class="name" @click="setSubject(artisan)" data-bs-toggle="modal" data-bs-target="#artisanDetailsModal">
          <span class="flag-icon" :class="'flag-icon-' + artisan.lcCountry"></span>

          {{ artisan.name }}

          <template v-if="artisan.inactiveReason">
            [inactive] <!-- grep-inactive-mark -->
          </template>

          <span class="text-nowrap">
            <AgesDescription :add-text="false" :artisan="artisan" />

            <span v-if="artisan.isNew" class="new-artisan">
              <i class="fa-solid fa-leaf"></i> recently added
            </span>
          </span>
        </td>

        <td class="makerId" v-show="columns.isVisible('makerId')"
            @click="setSubject(artisan)" data-bs-toggle="modal" data-bs-target="#artisanDetailsModal">
          {{ artisan.makerId }}
        </td>

        <td class="state" v-show="columns.isVisible('state')"
            @click="setSubject(artisan)" data-bs-toggle="modal" data-bs-target="#artisanDetailsModal">
          {{ artisan.state }}
        </td>

        <td class="languages" v-show="columns.isVisible('languages')"
            @click="setSubject(artisan)" data-bs-toggle="modal" data-bs-target="#artisanDetailsModal">
          {{ commaSeparated(artisan.languages) }}
        </td>

        <td class="productionModels" v-show="columns.isVisible('productionModels')"
            @click="setSubject(artisan)" data-bs-toggle="modal" data-bs-target="#artisanDetailsModal">
          {{ commaSeparated(artisan.productionModels) }}
        </td>

        <td class="styles" v-show="columns.isVisible('styles')"
            @click="setSubject(artisan)" data-bs-toggle="modal" data-bs-target="#artisanDetailsModal">
          {{ commaSeparatedOther(artisan.styles, artisan.otherStyles) }}
        </td>

        <td class="types" v-show="columns.isVisible('types')"
            @click="setSubject(artisan)" data-bs-toggle="modal" data-bs-target="#artisanDetailsModal">
          {{ commaSeparatedOther(artisan.orderTypes, artisan.otherOrderTypes) }}
        </td>

        <td class="features" v-show="columns.isVisible('features')"
            @click="setSubject(artisan)" data-bs-toggle="modal" data-bs-target="#artisanDetailsModal">
          {{ commaSeparatedOther(artisan.features, artisan.otherFeatures) }}
        </td>

        <td class="species" v-show="columns.isVisible('species')"
            @click="setSubject(artisan)" data-bs-toggle="modal" data-bs-target="#artisanDetailsModal">
          <ul v-if="artisan.gotSpeciesInfo">
            <li v-for="item in artisan.speciesDoes" class="yes"><i class="fas fa-check"></i>&nbsp;{{ item }}</li>

            <li v-for="item in artisan.speciesDoesnt" class="no"><i class="fas fa-times"></i>&nbsp;{{ item }}</li>
          </ul>
        </td>

        <td class="commissions" v-show="columns.isVisible('commissions')"
            @click="setSubject(artisan)" data-bs-toggle="modal" data-bs-target="#artisanDetailsModal">
          <ul v-if="artisan.isTracked">
            <li v-if="artisan.cstIssueText" class="inaccurate"><i class="far fa-question-circle"></i>&nbsp;{{ artisan.cstIssueText }}</li>

            <li v-for="item in artisan.openFor" class="yes"><i class="fas fa-check"></i>&nbsp;{{ item }}</li>

            <li v-for="item in artisan.closedFor" class="no"><i class="fas fa-times"></i>&nbsp;{{ item }}</li>
          </ul>
        </td>

        <td class="links" v-show="columns.isVisible('links')">
          <div class="btn-group artisan-links" role="group" aria-label="Links to websites">
            <a v-if="isDevEnv()" class="btn btn-warning" :href="Static.getArtisanEditUrl(artisan.getLastMakerId())"><i class="fas fa-edit"></i></a>

            <a v-if="artisan.fursuitReviewUrl" class="btn btn-secondary" :href="artisan.fursuitReviewUrl" target="_blank"><i class="fas fa-balance-scale"></i></a>

            <a class="btn btn-secondary" :href="'https://bewares.getfursu.it/#search:' + artisan.abSearchJson" target="_blank"><i class="fa-solid fa-spell-check"></i></a>

            <a v-if="artisan.websiteUrl" class="btn btn-secondary" :href="artisan.websiteUrl" target="_blank"><i class="fas fa-link"></i></a>

            <div class="btn-group" role="group">
              <button :id="'drpdwnmn' + index.toString()" type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"></button>

              <ul class="dropdown-menu" :aria-labelledby="'drpdwnmn' + index.toString()">
                <TblLink :url="'https://bewares.getfursu.it/#search:' + artisan.abSearchJson"
                         iconClass="fa-solid fa-spell-check" label="Check on Artists Beware" />
                <TblLink :url=artisan.fursuitReviewUrl iconClass="fas fa-balance-scale" label="FursuitReview" />
                <TblLink :url=artisan.websiteUrl iconClass="fas fa-link" label="Official website" />
                <TblLink v-for="item in artisan.pricesUrls" :url=item iconClass="fas fa-dollar-sign" label="Prices" />
                <TblLink :url=artisan.faqUrl iconClass="fas fa-comments" label="FAQ" />
                <TblLink :url=artisan.queueUrl iconClass="fas fa-clipboard-list" label="Queue" />
                <TblLink :url=artisan.furAffinityUrl iconClass="fas fa-image" label="FurAffinity" />
                <TblLink :url=artisan.deviantArtUrl iconClass="fab fa-deviantart" label="DeviantArt" />
                <TblLink :url=artisan.mastodonUrl iconClass="fa-brands fa-mastodon" label="Mastodon" />
                <TblLink :url=artisan.twitterUrl iconClass="fab fa-twitter" label="Twitter" />
                <TblLink :url=artisan.facebookUrl iconClass="fab fa-facebook" label="Facebook" />
                <TblLink :url=artisan.tumblrUrl iconClass="fab fa-tumblr" label="Tumblr" />
                <TblLink :url=artisan.youtubeUrl iconClass="fab fa-youtube" label="YouTube" />
                <TblLink :url=artisan.instagramUrl iconClass="fab fa-instagram" label="Instagram" />
                <TblLink :url=artisan.etsyUrl iconClass="fab fa-etsy" label="Etsy" />
                <TblLink :url=artisan.theDealersDenUrl iconClass="fas fa-shopping-cart" label="The Dealers Den" />
                <TblLink :url=artisan.otherShopUrl iconClass="fas fa-shopping-cart" label="On-line shop" />
                <TblLink :url=artisan.furryAminoUrl iconClass="fas fa-paw" label="Furry Amino" />
                <TblLink :url=artisan.scritchUrl iconClass="fas fa-camera" label="Scritch" />
                <TblLink :url=artisan.furtrackUrl iconClass="fas fa-camera" label="Furtrack" />
                <TblLink :url=artisan.linklistUrl iconClass="fas fa-link" label="List of links" />

                <li>
                  <a class="dropdown-item" @click="setSubject(artisan)" data-bs-toggle="modal" data-bs-target="#artisanUpdatesModal">
                    <i class="fas fa-exclamation-triangle"></i> Data outdated/inaccurate?
                  </a>
                </li>
              </ul>
            </div>
          </div>
        </td>
      </tr></template>
    </tbody>
  </table>

  <p id="artisans-table-count" class="small">Displaying {{ artisans.length }} out of {{
      Static.getTotalArtisansCount()
    }} fursuit makers in the database.</p>
</template>

<script lang="ts">
import AgesDescription from './AgesDescription.vue';
import Artisan from '../class/Artisan';
import ColumnsManager from '../main/ColumnsManager';
import MessageBus, {getMessageBus} from '../main/MessageBus';
import Search from '../main/Search';
import Static from '../Static';
import TblLink from './TblLink.vue';
import {DataRow} from '../main/DataManager';
import {Options, Vue} from 'vue-class-component';

@Options({
  computed: {
    Static() {
      return Static;
    }
  },
  components: {
    AgesDescription,
    TblLink,
  },
  props: {
    columns: {
      type: ColumnsManager,
      required: true,
    },
    search: {
      type: Search,
      required: true,
    },
  },
})
export default class Table extends Vue {
  private columns!: ColumnsManager;
  private search!: Search;
  private artisans: Artisan[] = [];
  private messageBus: MessageBus = getMessageBus();

  public created(): void {
    this.messageBus.listenDataChanges((newData: DataRow[]) => {
      this.artisans = newData.map(item => Artisan.fromArray(item));

      Static.hideLoadingIndicator();
    });
  }

  public updated(): void {
    this.messageBus.notifyTableUpdated();
  }

  private matchesText(artisan: Artisan): boolean {
    if ('' === this.search.textLc) {
      return true;
    }

    return artisan.searchableText.includes(this.search.textLc);
  }

  private matchedMakerId(artisan: Artisan): boolean {
    return this.search.isMakerId && (this.search.textUc === artisan.makerId || artisan.formerMakerIds.includes(this.search.textUc));
  }

  private setSubject(newSubjectArtisan: Artisan): void {
    this.messageBus.notifySubjectArtisanChange(newSubjectArtisan);
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
}
</script>
