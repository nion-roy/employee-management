<template>
  <div>
    <button class="btn btn-success mb-3" @click="openAdd">Add Employee</button>
    <div class="row mb-3">
      <div class="col-md-3">
        <input v-model="search" @input="onFilterChange" class="form-control" placeholder="Search by name or email" />
      </div>
      <div class="col-md-3">
        <select v-model="department" @change="onFilterChange" class="form-control">
          <option value="">All Departments</option>
          <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
        </select>
      </div>
      <div class="col-md-3">
        <input type="number" v-model.number="salaryMin" @input="onFilterChange" class="form-control" placeholder="Min Salary" />
      </div>
      <div class="col-md-3">
        <input type="number" v-model.number="salaryMax" @input="onFilterChange" class="form-control" placeholder="Max Salary" />
      </div>
    </div>
    <div class="mb-3">
      <button class="btn btn-secondary" @click="clearFilters">Clear Filters</button>
    </div>
    <table class="table table-bordered table-hover">
      <thead>
        <tr>
          <th @click="sort('name')">Name <span v-if="sortBy==='name'">{{ sortDir==='asc' ? '▲' : '▼' }}</span></th>
          <th @click="sort('email')">Email <span v-if="sortBy==='email'">{{ sortDir==='asc' ? '▲' : '▼' }}</span></th>
          <th>Department</th>
          <th>Designation</th>
          <th @click="sort('joined_date')">Joining Date <span v-if="sortBy==='joined_date'">{{ sortDir==='asc' ? '▲' : '▼' }}</span></th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="loading">
          <td colspan="6" class="text-center text-primary">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Loading...
          </td>
        </tr>
        <tr v-else-if="employees.length === 0">
          <td colspan="6" class="text-center text-muted">No employees found.</td>
        </tr>
        <tr v-else v-for="emp in employees" :key="emp.id">
          <td>{{ emp.name }}</td>
          <td>{{ emp.email }}</td>
          <td>{{ emp.department?.name }}</td>
          <td>{{ emp.detail?.designation }}</td>
          <td>{{ emp.detail?.joined_date }}</td>
          <td>
            <button class="btn btn-sm btn-primary me-2" @click="editEmployee(emp)"><i class="bi bi-pencil"></i></button>
            <button class="btn btn-sm btn-danger" @click="deleteEmployee(emp.id)"><i class="bi bi-trash"></i></button>
          </td>
        </tr>
      </tbody>
    </table>
    <nav>
      <ul class="pagination">
        <li class="page-item" :class="{disabled: page===1}">
          <button class="page-link" @click="changePage(page-1)">Previous</button>
        </li>
        <li
          v-for="p in visiblePages"
          :key="p"
          class="page-item"
          :class="{active: p===page, disabled: p==='...'}"
        >
          <button
            class="page-link"
            @click="typeof p === 'number' && changePage(p)"
            :disabled="p==='...'"
          >{{ p }}</button>
        </li>
        <li class="page-item" :class="{disabled: page===totalPages}">
          <button class="page-link" @click="changePage(page+1)">Next</button>
        </li>
      </ul>
    </nav>
    <EmployeeForm
      :show="showForm"
      :isEdit="isEdit"
      :employee="selectedEmployee"
      :departments="departments"
      @save="saveEmployee"
      @close="closeForm"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import EmployeeForm from './EmployeeForm.vue';
import Toastify from 'toastify-js';
import 'toastify-js/src/toastify.css';

const employees = ref([]);
const departments = ref([]);
const search = ref('');
const department = ref('');
const salaryMin = ref('');
const salaryMax = ref('');
const sortBy = ref('joined_date');
const sortDir = ref('desc');
const page = ref(1);
const perPage = ref(10);
const totalPages = ref(1);
const loading = ref(false);

const showForm = ref(false);
const isEdit = ref(false);
const selectedEmployee = ref(null);

const openAdd = () => {
  isEdit.value = false;
  selectedEmployee.value = null;
  showForm.value = true;
};
const editEmployee = (emp) => {
  isEdit.value = true;
  selectedEmployee.value = emp;
  showForm.value = true;
};
const closeForm = () => {
  showForm.value = false;
};
const saveEmployee = async (form) => {
  try {
    if (isEdit.value && selectedEmployee.value) {
      await axios.put(`/api/employees/${selectedEmployee.value.id}`, form);
    } else {
      await axios.post('/api/employees', form);
    }
    fetchEmployees();
    showForm.value = false;
    Toastify({
      text: isEdit.value ? "Employee updated successfully!" : "Employee created successfully!",
      duration: 3000,
      gravity: "top",
      position: "right",
      backgroundColor: "#28a745",
    }).showToast();
  } catch (e) {
    let message = "Error saving employee";
    if (e.response && e.response.data && e.response.data.message) {
      message = e.response.data.message;
    }
    Toastify({
      text: message,
      duration: 4000,
      gravity: "top",
      position: "right",
      backgroundColor: "#dc3545",
    }).showToast();
  }
};

const fetchDepartments = async () => {
  const res = await axios.get('/api/departments');
  departments.value = res.data.data || res.data;
};

const fetchEmployees = async () => {
  loading.value = true;
  try {
    const params = {
      search: search.value,
      department_id: department.value,
      salary_min: salaryMin.value,
      salary_max: salaryMax.value,
      sort_by: sortBy.value,
      sort_order: sortDir.value,
      page: page.value,
      per_page: perPage.value,
    };
    const res = await axios.get('/api/employees', { params });
    employees.value = res.data.data.data;
    totalPages.value = res.data.data.last_page || 1;
  } finally {
    loading.value = false;
  }
};

const sort = (field) => {
  if (sortBy.value === field) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = field;
    sortDir.value = 'asc';
  }
  fetchEmployees();
};

const changePage = (p) => {
  if (p < 1 || p > totalPages.value) return;
  page.value = p;
  fetchEmployees();
};

const deleteEmployee = async (id) => {
  if (confirm('Are you sure you want to delete this employee?')) {
    await axios.delete(`/api/employees/${id}`);
    fetchEmployees();
    Toastify({
      text: "Employee deleted successfully!",
      duration: 3000,
      gravity: "top",
      position: "right",
      backgroundColor: "#28a745",
    }).showToast();
  }
};

const visiblePages = computed(() => {
  const pages = [];
  const total = totalPages.value;
  const current = page.value;

  if (total <= 7) {
    for (let i = 1; i <= total; i++) pages.push(i);
  } else {
    pages.push(1);
    if (current > 4) pages.push('...');
    for (let i = Math.max(2, current - 2); i <= Math.min(total - 1, current + 2); i++) {
      pages.push(i);
    }
    if (current < total - 3) pages.push('...');
    pages.push(total);
  }
  return pages;
});

const onFilterChange = () => {
  page.value = 1;
  fetchEmployees();
};

const clearFilters = () => {
  search.value = '';
  department.value = '';
  salaryMin.value = '';
  salaryMax.value = '';
  page.value = 1;
  fetchEmployees();
};

onMounted(() => {
  fetchDepartments();
  fetchEmployees();
});
</script>

<style scoped>
.table th {
  cursor: pointer;
}
</style>
