import { ref } from 'vue'
import { defineStore } from 'pinia'

// Store globale per un unico modal di conferma condiviso da tutta l'app,
// al posto di window.confirm(): la UI nativa del browser non e' in stile
// e non e' personalizzabile. confirm() restituisce una Promise<boolean>
// cosi' il chiamante puo' fare "if (!(await confirmDialog.confirm(...)))
// return", identico a prima ma con "await" davanti.
export const useConfirmDialogStore = defineStore('confirmDialog', () => {
  const isOpen = ref(false)
  const message = ref('')
  const danger = ref(false)
  let resolvePromise = null

  function confirm(msg, { danger: isDanger = false } = {}) {
    message.value = msg
    danger.value = isDanger
    isOpen.value = true

    return new Promise((resolve) => {
      resolvePromise = resolve
    })
  }

  function respond(result) {
    isOpen.value = false
    resolvePromise?.(result)
    resolvePromise = null
  }

  return { isOpen, message, danger, confirm, respond }
})
