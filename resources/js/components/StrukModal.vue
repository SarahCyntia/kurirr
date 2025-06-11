<!-- components/StrukModal.vue -->
<template>
  <div v-if="visible" class="modal-backdrop">
    <div class="modal-box">
      <h3>Detail Struk</h3>
      <p><strong>No Resi:</strong> {{ data.no_resi }}</p>
      <p><strong>Penerima:</strong> {{ data.nama_penerima }}</p>
      <p><strong>Alamat Penerima:</strong> {{ data.alamat_penerima }}</p>
      <p><strong>Biaya:</strong> {{ data.biaya }}</p>
      <button class="btn btn-danger mt-3" @click="$emit('close')">Tutup</button>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{ data: any; visible: boolean }>();
defineEmits(["close"]);
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5); /* semi transparan */
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-box {
  background: #fff;
  padding: 2rem;
  border-radius: 12px;
  width: 90%;
  max-width: 500px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
  animation: slideDown 0.3s ease;
  font-family: 'Segoe UI', sans-serif;
}

.modal-box h3 {
  margin-bottom: 1rem;
  font-size: 1.5rem;
  color: #333;
}

.modal-box p {
  margin: 0.5rem 0;
  color: #555;
}

.modal-box strong {
  color: #222;
}

.btn {
  padding: 0.6rem 1.2rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: bold;
}

.btn-danger {
  background-color: #e74c3c;
  color: #fff;
  transition: background-color 0.3s;
}

.btn-danger:hover {
  background-color: #c0392b;
}

@keyframes slideDown {
  from {
    transform: translateY(-20px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }

}
</style>
