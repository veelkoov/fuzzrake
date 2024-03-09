<template>
  <div class="card bg-light mb-3">
    <div class="card-body">
      <h5 class="card-title">
        <strong class="text-danger">Don't get scammed!</strong>
      </h5>

      <p class="card-text">
        This tool serves for finding a maker/<wbr>studio which will best match your expectations. It can save you some time searching. What it can't do for you - <strong>which means you must do that yourself</strong> - is to verify the legitimacy of the maker/<wbr>studio you want to commission.
      </p>

      <p class="card-text">
        The <a :href="Static.getShouldKnowPath()">What you should know</a> page contains some helpful information,
        tips, and links to external resources. It includes a <em>Learn about <strong>the risk</strong></em>
        <!-- grep-learn-about-the-risk --> section.
        <br>

        <span class="small">
          <strong class="text-danger">You will most probably not get your fursuit in the next weeks, so reading a bit will not hurt.</strong> Slow down.
        </span>
      </p>

      <div class="card-text">
        <div class="form-check">
          <input id="checklist-ill-be-careful" v-model="illBeCareful" class="form-check-input" type="checkbox">

          <label class="form-check-label" for="checklist-ill-be-careful">
            I understand the risk and know where to look for more information
          </label>
        </div>
      </div>

      <div v-if="illBeCareful" class="card-text">
        <hr>

        <div class="row">
          <div class="col-md-6">
            <div class="form-check">
              <input id="aasImNotAdult" v-model="isAdult" class="form-check-input" type="radio" :value="false">

              <label class="form-check-label" for="aasImNotAdult">
                I am under 18 years old. <small class="text-muted">I am looking for a maker who accepts commissions from underage people.</small>
              </label>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-check">
              <input id="aasImAdult" v-model="isAdult" class="form-check-input" type="radio" :value="true">

              <label class="form-check-label" for="aasImAdult">
                I am at least 18 years old. <small class="text-muted">Yes, lying about my age would be a stupid idea and could get me banned from commissioning my beloved fursuit maker, ever.</small>
              </label>
            </div>
          </div>
        </div>
      </div>

      <div v-if="illBeCareful && isAdult" class="card-text">
        <hr>

        <div class="row">
          <div class="col-md-6">
            <div class="form-check">
              <input id="aasKeepSfw" v-model="wantsSfw" class="form-check-input" type="radio" :value="true">

              <label class="form-check-label" for="aasKeepSfw">
                I prefer makers with "family-friendly" websites and social media.
              </label>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-check">
              <input id="aasAllowNsfw" v-model="wantsSfw" class="form-check-input" type="radio" :value="false">

              <label class="form-check-label" for="aasAllowNsfw">
                I don't mind running occasionally into NSFW stuff.
              </label>
            </div>
          </div>
        </div>
      </div>

      <hr>

      <p class="card-text text-end">
        <input id="checklist-dismiss-btn" type="button" class="btn btn-primary" :disabled="buttonDisabled" :value="buttonText" @click="confirm()">
      </p>
    </div>
  </div>
</template>

<script lang="ts">
import AgeAndSfwConfig from '../../class/AgeAndSfwConfig';
import Static from '../../Static';
import {Options, Vue} from 'vue-class-component';

@Options({
  computed: {
    Static() {
      return Static;
    },
  },
  emits: ['dismissed'],
})
export default class CheckList extends Vue {
  private illBeCareful = false;
  private isAdult: boolean | null = null;
  private wantsSfw: boolean | null = null;
  private aasConfig = AgeAndSfwConfig.getInstance();

  public created() {
    if (this.aasConfig.getIsFilled()) {
      this.illBeCareful = true;
      this.isAdult = this.aasConfig.getIsAdult();
      this.wantsSfw = this.aasConfig.getWantsSfw();
    }
  }

  private get buttonDisabled(): boolean {
    return !this.illBeCareful || null === this.isAdult || (this.isAdult && null === this.wantsSfw);
  }

  private get buttonText(): string {
    return this.buttonDisabled ? "I can't click this button yet" : 'I will now click this button';
  }

  public confirm(): void {
    this.aasConfig.setIsAdult(this.isAdult ?? false);
    this.aasConfig.setWantsSfw(this.wantsSfw ?? true);
    this.aasConfig.setIsFilled(true);
    this.aasConfig.save();

    this.$emit('dismissed');
  }
}
</script>
