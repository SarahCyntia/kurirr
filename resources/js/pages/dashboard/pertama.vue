
<template>
    <!-- <div>
        <form class="back-black p-4  rounded shadow mx-auto" action="_action-pengaturan.php" method="post" enctype="multipart/form-data">
        <h4 class="text-dark mb-5">Status kurir</h4>
  <input type="hidden" name="action" value="ubah-pengaturan-dashboard">
  <input type="hidden" name="id" value="1">

  <div class="form-check mb-5">
    <input class="form-check-input" type="radio" name="status_kurir" id="akfit" value="1" checked>
    <label class="form-check-label" for="akfit">
      Menerima Pesanan
    </label>
  </div>

  <div class="form-check mb-5">
    <input class="form-check-input" type="radio" name="status_kurir" id="nonaktif" value="0">
    <label class="form-check-label" for="nonaktif">
      Tidak Menerima Pesanan
    </label>
  </div>


  <button type="submit" class="btn btn-primary w-20  ">Simpan</button>
</form>
</div> -->
    <h1 class="mb-5">Statistik hari ini</h1>
    <div class="box-wrapper">
        <div class="card p-5 mb-10">
            <h1>0</h1>
            <!-- <h2 class="mt-3">{{ yesterdayCount }}</h2> -->
            <router-link class="nav-link" to="/dashboard/order/data"
                >Data Order</router-link
            >
        </div>
        <div class="card p-5 mb-10">
            <h1>0</h1>
            <router-link class="nav-link" to="/dashboard/order/data"
                >Dijemput</router-link
            >
        </div>
        <div class="card p-5 mb-10">
            <h1>0</h1>
            <router-link class="nav-link" to="/dashboard/order/data"
                >Dikirim</router-link
            >
        </div>
        <div class="card p-5 mb-10">
            <h1>0</h1>
            <router-link class="nav-link" to="/dashboard/order/data"
                >Selesai</router-link
            >
        </div>
    </div>
    <h1 class="mb-5">Statistik Riwayat</h1>
    <div class="box-wrapper">
        <div class="card p-5 mb-10">
            <h1>0</h1>
            <router-link class="nav-link" to="/dashboard/order/riwayat"
                >Riwayat</router-link
            >
        </div>
        <div class="card p-5 mb-10">
            <h1>0</h1>
            <router-link class="nav-link" to="/dashboard/order/data"
                >Dijemput</router-link
            >
        </div>
        <div class="card p-5 mb-10">
            <h1>0</h1>
            <router-link class="nav-link" to="/dashboard/order/data"
                >Dikirim</router-link
            >
        </div>
    </div>

    <!-- <h1 class="mb-5">Statistik laporan</h1>
    <div class="card p-5 mb-10">
        <h1>0</h1>
        <router-link class="nav-link" to="/dashboard/order/data"
            >Data Order</router-link
        >
    </div> -->
</template>

<main></main>
<style>
p {
    line-height: 3;
}

div.card {
    border: 1px solid black;
    padding: 25px;
    background-color: lightblue;
    height: 100px;
    width: 18%;
    font-size: 20px;
    text-align: center;
    display: flex;
    flex-wrap: wrap; /* biar responsif kalau layar kecil */
}

h1 {
    margin-left: 2.3%;
}

.box-wrapper {
  display: flex;
  gap: 20px; /* jarak antar box */
  flex-wrap: wrap; /* biar responsif kalau layar kecil */
}
</style>
