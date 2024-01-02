<template>
  <div id="artisanDetailsModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div id="artisanDetailsModalContent" class="modal-content artisan-data">
        <div class="modal-header">
          <h5 class="modal-title">
            <template v-if="artisan.makerId">
              <a id="makerId" :href="'#' + artisan.makerId">
                <span class="badge bg-secondary"><i class="fas fa-link" /> {{ artisan.makerId }}</span>
              </a> <wbr>
            </template>

            <span id="artisanName">{{ artisan.name }}</span>&nbsp;<span class="flag-icon" :class="'flag-icon-' + artisan.lcCountry" />

            <small>
              Based in <OptionalText :item="artisan.location" />;
              crafting since <OptionalText :item="getSinceText()" />

              <template v-if="artisan.formerly.length">
                <br>Formerly/a.k.a. {{ commaSeparated(artisan.formerly) }}
              </template>
            </small>
          </h5>

          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" />
        </div>

        <div class="modal-body row px-4 py-2">
          <div v-if="artisan.inactiveReason" class="col-md-12 p-1 alert alert-warning" role="alert">
            <strong>This maker has been hidden for the following reason:</strong> <br>
            {{ artisan.inactiveReason }}
          </div>

          <div v-if="hasPhotos()" class="col-md-12 p-1 overflow-auto imgs-container">
            <div v-for="(item, index) in artisan.photoUrls" :key="item">
              <a :href="item" target="_blank"><img :src="artisan.miniatureUrls[index]" alt=""></a>
            </div>
          </div>

          <div v-if="artisan.intro" class="col-md-12 p-1">
            <p class="lead nl2br">
              {{ artisan.intro }}
            </p>
          </div>

          <div class="col-md-12 p-1">
            <p class="mb-0 small">
              Ages of studio members: <AgesDescription :add-text="true" :artisan="artisan" />
            </p>
          </div>

          <div class="col-md-3 p-1">
            <h5>Produces</h5>

            <div class="small pb-2">
              <OptionalList :comment="artisan.productionModelsComment" :items="artisan.productionModels" :other-items="[]" />
            </div>

            <h5>Styles</h5>

            <div class="small">
              <OptionalList :comment="artisan.stylesComment" :items="artisan.styles" :other-items="artisan.otherStyles" />
            </div>
          </div>

          <div class="col-md-5 p-1">
            <h5>Types</h5>

            <div class="small">
              <OptionalList :comment="artisan.orderTypesComment" :items="artisan.orderTypes" :other-items="artisan.otherOrderTypes" />
            </div>
          </div>

          <div class="col-md-4 p-1">
            <h5>Features</h5>

            <div class="small">
              <OptionalList :comment="artisan.featuresComment" :items="artisan.features" :other-items="artisan.otherFeatures" />
            </div>
          </div>

          <div class="col-md-6 p-1">
            <h5>Species</h5>

            <div id="artisanSpecies" class="small pb-2">
              <span v-if="artisan.speciesComment" class="nl2br">
                {{ artisan.speciesComment }}<br>
              </span>

              <template v-if="0 !== artisan.speciesDoes.length">
                <strong>Does</strong>: {{ commaSeparated(artisan.speciesDoes) }}
                <br v-if="0 !== artisan.speciesDoesnt.length">
              </template>

              <template v-if="0 !== artisan.speciesDoesnt.length">
                <strong>Doesn't</strong>: {{ commaSeparated(artisan.speciesDoesnt) }}
              </template>

              <UnknownValue v-if="0 === artisan.speciesDoes.length && 0 === artisan.speciesDoesnt.length" />
            </div>

            <h5>Languages</h5>

            <div class="small">
              <OptionalComSep :items="artisan.languages" />
            </div>
          </div>

          <div class="col-md-6 p-1">
            <h5>Payment plans</h5>

            <div class="small pb-2">
              <OptionalList :items="artisan.paymentPlans" />
            </div>

            <h5>Currencies</h5>

            <div class="small pb-2">
              <OptionalComSep :items="artisan.currenciesAccepted" />
            </div>

            <h5>Methods</h5>

            <div class="small">
              <OptionalComSep :items="artisan.paymentMethods" />
            </div>
          </div>

          <div class="col-md-12 p-1">
            <h5>Links</h5>

            <p class="small mb-1">
              Please mention
              "<mark class="user-select-all">I found you on getfursu.it<template v-if="!artisan.completenessGood">
                (BTW it says your data could use some updates)</template></mark>"
              when contacting the maker, thank you! ❤️
            </p>

            <div id="artisanLinks">
              <CardLink :url="'https://bewares.getfursu.it/#search:' + artisan.abSearchJson" label="Check Artists Beware records" icon-class="fas fa-balance-scale" add-btn-class="border border-primary" />
              <CardLink :url="artisan.fursuitReviewUrl" label="FursuitReview" icon-class="fas fa-balance-scale" add-btn-class="border border-primary" />
              <CardLink :url="artisan.websiteUrl" label="Official website" icon-class="fas fa-link" />
              <CardLink v-for="item in artisan.pricesUrls" :key="item" :url="item" label="Prices" icon-class="fas fa-dollar-sign" />
              <CardLink :url="artisan.faqUrl" label="FAQ" icon-class="fas fa-comments" />
              <CardLink :url="artisan.queueUrl" label="Queue" icon-class="fas fa-clipboard-list" />
              <CardLink :url="artisan.furAffinityUrl" label="FurAffinity" icon-class="fas fa-image" />
              <CardLink :url="artisan.deviantArtUrl" label="DeviantArt" icon-class="fab fa-deviantart" />
              <CardLink :url="artisan.mastodonUrl" label="Mastodon" icon-class="fa-brands fa-mastodon" />
              <CardLink :url="artisan.twitterUrl" label="Twitter" icon-class="fab fa-twitter" />
              <CardLink :url="artisan.facebookUrl" label="Facebook" icon-class="fab fa-facebook" />
              <CardLink :url="artisan.tumblrUrl" label="Tumblr" icon-class="fab fa-tumblr" />
              <CardLink :url="artisan.youtubeUrl" label="YouTube" icon-class="fab fa-youtube" />
              <CardLink :url="artisan.instagramUrl" label="Instagram" icon-class="fab fa-instagram" />
              <CardLink :url="artisan.etsyUrl" label="Etsy" icon-class="fab fa-etsy" />
              <CardLink :url="artisan.theDealersDenUrl" label="The Dealers Den" icon-class="fas fa-shopping-cart" />
              <CardLink :url="artisan.otherShopUrl" label="On-line shop" icon-class="fas fa-shopping-cart" />
              <CardLink :url="artisan.furryAminoUrl" label="Furry Amino" icon-class="fas fa-paw" />
              <CardLink :url="artisan.scritchUrl" label="Scritch" icon-class="fas fa-camera" />
              <CardLink :url="artisan.furtrackUrl" label="Furtrack" icon-class="fas fa-camera" />
              <CardLink :url="artisan.linklistUrl" label="List of links" icon-class="fas fa-link" />
            </div>
          </div>

          <div class="col-md-12 p-1">
            <h5>Commissions status</h5>
          </div>

          <div v-if="0 === artisan.commissionsUrls.length" class="col-md-12 p-1">
            <p>Commissions status is not being tracked.</p>

            <p><a :href="Static.getTrackingPath()" target="_blank">Learn more</a></p>
          </div>

          <template v-else>
            <div class="p-1" :class="[artisan.isStatusKnown ? 'col-md-6' : 'col-md-8']">
              <template v-if="artisan.isStatusKnown">
                <p v-if="artisan.csTrackerIssue">
                  <i class="inaccurate fas fa-question-circle" /> Note: the software encountered apparent difficulties while figuring out the status; the information is most probably inaccurate/<wbr>incomplete.
                </p>

                <table class="table table-sm table-striped table-borderless">
                  <tbody>
                    <tr v-for="item in artisan.openFor" :key="item">
                      <td>{{ item }}</td>
                      <td><i class="fas fa-check-square" />&nbsp;Open</td>
                    </tr>
                    <tr v-for="item in artisan.closedFor" :key="item">
                      <td>{{ item }}</td>
                      <td><i class="fas fa-times-circle" />&nbsp;Closed</td>
                    </tr>
                  </tbody>
                </table>
              </template>
              <template v-else>
                <p>
                  <i class="inaccurate fas fa-question-circle" /> Failed to automatically determine commissions status.
                  It should be tracked and updated automatically based on the contents of:
                  <template v-for="item in artisan.commissionsUrls" :key="item">
                    <a :href="item" target="_blank">{{ item }}</a>, <wbr>
                  </template>
                  however the software failed to "understand" the contents. Last time tried on {{ artisan.csLastCheck }} UTC.
                </p>

                <p><a :href="Static.getTrackingPath()" target="_blank">Learn more</a></p>
              </template>
            </div>

            <div class="p-1" :class="[artisan.isStatusKnown ? 'col-md-6' : 'col-md-4 text-end']">
              <template v-if="artisan.isStatusKnown">
                <p>
                  Status is tracked and updated automatically based on the contents of:
                  <template v-for="item in artisan.commissionsUrls" :key="item">
                    <a :href="item" target="_blank">{{ item }}</a> <wbr>
                  </template>
                  <br>
                  Last time checked on {{ artisan.csLastCheck }} UTC.
                </p>

                <p><a :href="Static.getTrackingPath()" target="_blank">Learn more</a></p>
              </template>

              <img v-else :src="Static.getTrackingFailedImgSrc()" class="img-fluid tracking-failed" alt="">
            </div>
          </template>

          <div class="col-md-12 p-1 pt-3">
            <h5>Data incomplete/<wbr>inaccurate/outdated?</h5>

            <p class="small">
              This maker/studio has {{ artisan.completeness }}% data completeness. {{ getCompletenessText() }}
              Click the button below to check update options.
            </p>

            <p class="small">
              Last time the information here was updated was: {{ getLastTimeUpdatedStr() }}.
            </p>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light border border-warning" data-bs-target="#artisanUpdatesModal" data-bs-toggle="modal">
            Data outdated/inaccurate? <!-- grep-updates-button -->
          </button>

          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import AgesDescription from '../AgesDescription.vue';
