<template>
	<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top">
		<div class="container">
			<router-link :to="{ name: user ? 'welcome' : 'welcome' }" class="navbar-brand">
				{{ appName }}
			</router-link>

			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarToggler" aria-controls="navbarToggler" aria-expanded="false">
				<span class="navbar-toggler-icon" />
			</button>

			<div id="navbarToggler" class="collapse navbar-collapse">
				<ul class="navbar-nav">
					<li class="nav-item dropdown">
						<a
							class="nav-link dropdown-toggle text-dark"
							href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							Field trial
						</a>

						<div class="dropdown-menu">
							<router-link :to="{ name: 'field-trial-data' }" class="dropdown-item pl-3">
								<fa icon="database" fixed-width />
								Data
							</router-link>
							<router-link :to="{ name: 'field-trial-genotype-data' }" class="dropdown-item pl-3">
								<fa icon="dna" fixed-width />
								Genotype data
							</router-link>

							<div class="dropdown-divider" />

							<router-link :to="{ name: 'field-trial-db-schema' }" class="dropdown-item pl-3">
								<fa icon="info" fixed-width />
								Database schema
							</router-link>
						</div>
					</li>
				</ul>

				<ul class="navbar-nav ml-auto">
					<!-- Authenticated -->
					<li v-if="user" class="nav-item dropdown">
						<a
							class="nav-link dropdown-toggle text-dark"
							href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<img :src="user.photo_url" class="rounded-circle profile-photo mr-1">
							{{ user.name }}
						</a>
						<div class="dropdown-menu">
							<template v-if="user.is_admin">
								<router-link :to="{ name: 'admin.usermanagement' }" class="dropdown-item pl-3">
									<fa icon="user-shield" fixed-width />
									Admin
								</router-link>
							</template>

							<router-link :to="{ name: 'settings.profile' }" class="dropdown-item pl-3">
								<fa icon="cog" fixed-width />
								{{ $t('settings') }}
							</router-link>

							<div class="dropdown-divider" />
							<a href="#" class="dropdown-item pl-3" @click.prevent="logout">
								<fa icon="sign-out-alt" fixed-width />
								{{ $t('logout') }}
							</a>
						</div>
					</li>
					
					<!-- Guest -->
					<template v-else>
						<li class="nav-item">
							<router-link :to="{ name: 'login' }" class="nav-link" active-class="active">
								{{ $t('login') }}
							</router-link>
						</li>
						<li class="nav-item">
							<router-link :to="{ name: 'register' }" class="nav-link" active-class="active">
								{{ $t('register') }}
							</router-link>
						</li>
					</template>
				</ul>
			</div>
		</div>
	</nav>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
	data: () => ({
		appName: window.config.appName
	}),

	computed: mapGetters({
		user: 'auth/user'
	}),

	methods: {
		async logout () {
			// Log out the user.
			await this.$store.dispatch('auth/logout')

			// Redirect to login.
			this.$router.push({ name: 'login' })
		}
	}
}
</script>

<style scoped>
.profile-photo {
	width: 2rem;
	height: 2rem;
	margin: -.375rem 0;
}
</style>
