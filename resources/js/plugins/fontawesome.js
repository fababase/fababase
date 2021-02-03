import Vue from 'vue'
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

// import { } from '@fortawesome/free-regular-svg-icons'

import {
	faCog,
	faDatabase,
	faDna,
	faInfo,
	faLock,
	faSignOutAlt,
	faSort,
	faSortDown,
	faSortUp,
	faUser,
	faUsersCog,
	faUserShield,
} from '@fortawesome/free-solid-svg-icons'

import {
	faGithub
} from '@fortawesome/free-brands-svg-icons'

library.add(
	faCog,
	faDatabase,
	faDna,
	faInfo,
	faGithub,
	faLock,
	faSignOutAlt,
	faSort,
	faSortDown,
	faSortUp,
	faUser,
	faUsersCog,
	faUserShield,
)

Vue.component('fa', FontAwesomeIcon)
