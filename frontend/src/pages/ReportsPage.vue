<template>
  <div class="reports-page fade-in">
    <div class="page-header">
      <h4>Reports</h4>
      <p>Financial reports and analytics</p>
    </div>
    <div class="row g-2 mb-4">
      <div class="col-auto">
        <select v-model.number="selectedYear" class="form-select" @change="loadCharts">
          <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
        </select>
      </div>
    </div>
    <div class="row g-3 mb-4">
      <div class="col-lg-6">
        <div class="chart-card h-100">
          <h6><i class="bi bi-pie-chart me-2"></i>Expense by Category</h6>
          <div v-if="hasPieData" style="position: relative; height: 300px;">
            <canvas ref="pieChart"></canvas>
          </div>
          <div v-else class="d-flex align-items-center justify-content-center text-muted" style="height: 300px;">
            No transactions yet
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="chart-card h-100">
          <h6><i class="bi bi-people me-2"></i>Expense by Member</h6>
          <div v-if="hasMemberData" style="position: relative; height: 300px;">
            <canvas ref="memberChart"></canvas>
          </div>
          <div v-else class="d-flex align-items-center justify-content-center text-muted" style="height: 300px;">
            No transactions yet
          </div>
        </div>
      </div>
    </div>
    <div class="row g-3">
      <div class="col-lg-6">
        <div class="chart-card h-100">
          <h6><i class="bi bi-bar-chart me-2"></i>Income vs Expense</h6>
          <div v-if="hasBarData" style="position: relative; height: 300px;">
            <canvas ref="barChart"></canvas>
          </div>
          <div v-else class="d-flex align-items-center justify-content-center text-muted" style="height: 300px;">
            No transactions yet
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="chart-card h-100">
          <h6><i class="bi bi-graph-up me-2"></i>6-Month Expense Trend</h6>
          <div v-if="hasLineData" style="position: relative; height: 300px;">
            <canvas ref="lineChart"></canvas>
          </div>
          <div v-else class="d-flex align-items-center justify-content-center text-muted" style="height: 300px;">
            No transactions yet
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, nextTick, onMounted } from 'vue';
import Chart from 'chart.js/auto';
import { dashboardService } from '../services/dashboardService';

const pieChart    = ref(null);
const memberChart = ref(null);
const barChart    = ref(null);
const lineChart   = ref(null);

const hasPieData    = ref(false);
const hasMemberData = ref(false);
const hasBarData    = ref(false);
const hasLineData   = ref(false);

const now = new Date();
const selectedYear = ref(now.getFullYear());
const years = [now.getFullYear() - 1, now.getFullYear(), now.getFullYear() + 1];

let pieInstance, memberInstance, barInstance, lineInstance;

async function loadCharts() {
  // Destroy existing chart instances before re-fetching
  if (pieInstance)    { pieInstance.destroy();    pieInstance    = null; }
  if (memberInstance) { memberInstance.destroy(); memberInstance = null; }
  if (barInstance)    { barInstance.destroy();    barInstance    = null; }
  if (lineInstance)   { lineInstance.destroy();   lineInstance   = null; }

  // 1. Fetch all 4 chart APIs in parallel
  const [pieRes, memberRes, barRes, lineRes] = await Promise.all([
    dashboardService.charts('expense-by-category', { year: selectedYear.value }).catch(() => null),
    dashboardService.charts('expense-by-member',   { year: selectedYear.value }).catch(() => null),
    dashboardService.charts('income-vs-expense',   { year: selectedYear.value }).catch(() => null),
    dashboardService.charts('expense-trend').catch(() => null),
  ]);

  const pieData    = pieRes?.data?.data    ?? [];
  const memberData = memberRes?.data?.data ?? [];
  const barData    = barRes?.data?.data    ?? [];
  const lineData   = lineRes?.data?.data   ?? [];

  // 2. Set data-present flags — this controls v-if on each <canvas>
  hasPieData.value    = pieData.length > 0;
  hasMemberData.value = memberData.length > 0;
  hasBarData.value    = barData.some(d => d.income > 0 || d.expense > 0);
  hasLineData.value   = lineData.some(d => d.expense > 0);

  // 3. CRITICAL: wait for Vue to re-render and mount the canvas elements in DOM
  await nextTick();

  // 4. Now safely initialize charts — refs are guaranteed non-null if data exists
  if (hasPieData.value && pieChart.value) {
    pieInstance = new Chart(pieChart.value, {
      type: 'doughnut',
      data: {
        labels: pieData.map(d => d.category),
        datasets: [{ data: pieData.map(d => d.total), backgroundColor: pieData.map(d => d.color), borderWidth: 0 }],
      },
      options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } },
    });
  }

  if (hasMemberData.value && memberChart.value) {
    memberInstance = new Chart(memberChart.value, {
      type: 'doughnut',
      data: {
        labels: memberData.map(d => d.member),
        datasets: [{ data: memberData.map(d => d.total), backgroundColor: memberData.map(d => d.color), borderWidth: 0 }],
      },
      options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } },
    });
  }

  if (hasBarData.value && barChart.value) {
    barInstance = new Chart(barChart.value, {
      type: 'bar',
      data: {
        labels: barData.map(d => d.month),
        datasets: [
          { label: 'Income',  data: barData.map(d => d.income),  backgroundColor: '#28a745', borderRadius: 4 },
          { label: 'Expense', data: barData.map(d => d.expense), backgroundColor: '#dc3545', borderRadius: 4 },
        ],
      },
      options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true } } },
    });
  }

  if (hasLineData.value && lineChart.value) {
    lineInstance = new Chart(lineChart.value, {
      type: 'line',
      data: {
        labels: lineData.map(d => d.month),
        datasets: [{
          label: 'Expense',
          data: lineData.map(d => d.expense),
          borderColor: '#dc3545',
          backgroundColor: 'rgba(220,53,69,0.1)',
          fill: true,
          tension: 0.4,
        }],
      },
      options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } },
    });
  }
}

onMounted(loadCharts);
</script>
