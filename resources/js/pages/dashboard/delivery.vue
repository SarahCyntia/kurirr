<template>
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-4">Statistik Pengiriman</h1>
      <div class="bg-white p-4 rounded-lg shadow-md">
        <canvas ref="chartCanvas"></canvas>
      </div>
    </div>
  </template>
  
  <script>
//   import { Chart, registerables } from "chart.js";
//   Chart.register(...registerables);
  
  export default {
    data() {
      return {
        deliveriesPerDay: [10, 15, 20, 25, 30, 35, 40], // Contoh data jumlah paket per hari
        labels: ["Sen", "Sel", "Rab", "Kam", "Jum", "Sab", "Min"],
      };
    },
    mounted() {
      this.renderChart();
    },
    methods: {
      renderChart() {
        const ctx = this.$refs.chartCanvas.getContext("2d");
        new Chart(ctx, {
          type: "line",
          data: {
            labels: this.labels,
            datasets: [
              {
                label: "Paket per Hari",
                data: this.deliveriesPerDay,
                borderColor: "#3b82f6",
                backgroundColor: "rgba(59, 130, 246, 0.2)",
                fill: true,
                tension: 0.3,
              },
            ],
          },
          options: {
            responsive: true,
            plugins: {
              legend: {
                display: true,
              },
            },
            scales: {
              y: {
                beginAtZero: true,
              },
            },
          },
        });
      },
    },
  };
  </script>
  
  <style scoped>
  .bg-white {
    background-color: #ffffff;
  }
  </style>
  