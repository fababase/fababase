<template>
	<form
		action="/api/data/field-trial-data-download"
		type="GET"
		class="col-sm-6 card-deck"
		v-on:submit.prevent="onDownloadInitiated">
		<div class="card mb-3">
			<div class="card-body">
				<h2 class="card-title h4">{{ title }}</h2>
				<p
					v-if="description"
					class="card-text">
					{{ description }}
				</p>
				
				<!-- Dynamically generated fields -->
				<component
					ref="formField"
					v-for="(field, i) in fields"
					v-bind:is="field.component"
					v-bind:key="i"
					v-bind="fieldProps(field)"
					v-bind:project="project"
					v-bind:isRequired="field.required"
					v-on:input="onFieldInput"/>

				<!-- Table list -->
				<template v-if="tables && !!tables.length">
					<hr class="mt-4" />
					<p class="card-text mb-1"><small class="text-muted">Tables used in query:</small></p>
					<ul class="list-unstyled d-flex flex-row m-n1">
						<li
							v-for="(table, i) in sortedTables"
							v-bind:key="i"
							class="m-1">
							<span class="badge badge-dark"><component v-bind:is="getTableFullName(table) ? 'abbr' : 'span'" v-bind:title="getTableFullName(table)">{{ table }}</component></span>
						</li>
					</ul>
				</template>

				<!-- Static hidden field that stores the id of the formula -->
				<input
					type="hidden"
					name="formula"
					v-bind:value="id" />

				<!-- Static hidden field that indiciates if the server will stream a static file -->
				<input
					type="hidden"
					name="isfile"
					v-bind:value="isStaticFile ? 1 : 0" />

				<!-- Static hidden field that stores the id of the formula -->
				<input
					type="hidden"
					name="project"
					v-bind:value="project" />
			</div>
			<div class="card-footer">
				<button
					type="submit"
					class="btn btn-light"
					v-on:click.stop>
					Download
				</button>
			</div>
		</div>
	</form>
</template>

<script>
import { mapGetters } from 'vuex';
import FormSelect from '~/components/Form/FormSelect';
import FormSelectSuggestion from '~/components/Form/FormSelectSuggestion';

export default {
	name: 'FieldDataFormula',
	components: {
		FormSelect,
		FormSelectSuggestion,
	},

	props: {
		id: {
			type: String,
			required: true,
		},
		title: {
			type: String,
			required: true,
		},
		description: {
			type: String,
			default: '',
		},
		fields: {
			type: Array,
			default() {
				return [];
			},
		},
		tables: {
			type: Array,
			default() {
				return [];
			}
		},
		isStaticFile: {
			type: Boolean,
			default: false,
		},
		project: {
			type: String,
			required: true,
		},
	},

	methods: {
		fieldProps(field) {
			switch (field.component) {
				case 'FormSelect':
					return {
						label: field.label,
						name: field.name,
						options: field.options,
					};
				case 'FormSelectSuggestion':
					return {
						label: field.label,
						name: field.name,
						allowedValues: field.options,
						endpoint: field.endpoint,
					};
			}
		},
		onFieldInput(data) {
			const { name, value } = data;
			const index = this.fields.findIndex((field) => field.name === name);
			
			this.fields[index].value = value;
		},
		getTableFullName(table) {
			switch (table) {
				case 'CL':
					return 'Climate';
				case 'GP':
					return 'Germplasm';
				case 'GT':
					return 'Genotype';
				case 'MN':
					return 'Management';
				case 'MP':
					return 'Map';
				case 'PD':
					return 'Phenotype data';
				case 'PH':
					return 'Phenotype';
				case 'PL':
					return 'Plot';
				case 'SL':
					return 'Seed lot';
				case 'SN':
					return 'SNP information';
				case 'TR':
					return 'Trial';
				default:
					return '';
			}
		},
		onDownloadInitiated () {
			const validationResults = (this.$refs.formField && this.$refs.formField.length) ? this.$refs.formField.map((field) => field.validate()) : [];
			
			if (validationResults.every(results => !!results)) {
				this.$el.submit();
			}
		},
		getDropdownButtonId(id) {
			return `project-selection--${id}`;
		}
	},

	computed: {
		...mapGetters({
			user: 'auth/user'
		}),
		sortedTables() {
			return this.tables.sort();
		},
		projectId() {
			if (this.user.is_norfab_user)
				return 'norfab';
			else if (this.user.is_profaba_user)
				return 'profabba';
			else
				return '';
		},
	}
}
</script>
