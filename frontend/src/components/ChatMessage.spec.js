import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import ChatMessage from './ChatMessage.vue'

describe('ChatMessage', () => {
  it('renders the message text', () => {
    const wrapper = mount(ChatMessage, { props: { role: 'user', text: 'Ciao!' } })

    expect(wrapper.text()).toBe('Ciao!')
  })

  it('applies a class matching the role', () => {
    const wrapper = mount(ChatMessage, { props: { role: 'assistant', text: 'Come posso aiutarti?' } })

    expect(wrapper.classes()).toContain('assistant')
  })
})
