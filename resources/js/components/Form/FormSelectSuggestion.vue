<template>
	<div class="form-group row">
		<label
			v-bind:for="searchTermElementId"
			v-html="label"
			class="col-sm-4 col-form-label"
			v-bind:class="searchTermLabelElementCssClassObject"
			v-on:click.stop />

		<div class="col-sm-8">
			<input
				type="text"
				class="form-control"
				autocomplete="false"
				placeholder="Start typing to search…"
				key="searchTerm"
				v-if="!internalValue"
				v-bind:class="searchTermElementCssClassObject"
				v-bind:id="searchTermElementId"
				v-model="searchTerm"
				v-on:click.stop
				v-on:blur="onSearchTermBlur"
				v-on:focus="onSearchTermFocus"
				v-on:keydown.enter="onSearchTermEnter"
				v-on:keydown.esc="resetSearch"
				v-on:keydown.down="selectNextSearchResult()"
				v-on:keydown.up="selectPreviousSearchResult()" />

			<div
				v-if="isInvalid"
				class="invalid-feedback">
				Please select a valid option from the suggested search results
			</div>

			<div
				v-if="internalValue"
				class="input-group">
				<input
					type="text"
					class="form-control"
					readonly
					key="internalValue"
					v-bind:value="internalValue"
					v-bind:name="name"
					v-on:keyup.delete="resetSearch" />
				<div class="input-group-append">
					<button
						type="button"
						class="btn btn-outline-secondary"
						aria-label="Close"
						v-on:click="resetSearch">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
			</div>

			<div
				v-if="shouldShowPrompt && isFocused"
				class="search-results bg-white rounded shadow-lg p-3 text-muted">
				<em>Continue typing to narrow down results&hellip;</em>
			</div>

			<div
				v-if="shouldShowNoResultsNotice && isFocused"
				class="search-results bg-danger rounded shadow-lg p-3 text-white">
				<strong>No results found.</strong> Please try another keyword.
			</div>

			<div
				v-if="searchResults.length && isFocused"
				class="search-results bg-white rounded shadow-lg"
				v-bind:id="searchResultsElementId"
				v-bind:style="searchResultsElementCssStyleObject">
				<table class="table table-sm table-hover mb-0">
					<thead class="thead-dark">
						<tr class="search-results__table__row">
							<th
								v-for="(columnName, i) in resultsColumnNames"
								v-bind:key="i">
								{{ prettify(columnName) }}
							</th>
						</tr>
					</thead>
					<tbody>
						<tr
							class="search-results__table__row search-results__table__row--body"
							v-bind:class="searchResultRowCssClassObject(i)"
							v-for="(row, i) in searchResults"
							v-bind:key="i"
							v-on:click.stop="selectSearchResultByIndex(i)">
							<td
								v-for="(column, j) in row"
								v-bind:key="j"
								v-html="parseColumnText(column)">
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</template>

<script>
import axios from 'axios';
import { debounce } from 'throttle-debounce';
import { escapeHtml } from '~/framework/utils/string.utils';

let count = 0;

