<script setup>
// Pagina demo (solo sviluppo): elenca i QR code dei tavoli seedati
// (tavolo-1..tavolo-10, vedi backend TableSeeder) cosi' si puo' testare il
// flusso cliente senza dover stampare/scansionare QR fisici. Ogni QR
// codifica l'URL assoluto di /t/:qrToken, quindi funziona anche
// inquadrandolo con la fotocamera di un telefono sulla stessa rete.
const tableNumbers = Array.from({ length: 10 }, (_, i) => i + 1)

function qrImageUrl(qrToken) {
  const target = `${window.location.origin}/t/${qrToken}`
  const params = new URLSearchParams({ size: '200x200', data: target })
  return `https://api.qrserver.com/v1/create-qr-code/?${params}`
}
</script>

<template>
  <main class="home">
    <img src="/pranzia-icon-512.png" alt="Pranzia" width="64" height="64" />
    <h1>PranzIA — Tavoli demo</h1>
    <p class="hint">
      Scansiona (o clicca) il QR di un tavolo per aprire la sessione cliente,
      come farebbe un ospite seduto fisicamente li'.
    </p>

    <ul class="tables">
      <li v-for="n in tableNumbers" :key="n" class="table">
        <RouterLink :to="`/t/tavolo-${n}`">
          <img :src="qrImageUrl(`tavolo-${n}`)" :alt="`QR Tavolo ${n}`" width="150" height="150" />
          <span class="number">Tavolo {{ n }}</span>
        </RouterLink>
      </li>
    </ul>
  </main>
</template>

<style scoped>
.home {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  padding: 2.5rem 1.5rem 3rem;
  font-family: 'Inter', system-ui, sans-serif;
  text-align: center;
}

h1 {
  font-family: 'Baloo 2', sans-serif;
  color: #412402;
  font-size: 1.5rem;
}

.hint {
  color: #666;
  font-size: 0.9rem;
  max-width: 28rem;
}

.tables {
  list-style: none;
  padding: 0;
  margin: 1.5rem 0 0;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1.25rem;
  width: 100%;
  max-width: 60rem;
}

.table a {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
  background: white;
  border: 1px solid #eee;
  border-radius: 0.75rem;
  padding: 1rem;
}

.table img {
  width: 100%;
  max-width: 150px;
  height: auto;
}

.number {
  font-weight: 600;
  color: #412402;
}
</style>
