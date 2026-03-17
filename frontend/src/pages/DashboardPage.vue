<template>
  <div class="dashboard-page fade-in">
    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
      <div class="col-xl-3 col-md-6">
        <div class="stat-card">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="stat-label">Total Balance</div>
              <div class="stat-value">{{ formatRupiah(summary.total_balance) }}</div>
            </div>
            <div class="stat-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
              <i class="bi bi-wallet2"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="stat-card">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="stat-label">Monthly Income</div>
              <div class="stat-value text-success">{{ formatRupiah(summary.monthly_income) }}</div>
            </div>
            <div class="stat-icon" style="background: linear-gradient(135deg, #28a745, #20c997);">
              <i class="bi bi-arrow-down-circle"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="stat-card">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="stat-label">Monthly Expense</div>
              <div class="stat-value text-danger">{{ formatRupiah(summary.monthly_expense) }}</div>
            </div>
            <div class="stat-icon" style="background: linear-gradient(135deg, #dc3545, #e83e8c);">
              <i class="bi bi-arrow-up-circle"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="stat-card">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="stat-label">Net This Month</div>
              <div class="stat-value" :class="summary.monthly_net >= 0 ? 'text-success' : 'text-danger'">
                {{ formatRupiah(summary.monthly_net) }}
              </div>
            </div>
            <div class="stat-icon" style="background: linear-gradient(135deg, #fd7e14, #ffc107);">
              <i class="bi bi-graph-up"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-3 mb-4">
      <div class="col-lg-4">
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
      <div class="col-lg-8">
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
    </div>

    <div class="row g-3">
      <div class="col-12">
        <div class="chart-card">
          <h6><i class="bi bi-graph-up me-2"></i>Expense Trend (Last 6 Months)</h6>
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
import { ref, onMounted } from 'vue';
import Chart from 'chart.js/auto';
import { dashboardService } from '../services/dashboardService';
import { formatRupiah } from '../utils/currency';

const summary = ref({ total_balance: 0, monthly_income: 0, monthly_expense: 0, monthly_net: 0 });
const pieChart = ref(null);
const barChart = ref(null);
const lineChart = ref(null);

const hasPieData = ref(true);
const hasBarData = ref(true);
const hasLineData = ref(true);

onMounted(async () => {
  try {
    const { data: summaryRes } = await dashboardService.summary();
    summary.value = summaryRes.data;
  } catch {}

  try {
    const { data: pieRes } = await dashboardService.charts('expense-by-category');
    hasPieData.value = pieRes.data && pieRes.data.length > 0;
    if (pieChart.value && hasPieData.value) {
      new Chart(pieChart.value, {
        type: 'doughnut',
        data: {
          labels: pieRes.data.map(d => d.category),
          datasets: [{
            data: pieRes.data.map(d => d.total),
            backgroundColor: pieRes.data.map(d => d.color),
            borderWidth: 0,
          }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { position: 'bottom', labels: { padding: 16 } } },
        },
      });
    }
  } catch {}

  try {
    const { data: barRes } = await dashboardService.charts('income-vs-expense');
    hasBarData.value = barRes.data && barRes.data.some(d => d.income > 0 || d.expense > 0);
    if (barChart.value && hasBarData.value) {
      new Chart(barChart.value, {
        type: 'bar',
        data: {
          labels: barRes.data.map(d => d.month),
          datasets: [
            { label: 'Income', data: barRes.data.map(d => d.income), backgroundColor: '#28a745', borderRadius: 4 },
            { label: 'Expense', data: barRes.data.map(d => d.expense), backgroundColor: '#dc3545', borderRadius: 4 },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { position: 'top' } },
          scales: { y: { beginAtZero: true } },
        },
      });
    }
  } catch {}

  try {
    const { data: lineRes } = await dashboardService.charts('expense-trend');
    hasLineData.value = lineRes.data && lineRes.data.some(d => d.expense > 0);
    if (lineChart.value && hasLineData.value) {
      new Chart(lineChart.value, {
        type: 'line',
        data: {
          labels: lineRes.data.map(d => d.month),
          datasets: [{
            label: 'Expense',
            data: lineRes.data.map(d => d.expense),
            borderColor: '#dc3545',
            backgroundColor: 'rgba(220,53,69,0.1)',
            fill: true,
            tension: 0.4,
          }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: { y: { beginAtZero: true } },
        },
      });
    }
  } catch {}
});
</script>
