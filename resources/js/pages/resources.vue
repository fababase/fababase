<template>
  <main role="main">
		<div class="jumbotron jumbotron-fluid">
			<div class="container">
				<h1 class="display-4">Resources</h1>
      </div>
		</div>

		<div class="container">
			<div class="list-group">
				<a v-for="(resource, i) in resources" v-bind:key="i" v-bind:href="resource.url" class="list-group-item list-group-item-action p-4">
					<h2 class="h4 mb-1">{{ resource.title }}</h2>
					<ul class="list-inline">
						<li v-for="(author, j) in resource.authors" v-bind:key="j" class="list-inline-item">
							<small>{{ author.name }}</small>
						</li>
					</ul>
					<p class="mb-1">{{ resource.description }}</p>
				</a>
			</div>
		</div>
  </main>
</template>

<script>
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
		}]
  })
}
</script>

<style scoped>

</style>