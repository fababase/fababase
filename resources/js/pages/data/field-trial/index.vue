<template>
	<main role="main">
		<div class="jumbotron">
			<div class="container">
				<h1 class="display-4">Field trial data</h1>
				<p class="lead">
					Retrieve data for field trial data for Faba beans.
					<router-link :to="{ name: 'field-trial-db-schema' }">More information is available on the database schema</router-link>.
					You can also download the entire dataset below, instead of constructing an advanced query:
				</p>
				<a
					class="btn btn-primary btn-lg"
					href="/api/data/field-trial-data-download-all"
					target="_blank"
					role="button">
					Download all
				</a>
			</div>
		</div>

		<div class="container">
			<div class="row">
				<FieldDataFormula
					v-for="(formula, i) in formulas"
					v-bind="formula"
					v-bind:key="i" />
			</div>
		</div>
	</main>
</template>

<script>
import axios from "axios";
import FieldDataFormula from '~/components/FieldDataFormula';

export default {
	middleware: "can-read-field-trial-data",
	components: {
		FieldDataFormula,
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
	},

	watch: {
		columns() {
			this.columns.forEach(async (column) => {
			try {
				const { data } = await axios.get(`/api/data/field-trial-allowed-values?column=${column.name}`);
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
					//   component: 'FormSelect',
					//   label: 'Trial ID',
					//   name: 'TRID',
					//   options: [],
					//   value: '',
					//   required: true,
					// }
					],
					tables: ['PH','PD','PL','TR','GP'],
				},
			],
		};
	}
};
</script>