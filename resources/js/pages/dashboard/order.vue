<template>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4">
      <!-- Pending Orders -->
      <div class="p-4 border-l-4 border-yellow-500 shadow-md bg-white rounded-lg">
        <div class="flex items-center gap-4">
          <span class="text-yellow-500 text-4xl">⏳</span>
          <div>
            <h3 class="text-lg font-semibold">Pending</h3>
            <p class="text-2xl font-bold">{{ orders.pending }}</p>
            <progress class="w-full" :value="orders.pending" :max="totalOrders"></progress>
          </div>
        </div>
      </div>
  
      <!-- Processing Orders -->
      <div class="p-4 border-l-4 border-blue-500 shadow-md bg-white rounded-lg">
        <div class="flex items-center gap-4">
          <span class="text-blue-500 text-4xl">🚚</span>
          <div>
            <h3 class="text-lg font-semibold">Diproses</h3>
            <p class="text-2xl font-bold">{{ orders.processing }}</p>
            <progress class="w-full" :value="orders.processing" :max="totalOrders"></progress>
          </div>
        </div>
      </div>
  
      <!-- Completed Orders -->
      <div class="p-4 border-l-4 border-green-500 shadow-md bg-white rounded-lg">
        <div class="flex items-center gap-4">
          <span class="text-green-500 text-4xl">✅</span>
          <div>
            <h3 class="text-lg font-semibold">Selesai</h3>
            <p class="text-2xl font-bold">{{ orders.completed }}</p>
            <progress class="w-full" :value="orders.completed" :max="totalOrders"></progress>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        orders: {
          pending: 12,
          processing: 8,
          completed: 25,
        },
      };
    },
    computed: {
      totalOrders() {
        return this.orders.pending + this.orders.processing + this.orders.completed;
      },
    },
  };
  </script>
  
  <style scoped>
  progress {
    height: 6px;
    border-radius: 4px;
    background: #e5e7eb;
  }
  progress::-webkit-progress-bar {
    background: #e5e7eb;
    border-radius: 4px;
  }
  progress::-webkit-progress-value {
    background: currentColor;
    border-radius: 4px;
  }
  </style>
  