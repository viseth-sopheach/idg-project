<template>
  <div class="flex h-screen bg-gray-100 font-sans text-gray-800">
    <Sidebar />
    <main class="flex-1 overflow-y-auto p-8">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import Sidebar from '../components/layout/Sidebar.vue'
</script>