export default {
	name: 'FormSelectSuggestion',

	props: {
		label: {
			type: String,
			required: true,
		},
		allowedValues: {
			type: Array,
			default: () => [],
		},
		endpoint: {
			type: String,
			required: true,
		},
		hasPlaceholder: {
			type: Boolean,
			default: true,
		},
		name: {
			type: String,
			required: true,
		},
		isRequired: {
			type: Boolean,
			default: false,
		},
		project: {
			type: String,
			required: true,
		},
	},

	data() {
		return {
			keywordLengthThreshold: 1,
			internalValue: '',
			keyword: '',
			searchResults: [],
			selectedSearchResultIndex: null,
			searchResultsXAlignment: 'left',
			searchResultsElementMaxHeight: null,
			searchResultsElementRight: null,
			searchResultsElementTranslateY: 0,
			shouldShowNoResultsNotice: false,
			viewportScrollPosition: 0,
			isFocused: false,
			isInvalid: false,
		};
	},

	watch: {
		internalValue (newInternalValue) {
			this.$emit('input', {
				name: this.name,
				value: newInternalValue.trim(),
			});
		},
		searchTerm (value) {
			if (this.searchTerm.length < this.keywordLengthThreshold) {
				this.shouldShowNoResultsNotice = false;
			}
		},
		isFocused (value) {
			if (value) {
				this.updateSearchResultsLayout();
			}
		},
		searchResults (value) {
			if (value) {
				this.updateSearchResultsLayout();
			}
		}
	},

	methods: {
		updateSearchResultsLayout: async function () {
			await this.$nextTick();

			const searchResultsElement = document.getElementById(this.searchResultsElementId);
			const searchTermElement = document.getElementById(this.searchTermElementId);

			if (!searchResultsElement || !searchTermElement) {
				return;
			}

			const searchResultsElementRect = searchResultsElement.getBoundingClientRect();
			const searchTermElementRect = searchTermElement.getBoundingClientRect();

			if (this.searchResultsXAlignment === 'right') {
				this.searchResultsElementRight = `${window.innerWidth - searchTermElementRect.right}px`;
			}

			this.searchResultsElementMaxHeight = `${window.innerHeight - searchTermElementRect.bottom - 32}px`;
		},
		onSearchTermBlur () {
			window.setTimeout(() => {
				this.isFocused = false;
				this.isInvalid = !this.internalValue && this.searchTerm;
			}, 250);

			// If there's only one result we just preselect it
			if (this.searchResults.length === 1) {
				this.selectSearchResultByIndex(0);
				return;
			}
		},
		onSearchTermFocus (e) {
			this.isFocused = true;
			this.isInvalid = false;

			const { top, left, width, height } = e.target.getBoundingClientRect();
			this.searchResultsElementTranslateY = top + height + window.scrollY;

			const centerX = left + 0.5 * width;
			this.searchResultsXAlignment = centerX > (window.innerWidth * 0.5) ? 'right' : 'left';
		},
		selectSearchResultByIndex (index) {
			this.internalValue = this.searchResults[index][this.name].toString();
			this.searchResults = [];
			this.isInvalid = !this.allowedValues.map(v => v.value.toString()).includes(this.internalValue);
		},
		onSearchTermEnter () {
			if (this.searchResults.length) {
				const index = Math.min(this.selectedSearchResultIndex || 0, this.searchResults.length - 1);
				this.selectSearchResultByIndex(index);
			}
    },
		prettify (columnName) {
			return columnName.replace(/_/gi, ' ');
		},
		parseColumnText (value) {
			// Force escape HTML coming from DB
			value = escapeHtml(value);

			if (value !== null) {
				const words = this.searchTerm.trim().split(' ');
				words.forEach(word => {
					const pattern = new RegExp(`(${word})`, 'gi');
					value = value.toString().replace(pattern, '<mark>$1</mark>');
				});
			} else {
				value = '–';
			}
			
			return value;
		},
		resetSearch () {
			this.searchResults = [];
			this.searchTerm = '';
			this.internalValue = '';
			this.selectedSearchResultIndex = null;
			this.shouldShowNoResultsNotice = false;
			this.isInvalid = false;
		},
		selectNextSearchResult () {
			if (this.selectedSearchResultIndex === null) {
				this.selectedSearchResultIndex = 0;
			} else {
				this.selectedSearchResultIndex = Math.min(this.searchResults.length - 1, this.selectedSearchResultIndex + 1);
			}

			this.scrollToSelectedSearchResult();
		},
		selectPreviousSearchResult () {
			if (this.selectedSearchResultIndex === null) {
				this.selectedSearchResultIndex = 0;
			} else {
				this.selectedSearchResultIndex = Math.max(0, this.selectedSearchResultIndex - 1);
			}

			this.scrollToSelectedSearchResult();
		},
		scrollToSelectedSearchResult () {
			const el = document.querySelector(`.search-results__table__row--body:nth-child(${this.selectedSearchResultIndex + 1})`);
			const parent = document.getElementById(this.searchResultsElementId);

			if (!el || !parent) {
				return;
			}

			const thead = parent.querySelector('thead');

			const elRect = el.getBoundingClientRect();
			const parentRect = parent.getBoundingClientRect();

			if (elRect.bottom < parentRect.bottom && elRect.top > parentRect.top) {
				return;
			}

			parent.scrollTo(0, elRect.top - parentRect.top - thead.getBoundingClientRect().height)
		},
		searchResultRowCssClassObject (index) {
			return {
				'table-active': index === this.selectedSearchResultIndex || this.searchResults.length === 1,
			};
		},
		updateSearchResult: debounce(250, async function(keyword) {
			const { data } = await axios.get(`/api/data/field-trial-search-by-column?column=${this.name}&keyword=${keyword.trim()}&project=${this.project}`);
			this.searchResults = data;

			this.shouldShowNoResultsNotice = !data.length;
		}),
		validate () {
			if (!this.isRequired) {
				return true;
			}

			this.isInvalid = !(this.isRequired && !!this.internalValue);
			return this.isRequired && !!this.internalValue;
		},
		onViewportScroll () {
			this.viewportScrollPosition = window.scrollY;
		}
	},

	computed: {
		searchTermElementId () {
			return `form-selection-suggestion-${count++}`;
		},
		searchResultsElementId () {
			return `form-selection-suggestion-results-${count++}`;
		},
		searchTerm: {
			get: function() {
				return this.keyword;
			},
			set: function(keyword) {
				this.keyword = keyword;
				if (this.keyword.length < this.keywordLengthThreshold) {
					this.searchResults = [];
					return;
				}

				this.updateSearchResult(keyword);
			}
		},
		resultsColumnNames () {
			if (!this.searchResults.length) {
				return [];
			}

			return Object.keys(this.searchResults[0]);
		},
		shouldShowPrompt () {
			return !this.searchResults.length && this.searchTerm.length < this.keywordLengthThreshold && this.searchTerm.length > 0;
		},
		searchTermLabelElementCssClassObject () {
			return {
				'text-danger': this.isInvalid,
			};
		},
		searchTermElementCssClassObject () {
			return {
				'is-invalid': this.isInvalid,
			};
		},
		searchResultsElementCssStyleObject () {
			return {
				maxHeight: this.searchResultsElementMaxHeight,
				right: this.searchResultsElementRight,
				transform: `translateY(${this.searchResultsElementTranslateY - this.viewportScrollPosition}px)`,
			}
    }
	},

	mounted () {
		window.addEventListener('scroll', this.onViewportScroll);
		document.addEventListener('click', this.resetSearch);
	},
  
	beforeDestroy () {
		window.removeEventListener('scroll', this.onViewportScroll);
		document.removeEventListener('click', this.resetSearch);
	}
}
</script>

<style lang="scss" scoped>
.search-results {
	position: fixed;
	z-index: 999;
	top: 0;
	font-size: .8em;

	overflow-y: auto;
}

.search-results__table__row {
	th:first-child,
	td:first-child {
		padding-left: 1em;
	}

	th:last-child,
	td:last-child {
		padding-right: 1em;
	}
}

.search-results__table__row--body {
	cursor: pointer;
}
</style>