<template>
	<div id="app">
		<wbmi-autocomplete-search-input
			class="wbmi-media-search-input"
			name="wbmi-media-search-input"
			:label="$i18n( 'wikibasemediainfo-special-mediasearch-input-label' )"
			:initial-value="term"
			:placeholder="$i18n( 'wikibasemediainfo-special-mediasearch-input-placeholder' )"
			:clear-title="$i18n( 'wikibasemediainfo-special-mediasearch-clear-title' )"
			:button-label="$i18n( 'searchbutton' )"
			:lookup-results="lookupResults"
			@input="getLookupResults"
			@submit="onUpdateTerm"
			@clear="onClear"
			@clear-lookup-results="clearLookupResults"
		>
		</wbmi-autocomplete-search-input>

		<!-- Generate a tab for each key in the "results" object. Data types,
		messages, and loading behavior are bound to this key. -->
		<wbmi-tabs :active="currentTab" @tab-change="onTabChange">
			<wbmi-tab v-for="tab in tabs"
				:key="tab"
				:name="tab"
				:title="tabNames[ tab ]">
				<!-- Display search filters for each tab. -->
				<search-filters
					:media-type="tab"
					@filter-change="onFilterChange( tab )"
				>
				</search-filters>

				<transition-group
					name="wbmi-concept-chips-transition"
					class="wbmi-concept-chips-transition"
					tag="div"
				>
					<concept-chips
						v-if="enableConceptChips && tab === 'bitmap' && relatedConcepts.length > 0"
						:key="'concept-chips-' + tab"
						:media-type="tab"
						:concepts="relatedConcepts"
						@concept-select="onUpdateTerm">
					</concept-chips>

					<div :key="'tab-content-' + tab">
						<!-- Display the available results for each tab -->
						<search-results :ref="tab" :media-type="tab"></search-results>

						<!-- Loading indicator if results are still pending -->
						<spinner v-if="pending[ tab ]"></spinner>

						<!-- No results message if search has completed and come back empty -->
						<no-results v-else-if="hasNoResults( tab )"></no-results>

						<!-- Empty-state encouraging user to search if they have not done so yet -->
						<empty-state v-else-if="shouldShowEmptyState"></empty-state>
					</div>
				</transition-group>

				<!-- Auto-load more results when user scrolls to the end of the list/grid,
				as long as the "autoload counter" for the tab has not reached zero -->
				<observer
					v-if="autoloadCounter[ tab ] > 0 && supportsObserver"
					@intersect="getMoreResultsForTabIfAvailable( tab )">
				</observer>

				<!-- When the autoload counter for a given tab reaches zero,
				don't load more results until user explicitly clicks on a
				"load more" button; this resets the autoload count -->
				<wbmi-button
					v-else-if="hasMore[ tab ] && !( pending[ tab ] )"
					class="wbmi-media-search-load-more"
					:progressive="true"
					@click="resetCountAndLoadMore( tab )">
					{{ $i18n( 'wikibasemediainfo-special-mediasearch-load-more-results' ) }}
				</wbmi-button>
			</wbmi-tab>
		</wbmi-tabs>
	</div>
</template>

<script>
/**
 * @file App.vue
 *
 * Top-level component for the Special:MediaSearch JS UI.
 * Contains two major elements:
 * - autocomplete search input
 * - tabs to display search results (one for each media type)
 *
 * Search query and search result data lives in Vuex, but this component
 * responds to user interactions like changes in query or tab, dispatches
 * Vuex actions to make API requests, and ensures that the URL parameters
 * remain in sync with the current search term and active tab (this is done
 * using history.replaceState)
 *
 * Autocomplete lookups are handled by a mixin. When new search input is
 * emitted from the AutocompleteSearchInput, the mixin handles the lookup
 * request and this component passes an array of string lookup results to the
 * AutocompleteSearchInput for display.
 */
var AUTOLOAD_COUNT = 2,
	mapState = require( 'vuex' ).mapState,
	mapGetters = require( 'vuex' ).mapGetters,
	mapMutations = require( 'vuex' ).mapMutations,
	mapActions = require( 'vuex' ).mapActions,
	WbmiAutocompleteSearchInput = require( './base/AutocompleteSearchInput.vue' ),
	WbmiTab = require( './base/Tab.vue' ),
	WbmiTabs = require( './base/Tabs.vue' ),
	WbmiButton = require( './base/Button.vue' ),
	SearchResults = require( './SearchResults.vue' ),
	SearchFilters = require( './SearchFilters.vue' ),
	ConceptChips = require( './ConceptChips.vue' ),
	NoResults = require( './NoResults.vue' ),
	Observer = require( './base/Observer.vue' ),
	Spinner = require( './Spinner.vue' ),
	EmptyState = require( './EmptyState.vue' ),
	autocompleteLookupHandler = require( './../mixins/autocompleteLookupHandler.js' ),
	url = new mw.Uri();

