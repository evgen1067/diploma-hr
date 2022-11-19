import { Api } from '../Api';

export class ChartApi extends Api {
  static async getLayoffsInfo(data) {
    return this.get('/api/v1/layoffs', data);
  }

  static async getDepartmentsInfo(data) {
    return this.get('/api/v1/departments', data);
  }
}
