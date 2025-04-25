<script>
import { ref, watch, onMounted } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./Form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { Kurir } from "@/types";
import axios from "axios"; // Pastikan axios sudah terinstall
</script>

<template>
      <Form :selected="selected" @close="openForm = false" v-if="openForm" @refresh="refresh" />
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Navbar</a>
        <button
            class="navbar-toggler"
            type="button"
            data-toggle="collapse"
            data-target="#navbarNavDropdown"
            aria-controls="navbarNavDropdown"
            aria-expanded="false"
            aria-label="Toggle navigation"
        >
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav">
                <li class="nav-item active">
                    <a class="nav-link" href="#"
                        >Home <span class="sr-only">(current)</span></a
                    >
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Features</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Pricing</a>
                </li>
                <li class="nav-item dropdown">
                    <a
                        class="nav-link dropdown-toggle"
                        href="#"
                        id="navbarDropdownMenuLink"
                        data-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="false"
                    >
                        Order
                    </a>
                    <div
                        class="dropdown-menu"
                        aria-labelledby="navbarDropdownMenuLink"
                    >
                        <router-link class="dropdown-item" to="@/pickup"
                            >Pick Up</router-link
                        >
                        <router-link class="dropdown-item" to="/dropoff"
                            >Drop Off</router-link
                        >
                        <router-link class="dropdown-item" to="/something-else"
                            >Something Else</router-link
                        >
                    </div>
                    
                </li>
            </ul>
        </div>
    </nav>
    <div class="box">
        <h1>
            Cek Resi <br />
            <p class="mt-4">Cek resi untuk lacak paket anda disini</p>
        </h1>
        <img
            src="/storage/gambar/bg-b31dca10-83f9-403c-b7a7-692f6618961d.png"
            alt="Gambar latar belakang"
        />
        <!-- <img src="/storage/gambar/gambar.png" alt="Gambar latar belakang" /> -->
    </div>
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-2">Cek Resi</h1>
        <p class="mb-4">Cek resi untuk lacak paket anda di sini</p>

        <div class="grid md:grid-cols-2 gap-6">
            <!-- Left Column - Form -->
            <div>
                <div class="mb-4">
                    <label class="block text-gray-600 mb-2">Resi</label> <br />
                    <input
                        v-model="trackingNumber"
                        type="text"
                        placeholder="000123456"
                        class="w-full border rounded p-2"
                    />
                </div>

                <div class="flex space-x-2">
                    <button
                        @click="trackPackage"
                        class="bg-blue-500 text-white px-4 py-2 rounded me-2"
                    >
                        Cek Resi
                    </button>
                    <button
                        @click="resetForm"
                        class="bg-gray-200 text-black px-4 py-2 rounded"
                    >
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
    <main></main>
    <div>
        <footer class="footer">
            <p>
                Author: Hege Refsnes<br />
                <a href="mailto:hege@example.com">2025</a>
            </p>
        </footer>
    </div>
</template>

<style scoped>
.box {
    /* padding: 150px; */
    color: black;
    background-color: rgb(227, 197, 197);
    /* font: 70px Poppins, Helvetica, "sans-serif"; */
    margin-top: 2%;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 10px;
}
.box h1 {
    font-size: 70px;
}
.box h1 p {
    font-size: 20px;
}
/* .background-image {
    max-width: 50%; Agar gambar responsif
    height: auto;    Menjaga rasio aspek gambar
} */

img {
    width: 40%;
    height: 100%;
    /* width: 100%; */
    height: auto;
    object-fit: cover; /* biar gambar tetap rapi dan nggak ketarik */
}

.container h1 {
    /* margin-left: 1000px; */
}

.footer {
    font: 20px Poppins, Helvetica, "sans-serif";
    margin: 28px 0px 28px 0px;
    padding: 1x x 20px;
    display: flex;
    text-align: center;
    justify-content: center;
    align-items: center;
    top: 0; /* mengilangkan dashboard */
    /* left: 0; */
    margin-top: 60%;
    background-color: brown;
}
/* .banner {
  width: 100%;
  overflow: hidden;
}

  .banner-image {
  width: 100%;
  height: auto;
  display: block;
} */
</style>
