<template>
	<main role="main">
		<div class="jumbotron">
			<div class="container">
				<h1 class="display-4">Genotyping data</h1>
				<p class="lead">
					Retrieve data for genotyping data for faba beans.
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
							<option value="profaba">ProFaba</option>
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
					class="btn btn-secondary"
					v-bind:class="{ 'disabled': !downloadRawGenotypeFileSize }"
					v-bind:href="downloadRawGenotypeButtonHref"
					target="_blank"
					role="button">
					<fa icon="dna" fixed-width />
					Download raw genotype data {{downloadRawGenotypeFileSizeText}}
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
import axios from 'axios';
import { mapGetters } from 'vuex';
import FieldDataFormula from '~/components/FieldDataFormula';
import { humanFileSize } from '~/framework/utils/string.utils';

export default {
	middleware: "can-read-field-trial-data",
	components: {
		FieldDataFormula,
	},

	data: function () {
		return {
			columns: [],
			project: '',
			downloadRawGenotypeFileSize: 0,
			formulas: [
				// RECIPE: Get genotypes by map name
				{
					id: 'get-genotypes-without-mapping-data',
					title: 'Without mapping data',
					description: 'Retrieves genotyping data without mapping data.',
					fields: [],
					isStaticFile: true,
					tables: ['GP','GT','SL','SN'],
				},

				// RECIPE: Get genotypes by map name
				{
					id: 'get-genotypes-by-map-name',
					title: 'By map name',
					description: 'Retrieves genotyping data with mapping data for a given map name.',
					fields: [
					{
						component: 'FormSelectSuggestion',
						label: 'Map name',
						name: 'MapName',
						options: [],
						value: '',
						required: true,
						endpoint: '/api/data/field-trial-search-by-column',
					},
					],
					isStaticFile: true,
					tables: ['GP','GT','MP','SL','SN'],
				},
			]
		};
	},
	
	computed: {
		...mapGetters({
			user: 'auth/user'
		}),
		isInMultipleUserGroups() {
			return (this.user.is_norfab_user && this.user.is_profaba_user) || this.user.is_admin;
		},
		downloadRawGenotypeButtonHref() {
			return `/api/data/field-trial-data-download-raw-genotype?project=${this.project}`;
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
		},
		downloadRawGenotypeFileSizeText() {
			return this.downloadRawGenotypeFileSize ? ` [${humanFileSize(this.downloadRawGenotypeFileSize, true)}]` : ' [Unavailable]';
		}
	},

	metaInfo() {
		return { title: "Genotyping data" };
	},

	watch: {
		columns() {
			this.populateAllowedValues();
		},
		project() {
			this.populateAllowedValues();

			try {
				axios.get(`/api/data/field-trial-data-get-download-genotype-file-size?project=${this.project}`)
					.then(resp => this.downloadRawGenotypeFileSize = resp.data.fileSize);
			} catch(e) {
				console.warn(`Unable to fetch file size: ${e}`);
			}
		}
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
};
</script>