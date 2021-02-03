<template>
  <main role="main">
		<div class="jumbotron jumbotron-fluid text-center">
			<div class="container">
				<h1 class="display-4">{{ title }}</h1>
        <p class="lead">
					Resources for the model legume <em>Vicia faba</em>.
				</p>
      </div>
		</div>

		<div class="container">
			<div class="row">
				<p>
					We are still quite new&mdash;there is&rsquo;much to show here.
					However, if you're looking to access
					<router-link :to="{ name: 'field-trial-data' }">field trial</router-link> and
					<router-link :to="{ name: 'field-trial-genotype-data' }">genotype</router-link>
					data, they are already up and running.
				</p>
			</div>
		</div>
  </main>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  layout: 'default',

  metaInfo () {
    return { title: this.$t('home') }
  },

  data: () => ({
    title: window.config.appName
  }),

  computed: mapGetters({
    authenticated: 'auth/check'
  })
}
</script>

<style scoped>

</style>
