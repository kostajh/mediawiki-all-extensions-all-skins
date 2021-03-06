import { mount, createLocalVue } from "@vue/test-utils";
import SXSentenceSelectorActionButtons from "./SXSentenceSelectorActionButtons";
import VueBananaI18n from "vue-banana-i18n";
import Vuex from "vuex";
const localVue = createLocalVue();
localVue.use(Vuex);
localVue.use(VueBananaI18n);

describe("SXSentenceSelector Action Buttons", () => {
  const store = new Vuex.Store({
    modules: {
      application: {
        namespaced: true,
        state: {
          isSectionTitleSelectedForTranslation: false
        },
        getters: {
          isCurrentSentenceLast: state => false
        }
      }
    }
  });
  const wrapper = mount(SXSentenceSelectorActionButtons, {
    store,
    localVue
  });

  it("Component output matches snapshot", () => {
    expect(wrapper.element).toMatchSnapshot();
  });

  it("Component emits correct actions on action buttons click", () => {
    const previousSentenceButton = wrapper.find(
      ".sx-sentence-selector__previous-sentence-button"
    );
    previousSentenceButton.trigger("click");
    expect(wrapper.emitted("select-previous-segment")).toBeTruthy();

    const applyTranslationButton = wrapper.find(
      ".sx-sentence-selector__apply-translation-button"
    );
    applyTranslationButton.trigger("click");
    expect(wrapper.emitted("apply-translation")).toBeTruthy();

    const skipTranslationButton = wrapper.find(
      ".sx-sentence-selector__skip-translation-button"
    );
    skipTranslationButton.trigger("click");
    expect(wrapper.emitted("skip-translation")).toBeTruthy();
  });

  it("Previous button is disabled when sentence is first in array", async () => {
    store.state.application.isSectionTitleSelectedForTranslation = true;
    /** Wait for DOM to be updated **/
    await wrapper.vm.$nextTick();
    const skipButton = wrapper.find("button");

    expect(skipButton.attributes("disabled")).toBe("disabled");
  });
});
