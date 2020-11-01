<template>
	<main role="main">
		<div class="jumbotron">
			<div class="container">
				<h1 class="display-4">Field trial data</h1>
				<p class="lead">
					Retrieve data for field trial data for faba beans.
					<router-link :to="{ name: 'field-trial-db-schema' }">More information is available on the database schema</router-link>.
				</p>

				<p v-if="isInMultipleUserGroups">
					You have access to multiple projects&mdash;please select which dataset you want to retrieve data from:
				</p>
				
				<div
					v-if="isInMultipleUserGroups"
					class="form-inline">
					<div class="form-group">
						<select
							id="project"
							class="custom-select"
							aria-describedby="projectHelpText"
							v-model="project">
							<option selected disabled value="">Select a project</option>
							<option value="norfab">NorFab</option>
							<option value="profaba" disabled>ProFaba [COMING SOON]</option>
						</select>
						<small id="projectHelpText" class="form-text text-muted ml-3">
							This also applies to the formulas further down this page.
						</small>
					</div>
				</div>

				<hr class="my-4" />

				<p>
					You can also download the entire dataset below, instead of constructing an advanced query:
				</p>

				<a
					class="btn btn-primary"
					v-bind:href="downloadButtonHref"
					target="_blank"
					role="button">
					Download full dataset
					<template v-if="projectName">from the {{ projectName }} project</template>
				</a>
			</div>
		</div>

		<div class="container">
			<div class="row">
				<FieldDataFormula
					v-for="(formula, i) in formulas"
					v-bind="formula"
					v-bind:project="project"
					v-bind:key="i" />
			</div>
		</div>
	</main>
</template>

<script>
import axios from "axios";
import { mapGetters } from 'vuex'
import FieldDataFormula from '~/components/FieldDataFormula';

export default {
	middleware: "can-read-field-trial-data",
	components: {
		FieldDataFormula,
	},
	
	computed: {
		...mapGetters({
			user: 'auth/user'
		}),
		isInMultipleUserGroups() {
			return (this.user.is_norfab_user && this.user.is_profaba_user) || this.user.is_admin;
		},
		downloadButtonHref() {
			return `/api/data/field-trial-data-download-all?project=${this.project}`;
		},
		projectName() {
			switch (this.project) {
				case 'norfab':
					return 'NorFab';
				case 'profaba':
					return 'ProFaba';
				default:
					return '';
			}
		}
	},

	metaInfo() {
		return { title: "Phenotyping data" };
	},

	mounted() {
		this.formulas.forEach((formula) => {
			formula.fields.forEach((field) => {
				const columnNames = this.columns.map((column) => column.name);
				const foundColumnNameIndex = columnNames.findIndex((columnName) => columnName === field.name);

				if (foundColumnNameIndex === -1) {
				this.columns.push({
					name: field.name,
					values: [],
				});
				}
			});
		});
		
		if (this.user.is_norfab_user) {
			this.project = 'norfab';
		} else if (this.user.is_profaba_user) {
			this.project = 'profaba';
		} else {
			this.project = '';
		}
	},

	watch: {
		columns() {
			this.populateAllowedValues();
		},
		project() {
			this.populateAllowedValues();
		}
	},
	
	methods: {
		populateAllowedValues() {
			this.columns.forEach(async (column) => {
				try {
					const { data } = await axios.get(`/api/data/field-trial-allowed-values?column=${column.name}&project=${this.project}`);
					column.values = data;

					this.formulas.forEach((formula) => {
					formula.fields.forEach((field) => {
						field.options = this.columns.find((column) => column.name === field.name)['values'].map((value) => {
						return { value };
						});
					});
					});
				} catch(e) {
					console.warn(`[field-trial] Unable to retrieve data for ${column.name}`);
				}
			});
		}
	},

	data() {
		return {
			columns: [],
			project: '',
			formulas: [
				{
					id: 'get-all-trial-data-by-phenotype',
					title: 'Get all trial data by phenotype',
					description: 'Retrieves all trial data for a given phenotype.',
					fields: [
					{
						component: 'FormSelectSuggestion',
						label: '<abbr title="Phenotype description">PD</abbr> ID',
						name: 'PDID',
						options: [],
						value: '',
						required: true,
						endpoint: '/api/data/field-trial-search-by-column',
					},
					],
					tables: ['PH','PD','PL','TR'],
				},
				{
					id: 'get-all-phenotypes-scored-by-trial',
					title: 'Get all phenotypes scored by trial',
					description: 'Retrieves all phenotype scores for a given trial.',
					fields: [
					{
						component: 'FormSelectSuggestion',
						label: 'Trial ID',
						name: 'TRID',
						options: [],
						value: '',
						required: true,
						endpoint: '/api/data/field-trial-search-by-column',
					},
					],
					tables: ['PH','PD','PL','TR'],
				},
				{
					id: 'get-phenotype-data-by-trial-and-trait',
					title: 'Phenotype data by trial & trait',
					description: 'Retrieves phenotype data for a given trial identifed by Trial ID (TRID) and phenotypig trait (PDID).',
					fields: [
					{
						component: 'FormSelectSuggestion',
						label: '<abbr title="Phenotype description">PD</abbr> ID',
						name: 'PDID',
						options: [],
						value: '',
						required: true,
						endpoint: '/api/data/field-trial-search-by-column',
					},
					{
						component: 'FormSelectSuggestion',
						label: 'Trial ID',
						name: 'TRID',
						options: [],
						value: '',
						required: true,
						endpoint: '/api/data/field-trial-search-by-column',
					},
					// {
					//	 component: 'FormSelect',
					//	 label: 'Trial ID',
					//	 name: 'TRID',
					//	 options: [],
					//	 value: '',
					//	 required: true,
					// }
					],
					tables: ['PH','PD','PL','TR','GP'],
				},
			],
		};
	}
};
</script>