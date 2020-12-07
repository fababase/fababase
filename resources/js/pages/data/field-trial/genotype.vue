<template>
	<main role="main">
		<div class="jumbotron">
			<div class="container">
				<h1 class="display-4">Genotype data</h1>
			</div>
		</div>

		<div class="container">
			<div class="row">
				<em>TBA</em>
			</div>
		</div>
	</main>
</template>

<script>
import axios from "axios";
import { mapGetters } from 'vuex'

export default {
	middleware: "can-read-field-trial-data",
	
	computed: {
		...mapGetters({
			user: 'auth/user'
		}),
		isInMultipleUserGroups() {
			return (this.user.is_norfab_user && this.user.is_profaba_user) || this.user.is_admin;
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
		return { title: "Genotyping data" };
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
	}
};
</script>