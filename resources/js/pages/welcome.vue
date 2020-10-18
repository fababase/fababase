<template>
  <main role="main">
		<div class="jumbotron">
			<div class="container">
				<h1 class="display-4">{{ title }}</h1>
        <p class="lead">
					Resources for the model legume <em>Vicia faba</em>.
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
