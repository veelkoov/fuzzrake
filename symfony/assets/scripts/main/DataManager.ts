import AgeAndSfwConfig from "../class/AgeAndSfwConfig";
import DarnIt from "../DarnIt";
import MessageBus from "./MessageBus";
import Static from "../Static";

export type ArtisanDataRow = readonly [
  makerId: string,
  formerMakerIds: string[],
  name: string,
  formerly: string[],
  dateAdded: string,
  dateUpdated: string,
  intro: string,
  since: string,
  languages: string[],
  country: string,
  state: string,
  city: string,
  productionModelsComment: string,
  productionModels: string[],
  stylesComment: string,
  styles: string[],
  otherStyles: string[],
  orderTypesComment: string,
  orderTypes: string[],
  otherOrderTypes: string[],
  featuresComment: string,
  features: string[],
  otherFeatures: string[],
  paymentPlans: string[],
  paymentMethods: string[],
  currenciesAccepted: string[],
  speciesComment: string,
  speciesDoes: string[],
  speciesDoesnt: string[],
  isMinor: boolean | null,
  ages: string,
  nsfwWebsite: boolean | null,
  nsfwSocial: boolean | null,
  doesNsfw: boolean | null,
  safeDoesNsfw: boolean,
  safeWorksWithMinors: boolean,
  fursuitReviewUrl: string,
  websiteUrl: string,
  pricesUrls: string[],
  commissionsUrls: string[],
  faqUrl: string,
  furAffinityUrl: string,
  deviantArtUrl: string,
  mastodonUrl: string,
  twitterUrl: string,
  facebookUrl: string,
  tumblrUrl: string,
  instagramUrl: string,
  youtubeUrl: string,
  linklistUrl: string,
  furryAminoUrl: string,
  etsyUrl: string,
  theDealersDenUrl: string,
  otherShopUrl: string,
  queueUrl: string,
  scritchUrl: string,
  furtrackUrl: string,
  photoUrls: string[],
  miniatureUrls: string[],
  otherUrls: string[],
  notes: string,
  inactiveReason: string,
  csLastCheck: string,
  csTrackerIssue: boolean,
  openFor: string[],
  closedFor: string[],
  completeness: number,
  contactAllowed: string,
  contactInfoObfuscated: string,
];

export default class DataManager {
  private prevQuery: string | null = null;
  private readonly ageAndSfwConfig: AgeAndSfwConfig =
    AgeAndSfwConfig.getInstance();

  public constructor(private readonly messageBus: MessageBus) {
    messageBus.listenDataLoadRequests(
      (newQuery: string, isExhaustive: boolean) =>
        this.queryUpdate(newQuery, isExhaustive),
    );
  }

  private queryUpdate(newQuery: string, isExhaustive: boolean): void {
    const usedQuery = isExhaustive
      ? `?${newQuery}`
      : this.getQueryWithMakerModeAndSfwOptions(newQuery);

    if (this.prevQuery === usedQuery) {
      return;
    }

    this.prevQuery = usedQuery;

    Static.showLoadingIndicator();

    jQuery.ajax(Static.getApiUrl(`artisans-array.json${usedQuery}`), {
      success: (newData: ArtisanDataRow[]): void => {
        this.messageBus.notifyDataChange(newData);
      },
      error: this.displayError,
    });
  }

  private displayError(
    _: JQuery.jqXHR,
    textStatus: string | null,
    errorThrown: string | null,
  ): void {
    let details = "";

    if (errorThrown) {
      details = errorThrown;
    } else if (textStatus) {
      details = textStatus;
    }

    if ("" !== details) {
      details = ` The error was: ${details}`;
    }

    DarnIt.report(
      `The server returned unexpected response (or none).${details}`,
      "",
      false,
    );
  }

  private getQueryWithMakerModeAndSfwOptions(newQuery: string): string {
    if (AgeAndSfwConfig.getInstance().getMakerMode()) {
      return "?isAdult=1&wantsSfw=0&wantsInactive=1";
    }

    let usedQuery = `?isAdult=${this.ageAndSfwConfig.getIsAdult() ? "1" : "0"}&wantsSfw=${this.ageAndSfwConfig.getWantsSfw() ? "1" : "0"}`;

    if ("" !== newQuery) {
      usedQuery += "&" + newQuery;
    }

    return usedQuery;
  }
}
