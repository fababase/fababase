<template>
	<div id="app" class="d-flex flex-column h-100">
		<loading ref="loading" />

		<transition name="page" mode="out-in">
			<component :is="layout" v-if="layout" />
		</transition>

		<footer class="footer mt-auto py-3">
			<div class="container">
				<span class="text-muted">
					All rights reserved. {{ yearSpan }} &copy; FabaBase, Aarhus, Denmark.
				</span>
			</div>
		</footer>
	</div>
</template>

<script>
import Loading from './Loading'

// Load layout components dynamically.
const requireContext = require.context('~/layouts', false, /.*\.vue$/)

const layouts = requireContext.keys()
	.map(file =>
		[file.replace(/(^.\/)|(\.vue$)/g, ''), requireContext(file)]
	)
	.reduce((components, [name, component]) => {
		components[name] = component.default || component
		return components
	}, {})

export default {
	el: '#app',

	components: {
		Loading
	},

	data: () => ({
		layout: null,
		defaultLayout: 'default',
		estYear: 2019,
		currentYear: (new Date()).getFullYear(),
	}),

	metaInfo () {
		const { appName } = window.config

		return {
			title: appName,
			titleTemplate: `%s · ${appName}`
		}
	},

	mounted () {
		this.$loading = this.$refs.loading
	},
	
	computed: {
		yearSpan() {
			if (this.estYear === this.currentYear) {
				return this.estYear;
			} else {
				return `${Math.min(this.estYear, this.currentYear)}—${Math.max(this.estYear, this.currentYear)}`;
			};
		},
	},

	methods: {
		/**
		 * Set the application layout.
		 *
		 * @param {String} layout
		 */
		setLayout (layout) {
			if (!layout || !layouts[layout]) {
				layout = this.defaultLayout
			}

			this.layout = layouts[layout]
		}
	}
}
</script>
