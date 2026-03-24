import api from './api';

export const netWorthService = {
  getCurrent() {
    return api.get('/net-worth/current');
  },
  getHistory() {
    return api.get('/net-worth/history');
  }
};