import Artisan from '../../../class/Artisan';
import CardLink from './CardLink.vue';
import MainState from '../MainState';
import OptionalComSep from '../OptionalComSep.vue';
import OptionalList from './../OptionalList.vue';
import OptionalText from '../OptionalText.vue';
import Static from '../../../Static';
import UnknownValue from './../UnknownValue.vue';
import {Options, Vue} from 'vue-class-component';

@Options({
  components: {
    OptionalComSep,
    OptionalText, AgesDescription, CardLink, Optional: OptionalComSep, UnknownValue, OptionalList},
  computed: {
    Static() {
      return Static;
    },
  },
  props: {
    state: {type: MainState, required: true},
  },
})
export default class Link extends Vue {
  private state!: MainState;

  private static readonly MONTHS: { [key: string]: string; } = {
    '01': 'Jan',
    '02': 'Feb',
    '03': 'Mar',
    '04': 'Apr',
    '05': 'May',
    '06': 'Jun',
    '07': 'Jul',
    '08': 'Aug',
    '09': 'Sep',
    '10': 'Oct',
    '11': 'Nov',
    '12': 'Dec',
  };

  private get artisan(): Artisan {
    return this.state.subjectArtisan;
  }

  private commaSeparated(list: string[]): string {
    return list.join(', ');
  }

