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

// aspectRatio: 1 blocca il ritaglio a un quadrato, come il riquadro reale
// delle card: cosi' l'anteprima qui sotto corrisponde esattamente a come
// uscira' nel menu, senza margini vuoti imprevisti. Il "preview" di
// Cropper.js aggiorna dal vivo l'anteprima mentre si trascinano le
// maniglie, senza bisogno di ricalcolarla a mano ad ogni movimento.
onMounted(() => {
  cropper = new Cropper(imgEl.value, {
    viewMode: 1,
    dragMode: 'move',
    background: false,
    autoCropArea: 1,
    responsive: true,
    aspectRatio: 1,
    preview: '.live-preview',
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

      <div class="crop-layout">
        <div class="crop-area">
          <img ref="imgEl" :src="imageSrc" alt="" />
        </div>

        <div class="preview-col">
          <span class="preview-label">Anteprima nel menu</span>
          <div class="live-preview"></div>
        </div>
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
  max-width: 40rem;
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

.crop-layout {
  display: flex;
  gap: 1rem;
  align-items: flex-start;
  flex-wrap: wrap;
}

.crop-area {
  flex: 1 1 20rem;
  max-height: 55vh;
  overflow: hidden;
  background: #222;
  border-radius: 0.5rem;
}

.crop-area img {
  display: block;
  max-width: 100%;
}

.preview-col {
  flex: 0 0 auto;
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  align-items: center;
}

.preview-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: #412402;
}

/* La stessa identica veste grafica del riquadro reale nelle card (sfondo
   a gradiente, filigrana ripetuta, arrotondamento): cosi' quello che si
   vede qui e' proprio come uscira', non un'approssimazione. */
.live-preview {
  position: relative;
  width: 9rem;
  height: 9rem;
  border-radius: 0.75rem;
  overflow: hidden;
  background: linear-gradient(135deg, #fdf1de, #f6d9a8);
}

.live-preview::before {
  content: '';
  position: absolute;
  inset: 0;
  z-index: 0;
  pointer-events: none;
  background-image: url("data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20width='130'%20height='65'%3E%3Ctext%20x='-8'%20y='40'%20transform='rotate(-18%2065%2032)'%20font-family='sans-serif'%20font-weight='700'%20font-size='17'%20fill='rgba(65,36,2,0.16)'%3EPranzIA%3C/text%3E%3C/svg%3E");
  background-repeat: repeat;
}

.live-preview :deep(img) {
  position: relative;
  z-index: 1;
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
