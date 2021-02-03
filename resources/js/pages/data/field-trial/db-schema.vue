<template>
	<main role="main">
		<div class="jumbotron jumbotron-fluid">
			<div class="container">
				<h1 class="display-4">Database schema</h1>
				<p class="lead">
					An overview of the relational database used to store
					<router-link :to="{ name: 'field-trial-data' }">field trial</router-link> and
					<router-link :to="{ name: 'field-trial-genotype-data' }">genotype</router-link>
					data.
				</p>
			</div>
		</div>

		<div class="container">
			<ul class="nav nav-tabs">
				<li class="nav-item">
					<a class="nav-link" href="#" v-bind:class="getTabLinkCssClassObject('norfab')" v-on:click.prevent="showTab('norfab')">NorFab</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="#" v-bind:class="getTabLinkCssClassObject('profaba')" v-on:click.prevent="showTab('profaba')">ProFaba</a>
				</li>
			</ul>
			<!--<svg-vue icon="field-data-db-schema"></svg-vue>-->

			<div class="tab-content">
				<div class="px-3 py-2" role="tabpanel" aria-labelledby="norfab-tab" v-if="shouldShowTab('norfab')">
					<figure class="figure">
						<img class="figure-img img-fluid rounded" src="/images/field-data-db-schema--norfab.png" />
						<figcaption class="figure-caption">Database schema for field and genotyping data for the NorFab project</figcaption>
					</figure>
				</div>

				<div class="px-3 py-2" role="tabpanel" aria-labelledby="profaba-tab" v-if="shouldShowTab('profaba')">
					<figure class="figure">
						<img class="figure-img img-fluid rounded" src="/images/field-data-db-schema--profaba.png" />
						<figcaption class="figure-caption">Database schema for field and genotyping data for the ProFaba project</figcaption>
					</figure>
				</div>
			</div>
		</div>
	</main>
</template>

<script>
import axios from 'axios';

export default {
	middleware: 'norfab-user',

	data: function() {
		return {
			visibleTab: 'norfab',
		};
	},

	methods: {
		getTabLinkCssClassObject: function(project) {
			return {
				'active': this.shouldShowTab(project),
			};
		},

		shouldShowTab: function(project) {
			return this.visibleTab === project;
		},

		showTab: function(project) {
			this.visibleTab = project;
		}
	}
};
</script>

<style lang="scss" scoped>
svg {
	width: 100%;
	display: block;
}

.nav-link.active {
	background-color: #fff;
	border-bottom-color: #fff;
}

.tab-content {
	background-color: #fff;
	border: 1px solid #dee2e6;
	border-top: none;
}
</style>
