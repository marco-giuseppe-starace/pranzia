import { beforeEach, describe, expect, it } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useSessionStore } from './session.js'

describe('session store', () => {
  beforeEach(() => {
    localStorage.clear()
    setActivePinia(createPinia())
  })

  it('defaults to italian with no session set', () => {
    const session = useSessionStore()

    expect(session.language).toBe('it')
    expect(session.sessionId).toBeNull()
  })

  it('stores the session id and table id from the API response', () => {
    const session = useSessionStore()

    session.setSession({ id: 42, table_id: 7 })

    expect(session.sessionId).toBe(42)
    expect(session.tableId).toBe(7)
  })

  it('persists the language choice across store instances', () => {
    const session = useSessionStore()
    session.setLanguage('en')

    setActivePinia(createPinia())
    const reloaded = useSessionStore()

    expect(reloaded.language).toBe('en')
  })
})
