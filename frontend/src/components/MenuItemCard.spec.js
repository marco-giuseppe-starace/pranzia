import { beforeEach, describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import MenuItemCard from './MenuItemCard.vue'
import { useCartStore } from '../stores/cart.js'

const availableItem = {
  id: 1,
  name: 'Bruschetta',
  description: 'Pane e pomodoro',
  price: '6.50',
  available: true,
  allergens: [{ id: 1, name: 'Glutine' }],
}

describe('MenuItemCard', () => {
  beforeEach(() => {
    localStorage.clear()
    setActivePinia(createPinia())
  })

  it('renders name, price and allergens', () => {
    const wrapper = mount(MenuItemCard, { props: { item: availableItem } })

    expect(wrapper.text()).toContain('Bruschetta')
    expect(wrapper.text()).toContain('6.50')
    expect(wrapper.text()).toContain('Glutine')
  })

  it('adds the item to the cart when clicked', async () => {
    const wrapper = mount(MenuItemCard, { props: { item: availableItem } })
    const cart = useCartStore()

    await wrapper.find('button').trigger('click')

    expect(cart.items).toHaveLength(1)
    expect(cart.items[0].menuItemId).toBe(1)
  })

  it('shows an unavailable label instead of the add button when out of stock', () => {
    const wrapper = mount(MenuItemCard, {
      props: { item: { ...availableItem, available: false } },
    })

    expect(wrapper.find('button').exists()).toBe(false)
    expect(wrapper.text()).toContain('Non disponibile')
  })
})
