import api from './api';

export const insightService = {
  getInsights() {
    return api.get('/insights');
  }
};
