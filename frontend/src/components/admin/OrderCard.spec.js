import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import OrderCard from './OrderCard.vue'

const pendingOrder = {
  id: 1,
  session_id: 5,
  status: 'pending',
  total: '13.00',
  items: [{ id: 1, menu_item_name: 'Bruschetta', quantity: 2, notes: null }],
}

describe('OrderCard', () => {
  it('shows a button to advance from pending to preparing', () => {
    const wrapper = mount(OrderCard, { props: { order: pendingOrder } })

    expect(wrapper.text()).toContain('In preparazione')
    expect(wrapper.find('button').exists()).toBe(true)
  })

  it('emits advance with the next status when clicked', async () => {
    const wrapper = mount(OrderCard, { props: { order: pendingOrder } })

    await wrapper.find('button').trigger('click')

    expect(wrapper.emitted('advance')).toEqual([[1, 'preparing']])
  })

  it('shows no advance button for a served order', () => {
    const wrapper = mount(OrderCard, {
      props: { order: { ...pendingOrder, status: 'served' } },
    })

    expect(wrapper.find('button').exists()).toBe(false)
  })
})
