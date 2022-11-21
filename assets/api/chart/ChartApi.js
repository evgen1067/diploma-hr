import { Api } from '../Api';

export class ChartApi extends Api {
  static async getLayoffsInfo(data) {
    return this.get('/api/v1/chart-layoffs', data);
  }

  static async getTurnoverInfo(data) {
    return this.get('/api/v1/turnover', data);
  }

  static async getDepartmentsInfo(data) {
    return this.get('/api/v1/departments', data);
  }
}
