import { Api } from '../Api';

export class EmployeeApi extends Api {
  static async getEmployees(data) {
    return this.get('/api/v1/employees', data);
  }
}
