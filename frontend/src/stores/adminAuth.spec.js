import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { api } from '../api/client.js'
import { useAdminAuthStore } from './adminAuth.js'

vi.mock('../api/client.js', () => ({
  api: { post: vi.fn() },
}))

describe('adminAuth store', () => {
  beforeEach(() => {
    localStorage.clear()
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('starts unauthenticated with no stored token', () => {
    const auth = useAdminAuthStore()

    expect(auth.isAuthenticated).toBe(false)
  })

  it('stores the token returned by the login endpoint', async () => {
    api.post.mockResolvedValue({ token: 'abc123' })
    const auth = useAdminAuthStore()

    await auth.login('staff@pranzia.test', 'secret')

    expect(auth.isAuthenticated).toBe(true)
    expect(auth.token).toBe('abc123')
    expect(localStorage.getItem('pranzia.admin.token')).toBe('abc123')
  })

  it('propagates a login failure without setting a token', async () => {
    api.post.mockRejectedValue(new Error('Credenziali non valide'))
    const auth = useAdminAuthStore()

    await expect(auth.login('staff@pranzia.test', 'wrong')).rejects.toThrow()
    expect(auth.isAuthenticated).toBe(false)
  })

  it('clears the token on logout even if the API call fails', async () => {
    api.post.mockResolvedValueOnce({ token: 'abc123' })
    const auth = useAdminAuthStore()
    await auth.login('staff@pranzia.test', 'secret')

    api.post.mockRejectedValueOnce(new Error('network error'))
    await expect(auth.logout()).rejects.toThrow()

    expect(auth.isAuthenticated).toBe(false)
    expect(localStorage.getItem('pranzia.admin.token')).toBeNull()
  })
})
