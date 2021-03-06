<template>
  <section class="sx-sentence-selector fill-height column ma-0 no-wrap">
    <mw-row class="sx-sentence-selector__header ma-0 py-2">
      <mw-col shrink>
        <mw-button
          class="px-3"
          type="icon"
          :icon="mwIconArrowPrevious"
          @click="onClose"
        />
      </mw-col>
      <mw-col grow class="px-1">
        <h4
          v-i18n:cx-sx-sentence-selector-header-title
          class="sx-sentence-selector__header-title"
        />
      </mw-col>
      <mw-col shrink class="px-3">
        <mw-button
          :label="$i18n('cx-sx-sentence-selector-done-button-label')"
          @click="previewTranslation"
        />
      </mw-col>
    </mw-row>
    <mw-row
      tag="section"
      direction="column"
      align="start"
      justify="between"
      class="sx-sentence-selector__body fill-height ma-0"
    >
      <mw-col class="sx-sentence-selector__section">
        <sx-sentence-selector-content-header />
        <div class="sx-sentence-selector__section-contents px-4">
          <sub-section
            v-for="subSection in subSections"
            :id="subSection.id"
            :key="`sub-section-${subSection.id}`"
            :sub-section="subSection"
            @bounce-translation="bounceTranslation"
          />
        </div>
      </mw-col>
      <!--      MwCard has a margin-bottom: 1em by default. Since this is -->
      <!--      the margin that the card jumps to when bouncing, we control-->
      <!--      card bounce through mb-0 class-->
      <proposed-translation-card
        :translation="translationPreview"
        :class="{ 'mb-0': !shouldProposedTranslationBounce }"
        @configure-options="configureTranslationOptions"
        @edit-translation="editTranslation"
        @apply-translation="applyTranslation"
        @skip-translation="skipTranslation"
        @select-previous-segment="selectPreviousSegment"
      />
    </mw-row>
    <sx-translation-selector :active.sync="isTranslationOptionsActive" />
  </section>
</template>

<script>
import { MwButton, MwRow, MwCol } from "@/lib/mediawiki.ui";
import { mwIconArrowPrevious } from "@/lib/mediawiki.ui/components/icons";

import SxTranslationSelector from "./SXTranslationSelector";
import { mapState, mapGetters, mapActions } from "vuex";
import SxSentenceSelectorContentHeader from "./SXSentenceSelectorContentHeader";
import ProposedTranslationCard from "./ProposedTranslationCard";
import SubSection from "./SubSection";

import { loadVEModules } from "@/plugins/ve";

export default {
  name: "SxSentenceSelector",
  components: {
    ProposedTranslationCard,
    SubSection,
    SxSentenceSelectorContentHeader,
    MwRow,
    MwCol,
    SxTranslationSelector,
    MwButton
  },
  data: () => ({
    mwIconArrowPrevious,
    translation: null,
    isTranslationOptionsActive: false,
    shouldProposedTranslationBounce: false
  }),
  computed: {
    ...mapState({
      suggestion: state => state.application.currentSectionSuggestion,
      currentPageSection: state => state.application.currentSourceSection,
      isSectionTitleSelected: state =>
        state.application.isSectionTitleSelectedForTranslation,
      selectedProvider: state => state.application.currentMTProvider
    }),
    ...mapGetters({
      selectedSentence: "application/getCurrentSelectedSentence"
    }),
    /**
     * Machine translation of sentence for currently selected MT provider
     */
    proposedSentenceTranslation: vm =>
      vm.selectedSentence?.proposedTranslations[vm.selectedProvider] || "",
    /**
     * Machine translation of title for currently selected MT provider
     */
    proposedTitleTranslation: vm =>
      vm.currentPageSection?.proposedTitleTranslations[vm.selectedProvider] ||
      "",
    /**
     * This computed property returns a preview of the translation of a sentence
     * or section title that will be applied to that sentence if user clicks
     * "Apply translation" button. If this segment is already  translated, current
     * applied translation will be returned. Machine translation for currently
     * selected MT provider will be returned otherwise.
     * @return {String}
     */
    translationPreview: vm =>
      vm.titleTranslationPreview || vm.sentenceTranslationPreview,
    subSections: vm => vm.currentPageSection?.subSections,
    sourcePage: vm =>
      vm.$store.getters["mediawiki/getPage"](
        vm.suggestion.sourceLanguage,
        vm.suggestion.sourceTitle
      ),
    /**
     * If section title is not selected for translation, false will be returned
     */
    titleTranslationPreview: vm =>
      vm.isSectionTitleSelected &&
      (vm.currentPageSection.translatedTitle || vm.proposedTitleTranslation),
    sentenceTranslationPreview: vm =>
      vm.selectedSentence?.translatedContent || vm.proposedSentenceTranslation,
    originalSegmentContent: vm =>
      vm.isSectionTitleSelected
        ? vm.currentPageSection.originalTitle
        : vm.selectedSentence.originalContent
  },
  async mounted() {
    await this.$store.dispatch("application/initializeMTProviders");
    this.$store.dispatch("application/selectInitialTranslationSegment");
    // Start loading VE in background. Don't wait for it though.
    // We anticipate that user is going to use editor in next step.
    loadVEModules();
  },
  methods: {
    ...mapActions({
      skipTranslation: "application/selectNextSentence",
      selectPreviousSegment: "application/selectPreviousSegment"
    }),
    applyTranslation() {
      this.$store.dispatch("application/applyTranslationToSelectedSegment", {
        translation: this.translationPreview
      });
    },
    bounceTranslation() {
      this.shouldProposedTranslationBounce = true;
      setTimeout(() => {
        this.shouldProposedTranslationBounce = false;
      }, 100);
    },
    onClose() {
      this.$router.go(-1);
    },
    configureTranslationOptions() {
      this.isTranslationOptionsActive = true;
      this.$store.dispatch("application/translateSegmentForAllProviders");
    },
    editTranslation() {
      this.$router.push({
        name: "sx-editor",
        params: {
          content: this.translationPreview,
          language: this.suggestion.targetLanguage,
          originalContent: this.originalSegmentContent
        }
      });
    },
    previewTranslation() {
      this.$router.push({
        name: "sx-publisher"
      });
    }
  }
};
</script>

<style lang="less">
@import "@/lib/mediawiki.ui/variables/wikimedia-ui-base.less";
@import "@/styles/page.less";

.sx-sentence-selector {
  &__header {
    background-color: @background-color-base--disabled;
    box-shadow: 0 @border-width-base @border-color-base--disabled;
    &-title {
      color: @color-base;
    }
  }
  &__body {
    min-height: 80vh;
  }
  &__section {
    overflow: auto;
  }
}
</style>
