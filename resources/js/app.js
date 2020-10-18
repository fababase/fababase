import Vue from 'vue'
import store from '~/store'
import router from '~/router'
import i18n from '~/plugins/i18n'
import App from '~/components/App'
import SvgVue from 'svg-vue';

import '~/plugins'
import '~/components'

Vue.use(SvgVue);

Vue.config.productionTip = false

/* eslint-disable no-new */
new Vue({
	i18n,
	store,
	router,
	...App
})
