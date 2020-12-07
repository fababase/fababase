function page(path) {
	return () => import(/* webpackChunkName: '' */ `~/pages/${path}`).then(m => m.default || m)
}

export default [
	// Index
	{ path: '/', name: 'welcome', component: page('welcome.vue') },

	// Data
  { path: '/data/field-trial-data', name: 'field-trial-data', component: page('data/field-trial/index.vue') },
  { path: '/data/field-trial-genotype-data', name: 'field-trial-genotype-data', component: page('data/field-trial/genotype.vue') },
	{ path: '/data/field-trial-db-schema', name: 'field-trial-db-schema', component: page('data/field-trial/db-schema.vue') },

	// User authentication system
	{ path: '/login', name: 'login', component: page('auth/login.vue') },
	{ path: '/register', name: 'register', component: page('auth/register.vue') },
	{ path: '/password/reset', name: 'password.request', component: page('auth/password/email.vue') },
	{ path: '/password/reset/:token', name: 'password.reset', component: page('auth/password/reset.vue') },
	{ path: '/email/verify/:id', name: 'verification.verify', component: page('auth/verification/verify.vue') },
	{ path: '/email/resend', name: 'verification.resend', component: page('auth/verification/resend.vue') },
	
	// Access handling system
	{ path: '/unauthorised', name: 'unauthorised', component: page('errors/401.vue') },

	// User homepage
	{ path: '/home', name: 'home', component: page('home.vue') },
	{
		path: '/settings',
		component: page('settings/index.vue'),
		children: [
			{ path: '', redirect: { name: 'settings.profile' } },
			{ path: 'profile', name: 'settings.profile', component: page('settings/profile.vue') },
			{ path: 'password', name: 'settings.password', component: page('settings/password.vue') }
		]
	},
	
	// Admin
	{
		path: '/admin',
		component: page('admin/index.vue'),
		children: [
			{ path: '', redirect: { name: 'admin.usermanagement' } },
			{ path: 'usermanagement', name: 'admin.usermanagement', component: page('admin/user-management.vue') },
		]
	},

	// Fallthrough catch-all paths
	{ path: '*', component: page('errors/404.vue') }
]
