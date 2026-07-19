<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import Cropper from 'cropperjs'
import 'cropperjs/dist/cropper.css'

const props = defineProps({
  imageSrc: { type: String, required: true },
})

const emit = defineEmits(['confirm', 'cancel'])

const imgEl = ref(null)
let cropper = null

// viewMode: 1 tiene il ritaglio dentro l'immagine (niente aree vuote);
// dragMode 'move' cosi' si puo' spostare l'inquadratura trascinando al
// centro, oltre a ridimensionarla dagli ancoraggi sugli angoli/bordi.
onMounted(() => {
  cropper = new Cropper(imgEl.value, {
    viewMode: 1,
    dragMode: 'move',
    background: false,
    autoCropArea: 1,
    responsive: true,
  })
})

onBeforeUnmount(() => {
  cropper?.destroy()
})

function confirm() {
  cropper.getCroppedCanvas({ maxWidth: 1600, maxHeight: 1600 }).toBlob((blob) => {
    if (blob) emit('confirm', blob)
  }, 'image/jpeg', 0.9)
}
</script>

<template>
  <div class="crop-overlay" @click.self="emit('cancel')">
    <div class="crop-modal">
      <h2>Ridimensiona foto</h2>
      <p class="hint">Trascina gli angoli per ritagliare, trascina al centro per spostare.</p>

      <div class="crop-area">
        <img ref="imgEl" :src="imageSrc" alt="" />
      </div>

      <div class="actions">
        <button type="button" class="secondary" @click="emit('cancel')">Annulla</button>
        <button type="button" class="primary" @click="confirm">Salva ritaglio</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.crop-overlay {
  position: fixed;
  inset: 0;
  background: rgba(65, 36, 2, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  z-index: 100;
}

.crop-modal {
  background: white;
  border-radius: 0.75rem;
  padding: 1.25rem;
  width: 100%;
  max-width: 32rem;
  font-family: 'Inter', system-ui, sans-serif;
}

h2 {
  margin: 0 0 0.25rem;
  font-family: 'Baloo 2', sans-serif;
  color: #412402;
  font-size: 1.2rem;
}

.hint {
  margin: 0 0 0.75rem;
  color: #666;
  font-size: 0.82rem;
}

.crop-area {
  max-height: 60vh;
  overflow: hidden;
  background: #222;
  border-radius: 0.5rem;
}

.crop-area img {
  display: block;
  max-width: 100%;
}

.actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  margin-top: 1rem;
}

button {
  border: none;
  border-radius: 0.4rem;
  padding: 0.5rem 0.9rem;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
}

button.primary {
  background: #ef9f27;
  color: #412402;
}

button.secondary {
  background: #f4f1ea;
  color: #412402;
}
</style>
