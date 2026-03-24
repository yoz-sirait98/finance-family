<template>
  <div class="budgets-page fade-in">
    <div class="page-header d-flex justify-content-between align-items-center">
      <div><h4>Budgets</h4><p>Set budgets per category for this month</p></div>
      <button class="btn btn-primary-gradient" @click="openCreate"><i class="bi bi-plus-lg me-1"></i>Set Budget</button>
    </div>
    <div class="row g-3">
      <div v-for="b in budgets" :key="b.id || b.category_id" class="col-md-6 col-lg-4">
        <div class="stat-card">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0 fw-bold">{{ b.category?.name || 'Unknown' }}</h6>
            <div class="btn-group btn-group-sm">
              <button class="btn btn-outline-primary" @click="openEdit(b)"><i class="bi bi-pencil"></i></button>
              <button class="btn btn-outline-danger" @click="confirmDelete(b)"><i class="bi bi-trash"></i></button>
            </div>
          </div>
          <div class="d-flex justify-content-between small text-muted mb-1">
            <span>Spent: Rp {{ Number(b.spent || 0).toLocaleString('id-ID') }}</span>
            <span>Budget: Rp {{ Number(b.amount || 0).toLocaleString('id-ID') }}</span>
          </div>
          <div class="progress mb-2">
            <div class="progress-bar" :class="b.percentage >= 80 ? 'bg-danger' : b.percentage >= 50 ? 'bg-warning' : 'bg-success'" :style="{ width: Math.min(100, b.percentage || 0) + '%' }"></div>
          </div>
          <div class="d-flex justify-content-between small">
            <span :class="b.percentage >= 80 ? 'text-danger fw-bold' : 'text-muted'">{{ (b.percentage || 0).toFixed(1) }}% used</span>
            <span class="text-muted">Remaining: Rp {{ Number(b.remaining || 0).toLocaleString('id-ID') }}</span>
          </div>
          <div v-if="b.is_over_threshold" class="alert alert-danger small mt-2 mb-0 py-1 px-2">
            <i class="bi bi-exclamation-triangle me-1"></i>Budget threshold exceeded!
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="vue-modal-backdrop" @mousedown.self="showModal = false">
      <div class="vue-modal">
        <div class="modal-header">
          <h5 class="modal-title">{{ editingId ? 'Edit' : 'Set' }} Budget</h5>
          <button type="button" class="btn-close" @click="showModal = false"></button>
        </div>
        <form @submit.prevent="save">
          <div class="modal-body">
            <div v-if="formError" class="alert alert-danger small">{{ formError }}</div>
            <div class="mb-3">
              <label class="form-label">Category</label>
              <select v-model="form.category_id" class="form-select" required :disabled="!!editingId">
                <option v-for="c in expenseCategories" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Amount (Rp)</label>
              <input v-model.number="form.amount" type="number" class="form-control" min="0" required />
            </div>
            <div class="row">
              <div class="col-6 mb-3">
                <label class="form-label">Month</label>
                <select v-model.number="form.month" class="form-select">
                  <option v-for="m in 12" :key="m" :value="m">{{ m }}</option>
                </select>
              </div>
              <div class="col-6 mb-3">
                <label class="form-label">Year</label>
                <input v-model.number="form.year" type="number" class="form-control" />
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showModal = false">Cancel</button>
            <button type="submit" class="btn btn-primary-gradient">Save</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirm Modal -->
    <div v-if="showDeleteModal" class="vue-modal-backdrop" @mousedown.self="showDeleteModal = false">
      <div class="vue-modal" style="max-width:420px">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Delete Budget</h5>
          <button type="button" class="btn-close" @click="showDeleteModal = false"></button>
        </div>
        <div class="modal-body">
          <p class="mb-0">Are you sure you want to delete the budget for <strong>{{ deletingItem?.category?.name }}</strong>? This cannot be undone.</p>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button class="btn btn-secondary" @click="showDeleteModal = false">Cancel</button>
          <button class="btn btn-danger" @click="doDelete">Delete</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { budgetService } from '../services/budgetService';
import { categoryService } from '../services/categoryService';
import { useToastStore } from '../stores/toast';
import { useBudgetStore } from '../stores/budgets';

const budgets = ref([]);
const expenseCategories = ref([]);
const editingId = ref(null);
const now = new Date();
const form = ref({ category_id: '', amount: 0, month: now.getMonth() + 1, year: now.getFullYear() });
const formError = ref('');

const showModal = ref(false);
const showDeleteModal = ref(false);
const deletingItem = ref(null);
const toast = useToastStore();
const budgetStore = useBudgetStore();

async function fetchData() {
  const { data } = await budgetService.list({ month: now.getMonth() + 1, year: now.getFullYear() });
  budgets.value = data.data;
}

async function fetchCategories() {
  const { data } = await categoryService.list('expense');
  expenseCategories.value = data.data;
}

function openCreate() {
  editingId.value = null;
  form.value = { category_id: expenseCategories.value[0]?.id || '', amount: 0, month: now.getMonth() + 1, year: now.getFullYear() };
  formError.value = '';
  showModal.value = true;
}

function openEdit(b) {
  editingId.value = b.id;
  form.value = { category_id: b.category_id || b.category?.id, amount: b.amount, month: b.month, year: b.year };
  formError.value = '';
  showModal.value = true;
}

async function save() {
  formError.value = '';
  try {
    if (editingId.value) {
      await budgetService.update(editingId.value, form.value);
      toast.success('Budget updated successfully');
    } else {
      await budgetService.create(form.value);
      toast.success('Budget created successfully');
    }
    showModal.value = false;
    fetchData();
    budgetStore.fetchAlerts(); // update navbar bell instantly
  } catch(e) {
    formError.value = e.response?.data?.message || 'Error occurred';
    toast.error(formError.value);
  }
}

function confirmDelete(b) {
  deletingItem.value = b;
  showDeleteModal.value = true;
}

async function doDelete() {
  if (!deletingItem.value) return;
  try {
    await budgetService.delete(deletingItem.value.id);
    toast.success('Budget deleted successfully');
    showDeleteModal.value = false;
    deletingItem.value = null;
    fetchData();
    budgetStore.fetchAlerts(); // update navbar bell instantly
  } catch(e) {
    toast.error(e.response?.data?.message || 'Failed to delete budget');
  }
}

onMounted(async () => {
  await fetchCategories();
  fetchData();
});
</script>
