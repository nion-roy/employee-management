<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ isEdit ? 'Edit' : 'Add' }} Employee</h5>
          <button type="button" class="btn-close" @click="close"></button>
        </div>
        <form @submit.prevent="submit">
          <div class="modal-body">
            <div v-if="saveError" class="alert alert-danger">{{ saveError }}</div>
            <div class="mb-3">
              <label class="form-label">Name</label>
              <input v-model="form.name" type="text" class="form-control" :class="{'is-invalid': errors.name}" placeholder="Enter full name" />
              <div class="invalid-feedback" v-if="errors.name">{{ errors.name }}</div>
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input v-model="form.email" type="email" class="form-control" :class="{'is-invalid': errors.email}" placeholder="Enter email address" />
              <div class="invalid-feedback" v-if="errors.email">{{ errors.email }}</div>
            </div>
            <div class="mb-3">
              <label class="form-label">Department</label>
              <select v-model="form.department_id" class="form-control" :class="{'is-invalid': errors.department_id}" :disabled="!departments.length">
                <option value="">Select Department</option>
                <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
              </select>
              <div class="invalid-feedback" v-if="errors.department_id">{{ errors.department_id }}</div>
            </div>
            <div class="mb-3">
              <label class="form-label">Designation</label>
              <input v-model="form.designation" type="text" class="form-control" :class="{'is-invalid': errors.designation}" placeholder="Enter designation" />
              <div class="invalid-feedback" v-if="errors.designation">{{ errors.designation }}</div>
            </div>
            <div class="mb-3">
              <label class="form-label">Salary</label>
              <input v-model="form.salary" type="number" step="any" class="form-control" :class="{'is-invalid': errors.salary}" placeholder="Enter salary" />
              <div class="invalid-feedback" v-if="errors.salary">{{ errors.salary }}</div>
            </div>
            <div class="mb-3">
              <label class="form-label">Joining Date</label>
              <input v-model="form.joined_date" type="date" class="form-control" :class="{'is-invalid': errors.joined_date}" placeholder="Select joining date" />
              <div class="invalid-feedback" v-if="errors.joined_date">{{ errors.joined_date }}</div>
            </div>
            <div class="mb-3">
              <label class="form-label">Address</label>
              <textarea v-model="form.address" class="form-control" :class="{'is-invalid': errors.address}" placeholder="Enter address"></textarea>
              <div class="invalid-feedback" v-if="errors.address">{{ errors.address }}</div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="close">Cancel</button>
            <button type="submit" class="btn btn-primary">{{ isEdit ? 'Update' : 'Create' }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, watch, toRefs, ref } from 'vue';
const props = defineProps({
  show: Boolean,
  isEdit: Boolean,
  employee: Object,
  departments: Array
});
const emit = defineEmits(['save', 'close']);

const form = reactive({
  name: '',
  email: '',
  department_id: '',
  designation: '',
  salary: '',
  joined_date: '',
  address: ''
});
const errors = reactive({});
const saveError = ref('');

watch(() => props.employee, (emp) => {
  if (emp) {
    form.name = emp.name || '';
    form.email = emp.email || '';
    form.department_id = emp.department_id || '';
    form.designation = emp.detail?.designation || '';
    form.salary = emp.detail?.salary || '';
    form.joined_date = emp.detail?.joined_date || '';
    form.address = emp.detail?.address || '';
  } else {
    form.name = '';
    form.email = '';
    form.department_id = '';
    form.designation = '';
    form.salary = '';
    form.joined_date = '';
    form.address = '';
  }
}, { immediate: true });

watch(() => props.departments, (newDepts) => {
  console.log('Departments in form:', newDepts);
}, { immediate: true });

const validate = () => {
  Object.keys(errors).forEach(k => errors[k] = '');
  let valid = true;
  if (!form.name) { errors.name = 'Name is required'; valid = false; }
  if (!form.email) { errors.email = 'Email is required'; valid = false; }
  if (!form.department_id) { errors.department_id = 'Department is required'; valid = false; }
  if (!form.designation) { errors.designation = 'Designation is required'; valid = false; }
  // Removed salary validation
  if (!form.joined_date) { errors.joined_date = 'Joining date is required'; valid = false; }
  if (!form.address) { errors.address = 'Address is required'; valid = false; }
  return valid;
};

const resetForm = () => {
  form.name = '';
  form.email = '';
  form.department_id = '';
  form.designation = '';
  form.salary = '';
  form.joined_date = '';
  form.address = '';
};

const submit = async () => {
  if (!validate()) return;
  // Map fields for backend
  const payload = {
    name: form.name,
    email: form.email,
    department_id: form.department_id,
    salary: form.salary,
    joining_date: form.joined_date, // map joined_date -> joining_date
    address: form.address,
    position: form.designation, // map designation -> position
    phone: '' // send phone, empty if not provided
  };
  saveError.value = '';
  try {
    await emit('save', payload);
    resetForm();
  } catch (e) {
    if (e.response && e.response.status === 422 && e.response.data.errors) {
      // Laravel validation error format
      Object.keys(errors).forEach(k => errors[k] = '');
      for (const key in e.response.data.errors) {
        errors[key] = e.response.data.errors[key][0];
      }
    } else if (e.response && e.response.data && e.response.data.message) {
      saveError.value = e.response.data.message;
    } else {
      saveError.value = 'An unexpected error occurred.';
    }
  }
};
const close = () => {
  resetForm();
  emit('close');
};
</script>

<style scoped>
.modal {
  display: block;
}
</style> 