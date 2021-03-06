<template>
	<div class="wbmi-media-search-filters-wrapper" :class="rootClasses">
		<div class="wbmi-media-search-filters">
			<template v-for="( filter, index ) in searchFilters">
				<wbmi-select
					ref="filters"
					:key="'filter-' + index"
					:class="getFilterClasses( filter.type )"
					:name="filter.type"
					:items="filter.items"
					:initial-selected-item-index="0"
					:prefix="getFilterPrefix( filter.type )"
					@select="onSelect( $event, filter.type )"
				>
				</wbmi-select>
				<wbmi-observer
					v-if="index === searchFilters.length - 1 && supportsObserver"
					:key="'filter-observer-' + index"
					@intersect="removeGradientClass"
					@hide="addGradientClass"
				></wbmi-observer>
			</template>
		</div>
	</div>
</template>

<script>
/**
 * @file SearchFilters.vue
 *
 * Container for the search filters for a tab. Displays the filters and handles
 * change in filter value. When a filter value changes, the Vuex state is
 * updated with the new filter value, and a new-search event is emitted so the
 * parent App component can dispatch the search action.
 */
var mapState = require( 'vuex' ).mapState,
	mapMutations = require( 'vuex' ).mapMutations,
	WbmiSelect = require( './base/Select.vue' ),
	WbmiObserver = require( './base/Observer.vue' ),
	SearchFilter = require( '../models/SearchFilter.js' ),
	filterItems = require( './../data/filterItems.json' ),
	sortFilterItems = require( './../data/sortFilterItems.json' );

// @vue/component
module.exports = {
	name: 'SearchFilters',

	components: {
		'wbmi-select': WbmiSelect,
		'wbmi-observer': WbmiObserver
	},

	props: {
		mediaType: {
			type: String,
			required: true
		}
	},

	data: function () {
		return {
			hasGradient: false
		};
	},

	computed: $.extend( {}, mapState( [
		'filterValues'
	] ), {
		/**
		 * @return {Object}
		 */
		rootClasses: function () {
			return {
				'wbmi-media-search-filters-wrapper--gradient': this.hasGradient
			};
		},

		/**
		 * @return {Array} SearchFilter objects for this media type.
		 */
		searchFilters: function () {
			var filtersArray = [],
				filterKey,
				newFilter,
				sortFilter = new SearchFilter( 'sort', sortFilterItems );

			for ( filterKey in filterItems[ this.mediaType ] ) {
				newFilter = new SearchFilter(
					filterKey,
					filterItems[ this.mediaType ][ filterKey ]
				);
				filtersArray.push( newFilter );
			}

			// All media types use the sort filter.
			filtersArray.push( sortFilter );
			return filtersArray;
		},

		/**
		 * Key names (not values) of all active filters for the given tab;
		 * Having a shorthand computed property for this makes it easier to
		 * watch for changes.
		 *
		 * @return {Array} Empty array or [ "imageSize", "mimeType" ], etc
		 */
		currentActiveFilters: function () {
			return Object.keys( this.filterValues[ this.mediaType ] );
		},

		supportsObserver: function () {
			return 'IntersectionObserver' in window &&
				'IntersectionObserverEntry' in window &&
				'intersectionRatio' in window.IntersectionObserverEntry.prototype;
		}
	} ),

	methods: $.extend( {}, mapMutations( [
		'addFilterValue',
		'removeFilterValue'
	] ), {
		/**
		 * Handle filter change.
		 *
		 * @param {string} value The new filter value
		 * @param {string} filterType
		 * @fires filter-change
		 */
		onSelect: function ( value, filterType ) {
			if ( value ) {
				this.addFilterValue( {
					value: value,
					mediaType: this.mediaType,
					filterType: filterType
				} );
			} else {
				this.removeFilterValue( {
					mediaType: this.mediaType,
					filterType: filterType
				} );
			}

			// Tell the App component to do a new search.
			this.$emit( 'filter-change' );
		},

		/**
		 * We need a class for select lists where a non-default item is selected.
		 *
		 * @param {string} filterType
		 * @return {Object}
		 */
		getFilterClasses: function ( filterType ) {
			return {
				'wbmi-search-filter--selected': this.currentActiveFilters.indexOf( filterType ) !== -1
			};
		},

		/**
		 * Add select list prefixes per filter type.
		 *
		 * @param {string} filterType
		 * @return {string}
		 */
		getFilterPrefix: function ( filterType ) {
			if ( filterType === 'sort' ) {
				return this.$i18n( 'wikibasemediainfo-special-mediasearch-filter-sort-label' );
			}

			return '';
		},

		/**
		 * When final filter is out of view, add class that will add a gradient
		 * to indicate to the user that they can horizontally scroll.
		 */
		addGradientClass: function () {
			this.hasGradient = true;
		},

		/**
		 * When final filter is in view, don't show the gradient.
		 */
		removeGradientClass: function () {
			this.hasGradient = false;
		}
	} ),

	watch: {
		/**
		 * Watch for changes in active filters (regardless of value) so that we
		 * can re-set the Select components to initial values if filters are
		 * cleared via a Vuex action.
		 *
		 * @param {Array} newValue
		 * @param {Array} oldValue
		 */
		currentActiveFilters: function ( newValue, oldValue ) {
			// If we are going from one or more active filters to no filters,
			// then forcibly reset any filter components to their initial state
			// in case that change comes from a Vuex "clear" action rather than
			// the user clicking around.
			if ( oldValue.length > 0 && newValue.length === 0 ) {
				this.$refs.filters.forEach( function ( filter ) {
					filter.reset();
				} );
			}
		}
	}
};
</script>
