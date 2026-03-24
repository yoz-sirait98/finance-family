import api from './api';

export const dashboardService = {
  summary: (params) => api.get('/dashboard/summary', { params }),
  charts: (type, params) => api.get(`/dashboard/charts/${type}`, { params }),
  full: (params) => api.get('/dashboard/full', { params }),
  reports: (params) => api.get('/dashboard/reports', { params }),
};
