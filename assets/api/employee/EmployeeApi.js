import { Api } from '@/api/Api';

/**
 * @typedef Employee
 * @type {object}
 * @property {number} id идентификатор пользователя
 * @property {string} fullName фио
 * @property {string} dateOfEmployment дата трудоустройства
 * @property {string} department отдел
 * @property {string} position должность
 * @property {string} status статус
 * @property {number} workExperience стаж работы
 * @property {string|null} dateOfDismissal дата увольнения
 * @property {string|null} reasonForDismissal причина увольнения
 * @property {string|null} categoryOfDismissal категория увольнения
 */

export class EmployeeApi extends Api {
  /**
   * Получить список сотрудников
   * @returns {Promise<{ result: Employee[] }>}
   */
  static async getEmployees(data) {
    return this.get(`/api/v1/employees`, data);
  }

  /**
   * Получить сотрудника
   * @returns { Promise<{ result: Employee }> }
   */
  static async getEmployee(id, data) {
    return this.get(`/api/v1/employees/${id}`, data);
  }

  static async createEmployee(data) {
    return this.post(`/api/v1/employees/new`, data);
  }

  static async updateEmployee(id, data) {
    return this.put(`/api/v1/employees/${id}`, data);
  }

  static async deleteEmployees(data) {
    return this.post(`/api/v1/employees/delete`, data);
  }
}
