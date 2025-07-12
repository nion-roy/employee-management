<template>
  <div class="container mt-5">
    <h1 class="mb-4">Employee Management Dashboard</h1>
    <EmployeeTable />
  </div>
</template>

<script setup>
import EmployeeTable from './components/EmployeeTable.vue';
</script>

<style>
body {
  background: #f8f9fa;
}
</style> 