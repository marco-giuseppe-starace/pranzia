import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import OrderCard from './OrderCard.vue'

const pendingOrder = {
  id: 1,
  session_id: 5,
  table_number: 5,
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

    await wrapper.get('button:not(.back)').trigger('click')

    expect(wrapper.emitted('advance')).toEqual([[1, 'preparing']])
  })

  it('shows no forward button for a served order, only a way back', () => {
    const wrapper = mount(OrderCard, {
      props: { order: { ...pendingOrder, status: 'served' } },
    })

    expect(wrapper.find('button:not(.back)').exists()).toBe(false)
    expect(wrapper.find('button.back').exists()).toBe(true)
  })

  it('emits advance with the previous status when going back', async () => {
    const wrapper = mount(OrderCard, {
      props: { order: { ...pendingOrder, status: 'preparing' } },
    })

    await wrapper.get('button.back').trigger('click')

    expect(wrapper.emitted('advance')).toEqual([[1, 'pending']])
  })

  it('shows no back button for a pending order', () => {
    const wrapper = mount(OrderCard, { props: { order: pendingOrder } })

    expect(wrapper.find('button.back').exists()).toBe(false)
  })
})
