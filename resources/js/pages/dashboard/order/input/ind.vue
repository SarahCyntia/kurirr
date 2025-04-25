<template>
  <div class="form-container">
    <!-- <h2>Input Data Order</h2> -->
    <form @submit.prevent="submitForm">
      <div class="form-group">
        <label for="namaBarang">Nama Barang</label>
        <input v-model="form.namaBarang" id="namaBarang" type="text" placeholder="Nama Barang" required />
      </div>

      <div class="form-group">
        <label for="alamatAsal">Alamat Asal</label>
        <textarea v-model="form.alamatAsal" id="alamatAsal" placeholder="Alamat Asal" required></textarea>
      </div>

      <div class="form-group">
        <label for="alamatTujuan">Alamat Tujuan</label>
        <textarea v-model="form.alamatTujuan" id="alamatTujuan" placeholder="Alamat Tujuan" required></textarea>
      </div>

      <div class="form-group">
        <label for="penerima">Penerima</label>
        <input v-model="form.penerima" id="penerima" type="text" placeholder="Nama Penerima" required />
      </div>
      <div class="button-group">
        <button type="submit" class="btn btn-input">Input</button>
        <button type="button" class="btn btn-batal" @click="resetForm">Batal</button>
      </div>
    </form>
  </div>
</template>

 <script>
import axios from 'axios';

export default {
name: 'FormKurir',
data() {
  return {
    form: {
      namaBarang: '',
      alamatAsal: '',
      alamatTujuan: '',
      penerima: ''
    },
    loading: false,
    successMessage: '',
    errorMessage: ''
  };
},
methods: {
  async submitForm() {
    this.loading = true;
    this.successMessage = '';
    this.errorMessage = '';

    try {
      const response = await axios.post('/dashboard/order/input', {
        nama_barang: this.form.namaBarang,
        alamat_asal: this.form.alamatAsal,
        alamat_tujuan: this.form.alamatTujuan,
        penerima: this.form.penerima
      });

      this.successMessage = 'Order berhasil disimpan!';
      alert('✅ Data berhasil disimpan!');
      console.log(response.data);
      this.resetForm();
    } catch (error) {
      this.errorMessage = error.response?.data?.message || 'Gagal menyimpan data.';
      alert('❌ Gagal menyimpan data!');
      console.error(error);
    } finally {
      this.loading = false;
    }
  },
  resetForm() {
    this.form = {
      namaBarang: '',
      alamatAsal: '',
      alamatTujuan: '',
      penerima: ''
    };
  }
}
};
</script>


<style scoped>
.form-container {
  max-width: 500px;
  margin: 0 auto;
}
.form-group {
  margin-bottom: 15px;
}
input, textarea {
  width: 100%;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 5px;
}
label {
  font-weight: bold;
  margin-bottom: 5px;
  display: block;
}
.button-group {
  margin-top: 20px;
}
.btn {
  padding: 10px 15px;
  font-weight: bold;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}
.btn-input {
  background-color: #3498db;
  color: white;
  margin-right: 10px;
}
.btn-batal {
  background-color: #e74c3c;
  color: white;
}
</style>
