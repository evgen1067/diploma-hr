import { Api } from '@/api/Api';

export class ChartApi extends Api {
  static async getLayoffsInfo(data) {
    return this.get('/api/v1/chart-layoffs', data);
  }

  static async getTurnoverInfo(data) {
    return this.get('/api/v1/chart-turnover', data);
  }
}