// @vue/component
module.exports = {
	name: 'MediaSearch',

	components: {
		'wbmi-tabs': WbmiTabs,
		'wbmi-tab': WbmiTab,
		'wbmi-autocomplete-search-input': WbmiAutocompleteSearchInput,
		'wbmi-button': WbmiButton,
		'search-results': SearchResults,
		'search-filters': SearchFilters,
		'concept-chips': ConceptChips,
		observer: Observer,
		spinner: Spinner,
		'empty-state': EmptyState,
		'no-results': NoResults
	},

	mixins: [ autocompleteLookupHandler ],

	data: function () {
		return {
			currentTab: url.query.type || '',

			// Object with keys corresponding to each tab;
			// values are integers; set in the created() hook
			autoloadCounter: {},

			// Temporary feature flag for Concept Chips. To enable, add
			// ?conceptchips=true to the URL.
			enableConceptChips: !!url.query.conceptchips
		};
	},

	computed: $.extend( {}, mapState( [
		'term',
		'results',
		'continue',
		'pending',
		'relatedConcepts'
	] ), mapGetters( [
		'hasMore'
	] ), {

		/**
		 * @return {string[]} [ 'bitmap', 'video', 'audio', 'page', 'other' ]
		 */
		tabs: function () {
			return Object.keys( this.results );
		},

		/**
		 * @return {Object} { bitmap: 'Images', video: 'Video', page: 'Categories and Pages'... }
		 */
		tabNames: function () {
			var names = {},
				prefix = 'wikibasemediainfo-special-mediasearch-tab-';

			// Get the i18n message for each tab title and assign to appropriate
			// key in returned object
			this.tabs.forEach( function ( tab ) {
				names[ tab ] = this.$i18n( prefix + tab ).text();
			}.bind( this ) );

			return names;
		},

		/**
		 * Whether to show the pre-search empty state; show this whenever a
		 * search term is not present and there are no results to display
		 *
		 * @return {boolean}
		 */
		shouldShowEmptyState: function () {
			return this.term.length === 0 && this.results[ this.currentTab ] &&
				this.results[ this.currentTab ].length === 0;
		},

		supportsObserver: function () {
			return 'IntersectionObserver' in window &&
				'IntersectionObserverEntry' in window &&
				'intersectionRatio' in window.IntersectionObserverEntry.prototype;
		}
	} ),

	methods: $.extend( {}, mapMutations( [
		'resetFilters',
		'resetResults',
		'clearRelatedConcepts',
		'setTerm',
		'setPending'
	] ), mapActions( [
		'search',
		'getRelatedConcepts',
		'clear'
	] ), {
		/**
		 * Keep UI state, URL, and history in sync as the user changes tabs
		 *
		 * @param {Object} newTab
		 * @param {string} newTab.name
		 */
		onTabChange: function ( newTab ) {
			this.currentTab = newTab.name;
			url.query.type = newTab.name;
			window.history.pushState( url.query, null, '?' + url.getQueryString() );
		},

		/**
		 * @param {string} tab bitmap, video, etc
		 */
		onFilterChange: function ( tab ) {
			this.resetResults( tab );
			this.resetCountAndLoadMore( tab );
			this.$refs[ tab ][ 0 ].hideDetails();
		},

		/**
		 * Keep the UI state, URL, and history in sync as the user changes
		 * search queries
		 *
		 * @param {string} newTerm
		 */
		onUpdateTerm: function ( newTerm ) {
			this.setTerm( newTerm );
			url.query.q = newTerm;
			window.history.pushState( url.query, null, '?' + url.getQueryString() );
		},

		/**
		 * Dispatch Vuex actions to clear existing term and results whenever a
		 * "clear" event is detected. Update the URL and history as well.
		 * Also resets the autoload counter to clear any "load more" buttons
		 * that may have previously been visible in any of the tabs.
		 */
		onClear: function () {
			this.clear( this.currentTab );
			this.clearLookupResults();
			this.setPending( { type: this.currentTab, pending: false } );

			url.query.q = '';
			window.history.pushState( url.query, null, '?' + url.getQueryString() );
			this.autoloadCounter = this.setInitialAutoloadCountForTabs();
		},

		/**
		 * @param {PopStateEvent} e
		 * @param {Object} [e.state]
		 */
		onPopState: function ( e ) {
			// If the newly-active history entry includes a state object, use it
			// to reset the URL query params and the UI state
			if ( e.state ) {
				this.setTerm( e.state.q || '' );
				this.currentTab = e.state.type;

				// if the newly-active history entry includes a "null" query
				// (the result of clicking the clear button, for example),
				// ensure that the results are reset
				if ( this.term === '' ) {
					this.resetResults();
					this.clearRelatedConcepts();
					this.clearLookupResults();
				}

				// Also update the mw.Uri object since we use it to generate
				// future states
				url.query.q = this.term;
				url.query.type = this.currentTab;
			}
		},

		/**
		 * @param {string} tab bitmap, audio, etc.
		 */
		getMoreResultsForTabIfAvailable: function ( tab ) {
			// Don't make API requests if the search term is empty
			if ( this.term === '' ) {
				return;
			}

			if ( this.hasMore[ tab ] && !this.pending[ tab ] ) {
				// Decrement the autoload count of the appropriate tab
				this.autoloadCounter[ tab ]--;

				// If more results are available, and if another request is not
				// already pending, then launch a search request
				this.search( {
					term: this.term,
					type: this.currentTab
				} );
			} else if ( this.hasMore[ tab ] && this.pending[ tab ] ) {
				// If more results are available but another request is
				// currently in-flight, attempt to make the request again
				// after some time has passed
				window.setTimeout(
					this.getMoreResultsForTabIfAvailable.bind( this, tab ),
					2000
				);
			}
		},

		resetCountAndLoadMore: function ( tab ) {
			// Don't make API requests if the search term is empty
			if ( this.term === '' ) {
				return;
			}

			// Reset the autoload count for the given tab
			this.autoloadCounter[ tab ] = AUTOLOAD_COUNT;

			// Launch a search request
			this.search( {
				term: this.term,
				type: this.currentTab
			} );
		},

		/**
		 * Dispatch Vuex actions to clear existing results and fetch new ones.
		 * Also resets the autoload counter for all tabs for semi-infinite
		 * scroll behavior.
		 */
		performNewSearch: function () {
			this.resetResults();
			this.clearRelatedConcepts();
			this.autoloadCounter = this.setInitialAutoloadCountForTabs();

			// Abort in-flight lookup promises to ensure the results provided
			// are for the most recent search input.
			if ( this.lookupPromises ) {
				this.lookupPromises.abort();
			}

			this.search( {
				term: this.term,
				type: this.currentTab
			} );
		},

		/**
		 * Determine if a given tab should display a "no results found" message
		 *
		 * @param {string} tab
		 * @return {boolean}
		 *
		 */
		hasNoResults: function ( tab ) {
			return this.term.length > 0 && // user has entered a search term
				this.pending[ tab ] === false && // tab is not pending
				this.results[ tab ].length === 0 && // tab has no results
				this.continue[ tab ] === null; // query cannot be continued
		},

		/**
		 * @return {Object} counter object broken down by tab name
		 */
		setInitialAutoloadCountForTabs: function () {
			var count = {};

			this.tabs.forEach( function ( tabName ) {
				count[ tabName ] = AUTOLOAD_COUNT;
			} );

			return count;
		}
	} ),

	watch: {
		/**
		 * When the currentTab changes, fetch more results for the new tab if
		 * available
		 *
		 * @param {string} newTab bitmap, audio, etc.
		 * @param {string} oldTab bitmap, audio, etc.
		 */
		currentTab: function ( newTab, oldTab ) {
			if ( newTab && newTab !== oldTab ) {
				this.getMoreResultsForTabIfAvailable( newTab );

				if ( this.enableConceptChips && newTab === 'bitmap' && this.relatedConcepts.length < 1 ) {
					this.getRelatedConcepts( this.term );
				}
			}

		},

		/**
		 * If the new term does not match what previously existed here, perform
		 * a new search.
		 *
		 * @param {string} newTerm
		 * @param {string} oldTerm
		 */
		term: function ( newTerm, oldTerm ) {
			if ( newTerm && newTerm !== oldTerm ) {
				this.performNewSearch();

				if ( this.enableConceptChips && this.currentTab === 'bitmap' ) {
					this.getRelatedConcepts( newTerm );
				}
			}
		}
	},

	created: function () {
		// If user arrives on the page without URL params to specify initial search
		// type / active tab, default to bitmap. This is done in created hook
		// because some computed properties assume that a currentTab will always be
		// specified; the created hook runs before computed properties are evaluated.
		if ( this.currentTab === '' ) {
			this.currentTab = 'bitmap';
			url.query.type = 'bitmap';
		}

		// Record whatever the initial query params are that the user arrived on
		// the page with as "state" in the history stack using history.replaceState;
		// this will enable us to access it later if the user starts navigating
		// through history states
		window.history.replaceState( url.query, null, '?' + url.getQueryString() );

		// Set up a listener for popState events in case the user navigates
		// through their history stack. Previous search queries should be
		// re-created when this happens, and URL params and UI state should
		// remain in sync

		// First, create a bound handler function and reference it for later removal
		this.boundOnPopState = this.onPopState.bind( this );

		// Set up the event listener
		window.addEventListener( 'popstate', this.boundOnPopState );

		// Set the initial autoload count for all tabs for semi-infinite scroll
		// behavior
		this.autoloadCounter = this.setInitialAutoloadCountForTabs();

		// If a search term exists on page load, fetch related concepts for
		// concept chips.
		if ( this.enableConceptChips && this.term && this.currentTab === 'bitmap' ) {
			this.getRelatedConcepts( this.term );
		}
	},

	beforeDestroy: function () {
		window.removeEventListener( 'popstate', this.boundOnPopState );
	}
};
</script>
