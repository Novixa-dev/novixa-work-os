// Runs before any page (and its data fetching) renders, so protected pages never
// fire API requests while the user is still unauthenticated.
export default defineNuxtRouteMiddleware(async (to) => {
  if (import.meta.server) return

  const { initAuth, isAuthenticated } = useAuth()
  await initAuth()

  const isAuthRoute = to.path.startsWith('/auth')

  if (!isAuthenticated.value && !isAuthRoute) {
    return navigateTo({
      path: '/auth/login',
      query: to.fullPath !== '/' && to.fullPath !== '/dashboard' ? { redirect: to.fullPath } : undefined,
    })
  }

  if (isAuthenticated.value && isAuthRoute) {
    return navigateTo('/dashboard')
  }
})
