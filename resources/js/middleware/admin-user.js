import store from '~/store'

export default async (to, from, next) => {
  const user = store.getters['auth/user'];

  if (!user) {
    next({
      name: 'login',
      params: {
        destination: to.name,
      },
    });
  } else if (!user.is_admin) {
    next({ name: 'unauthorised' })
  } else {
    next()
  }
}
