import { beforeEach, describe, expect, it, vi } from 'vitest'
import { flushPromises, mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { api } from '../../api/client.js'
import AdminOrdersView from './AdminOrdersView.vue'

vi.mock('../../api/client.js', () => ({
  api: { get: vi.fn(), patch: vi.fn() },
}))

vi.mock('../../layouts/AdminLayout.vue', () => ({
  default: { template: '<div><slot /></div>' },
}))

function deferred() {
  let resolve
  const promise = new Promise((r) => { resolve = r })
  return { promise, resolve }
}

const orderResource = (id, status) => ({
  id,
  session_id: 1,
  status,
  total: '6.50',
  items: [{ id: 1, menu_item_name: 'Bruschetta', quantity: 1, notes: null }],
})

describe('AdminOrdersView', () => {
  beforeEach(() => {
    localStorage.clear()
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('ignores a stale in-flight response that resolves after a newer one', async () => {
    const stale = deferred()
    const fresh = deferred()
    // First call: onMounted's initial loadOrders(). Second call: a refresh
    // triggered while the first is still in flight (poll tick or manual
    // "advance status" refresh) - the exact race that hits production.
    api.get.mockReturnValueOnce(stale.promise).mockReturnValueOnce(fresh.promise)

    const wrapper = mount(AdminOrdersView)
    await wrapper.get('select').trigger('change') // triggers the second loadOrders()

    // Resolve the newer request first, then the stale one arrives late.
    fresh.resolve({ data: [orderResource(2, 'preparing')] })
    await flushPromises()
    stale.resolve({ data: [orderResource(1, 'pending')] })
    await flushPromises()

    // Only the fresher order (#2, preparing) should be shown - the stale
    // response must not overwrite it.
    expect(wrapper.text()).toContain('#2')
    expect(wrapper.text()).not.toContain('#1')
  })
})
