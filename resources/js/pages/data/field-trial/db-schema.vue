<template>
	<main role="main">
		<div class="jumbotron">
			<div class="container">
				<h1 class="display-4">Field trial database schema</h1>
				<p class="lead">
					An overview of the relational database used to store
					<router-link :to="{ name: 'field-trial-data' }">Faba data</router-link>.
				</p>
			</div>
		</div>

		<div class="container">
			<div class="row">
				<!--<svg-vue icon="field-data-db-schema"></svg-vue>-->
				<img class="img-fluid" src="/images/field-data-db-schema.png" />
			</div>
		</div>
	</main>
</template>

<script>
import axios from 'axios';

export default {
	middleware: 'norfab-user',	
};
</script>

<style lang="scss">
svg {
	width: 100%;
	display: block;
}
</style>
