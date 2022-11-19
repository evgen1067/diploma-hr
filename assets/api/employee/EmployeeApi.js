import { Api } from '../Api';

export class EmployeeApi extends Api {
  static async getEmployees(data) {
    return this.get('/api/v1/employees', data);
  }

  static async getEmployee(id, data) {
    return this.get(`/api/v1/employees/${id}`, data);
  }

  static async createEmployee(data) {
    return this.post('/api/v1/employees/new', data);
  }

  static async deleteEmployees(data) {
    return this.post('/api/v1/employees/delete', data);
  }
}