  private getSinceText(): string {
    if (this.artisan.since === '') {
      return '';
    }

    let parts = this.artisan.since.split('-');

    return Link.MONTHS[parts[1]] + ' ' + parts[0];
  }

  private hasPhotos(): boolean {
    return 0 !== this.artisan.photoUrls.length && this.artisan.miniatureUrls.length === this.artisan.photoUrls.length;
  }

  private getCompletenessText(): string {
    if (this.artisan.completeness >= Artisan.DATA_COMPLETE_LEVEL_PERFECT) {
      return 'Awesome! ❤️';
    } else if (this.artisan.completeness >= Artisan.DATA_COMPLETE_LEVEL_GREAT) {
      return 'Great!'
    } else if (this.artisan.completeness >= Artisan.DATA_COMPLETE_LEVEL_GOOD) {
      return 'Good job!'
    } else if (this.artisan.completeness >= Artisan.DATA_COMPLETE_LEVEL_OK) {
      return 'Some updates might be helpful...';
    } else {
      return 'Yikes! :( Updates needed!';
    }
  }

  private getLastTimeUpdatedStr(): string {
    // TODO: Should not check for 'unknown'; https://github.com/veelkoov/fuzzrake/issues/174

    if (this.artisan.dateUpdated && this.artisan.dateUpdated !== 'unknown') {
      return this.artisan.dateUpdated + ' UTC';
    } else if (this.artisan.dateAdded && this.artisan.dateAdded !== 'unknown') {
      return this.artisan.dateAdded + ' UTC';
    } else {
      return 'before 2021-01-10';
    }
  }
}
</script>

<style scoped>
  .nl2br {
    white-space: pre-wrap;
  }
</style>
