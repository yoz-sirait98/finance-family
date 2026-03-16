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
          <div style="position: relative; height: 300px;">
            <canvas ref="pieChart"></canvas>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="chart-card h-100">
          <h6><i class="bi bi-people me-2"></i>Expense by Member</h6>
          <div style="position: relative; height: 300px;">
            <canvas ref="memberChart"></canvas>
          </div>
        </div>
      </div>
    </div>
    <div class="row g-3">
      <div class="col-lg-6">
        <div class="chart-card h-100">
          <h6><i class="bi bi-bar-chart me-2"></i>Income vs Expense</h6>
          <div style="position: relative; height: 300px;">
            <canvas ref="barChart"></canvas>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="chart-card h-100">
          <h6><i class="bi bi-graph-up me-2"></i>6-Month Expense Trend</h6>
          <div style="position: relative; height: 300px;">
            <canvas ref="lineChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import Chart from 'chart.js/auto';
import { dashboardService } from '../services/dashboardService';
const pieChart = ref(null);
const memberChart = ref(null);
const barChart = ref(null);
const lineChart = ref(null);
const now = new Date();
const selectedYear = ref(now.getFullYear());
const years = [now.getFullYear() - 1, now.getFullYear(), now.getFullYear() + 1];
let pieInstance, memberInstance, barInstance, lineInstance;
async function loadCharts() {
  try {
    const { data: pieRes } = await dashboardService.charts('expense-by-category', { year: selectedYear.value });
    if (pieInstance) pieInstance.destroy();
    if (pieChart.value && pieRes.data.length) {
      pieInstance = new Chart(pieChart.value, { type: 'doughnut', data: { labels: pieRes.data.map(d => d.category), datasets: [{ data: pieRes.data.map(d => d.total), backgroundColor: pieRes.data.map(d => d.color), borderWidth: 0 }] }, options: { responsive: true, plugins: { legend: { position: 'bottom' } }, maintainAspectRatio: false } });
    }
  } catch {}
  try {
    const { data: memberRes } = await dashboardService.charts('expense-by-member', { year: selectedYear.value });
    if (memberInstance) memberInstance.destroy();
    if (memberChart.value && memberRes.data.length) {
      memberInstance = new Chart(memberChart.value, { type: 'doughnut', data: { labels: memberRes.data.map(d => d.member), datasets: [{ data: memberRes.data.map(d => d.total), backgroundColor: memberRes.data.map(d => d.color), borderWidth: 0 }] }, options: { responsive: true, plugins: { legend: { position: 'bottom' } }, maintainAspectRatio: false } });
    }
  } catch {}
  try {
    const { data: barRes } = await dashboardService.charts('income-vs-expense', { year: selectedYear.value });
    if (barInstance) barInstance.destroy();
    if (barChart.value && barRes.data.length) {
      barInstance = new Chart(barChart.value, { type: 'bar', data: { labels: barRes.data.map(d => d.month), datasets: [{ label: 'Income', data: barRes.data.map(d => d.income), backgroundColor: '#28a745', borderRadius: 4 }, { label: 'Expense', data: barRes.data.map(d => d.expense), backgroundColor: '#dc3545', borderRadius: 4 }] }, options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } } });
    }
  } catch {}
  try {
    const { data: lineRes } = await dashboardService.charts('expense-trend');
    if (lineInstance) lineInstance.destroy();
    if (lineChart.value && lineRes.data.length) {
      lineInstance = new Chart(lineChart.value, { type: 'line', data: { labels: lineRes.data.map(d => d.month), datasets: [{ label: 'Expense', data: lineRes.data.map(d => d.expense), borderColor: '#dc3545', backgroundColor: 'rgba(220,53,69,0.1)', fill: true, tension: 0.4 }] }, options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } } });
    }
  } catch {}
}
onMounted(loadCharts);
</script>
