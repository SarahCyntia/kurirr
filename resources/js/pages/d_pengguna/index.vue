<template>
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"></a>
            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNavAltMarkup"
                aria-controls="navbarNavAltMarkup"
                aria-expanded="false"
                aria-label="Toggle navigation"
            >
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                <div class="navbar-nav">
                    <a class="nav-link active" aria-current="page" href="#"
                        >🏠 Home</a
                    >
                    <router-link class="nav-link" to="/dashboard/order/input">🛒 Input</router-link>
                    <router-link class="nav-link" to="/dashboard/order/data">🛵 Order</router-link>
                    <a class="nav-link disabled" aria-disabled="true"
                        >Disabled</a
                    >
                </div>
            </div>
        </div>
    </nav>
    <!-- <DashboardHome v-if="currentTab === 'dashboard'" /> -->
</template>
<style>
.navbar .nav-link,
  .navbar .navbar-brand {
    font-size: 2rem; /* Ubah nilai ini sesuai keinginan, misal 1.5rem, 20px, dsb */
    padding-left: 45rem;
  }

</style>