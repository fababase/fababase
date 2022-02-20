<template>
  <main role="main">
		<div class="jumbotron jumbotron-fluid">
			<div class="container">
				<h1 class="display-4">Resources</h1>
      </div>
		</div>

		<div class="container">
			<div class="list-group">
				<a v-for="(resource, i) in filteredResources" v-bind:key="i" v-bind:href="resource.url" class="list-group-item list-group-item-action p-4">
					<h2 class="h4 mb-2">{{ resource.title }}</h2>
					<span v-if="resource.authors.length" class="d-block text-muted">
						<fa v-bind:icon="getAuthorsIcon(resource.authors.length)" size="sm" fixed-width />
						{{ getAuthors(resource.authors) }}
					</span>
					<p class="m-0 mt-3" v-if="resource.description">{{ resource.description }}</p>
				</a>
			</div>
		</div>
  </main>
</template>

<script>
import { mapGetters } from 'vuex';
import { textualJoin } from '~/framework/utils/array.utils';

export default {
  layout: 'default',

  metaInfo () {
    return { title: 'Resources' }
  },

  data: () => ({
    resources: [{
			title: 'Faba Base Genotype Converter',
			description: `A script that converts the raw output from "genotype data by map name"
into a generic genotype format as well as EMMAX and plink outputs useful for downstream analysis.`,
			url: 'https://github.com/cks2903/FabaBaseGenotypeConverter',
			authors: [{
				name: 'Cathrine Kiel Skovbjerg'
			}]
		}, {
			title: 'Seed images',
			description: '',
			url: 'https://figshare.com/s/d85788585f0e7ccc27a5',
			authors: [{
				name: 'Cathrine Kiel Skovbjerg'
			}]
		}, {
			title: 'Field plans',
			description: '',
			url: 'https://figshare.com/s/b6ca10b17d953a9a942f',
			authors: [{
				name: 'Cathrine Kiel Skovbjerg'
			}]
		}, {
			title: 'Relationship matrices',
			description: '',
			url: 'https://figshare.com/s/91d0f4b0c43bfb6c7f8b',
			authors: [{
				name: 'Cathrine Kiel Skovbjerg'
			}]
		}, {
			title: 'Other resources',
			description: '',
			url: 'https://figshare.com/s/2e7917fd572a66684b8e',
			authors: [{
				name: 'Cathrine Kiel Skovbjerg'
			}]
		}, {
			title: 'ProFaba VCF',
			description: '',
			url: '/api/resources/download?file=profaba/20220128_profaba_genotypes_imputed.vcf.gz',
			authors: [{
				name: 'Cathrine Kiel Skovbjerg'
			}],
			access: 'profaba'
		}, {
			title: 'NorFab VCF',
			description: '',
			url: '/api/resources/download?file=norfab/20220211_norfab_genotypes_imputed.vcf.gz',
			authors: [{
				name: 'Cathrine Kiel Skovbjerg'
			}],
			access: 'norfab'
		}]
	}),
	
	methods: {
		getAuthors(authors) {
			return textualJoin(authors.map(author => author.name));
		},
		getAuthorsIcon(count) {
			if (count === 1) {
				return 'user';
			} else if (count === 2) {
				return 'user-friends';
			} else {
				return 'users';
			}
		},
	},

	computed: {
		...mapGetters({
			user: 'auth/user'
		}),
		filteredResources() {
			return this.resources.filter(({ access }) => {
				switch (access) {
					case 'profaba':
						return this.user.is_profaba_user || this.user.is_admin;

					case 'norfab':
						return this.user.is_norfab_user || this.user.is_admin;

					default:
						return true;
				}
			});
		}
	}
}
</script>

<style scoped>

</style>
