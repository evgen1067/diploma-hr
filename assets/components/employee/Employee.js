import cloneDeep from 'lodash.clonedeep'

const emptyEmployeeRequest = {
  name: '',
  username: '',
  email: '',
  phone: '',
  website: '',
};

const employeeForm = [
  {
    key: 'name',
    label: 'Имя',
    type: 'text',
    rule: [value => value === 'Ben' || 'Should be Ben'],
  },
  {
    key: 'username',
    label: 'Фамилия',
    type: 'text',
    rule: [value => value === 'Ben' || 'Should be Ben'],
  },
  {
    key: 'email',
    label: 'Email',
    type: 'email',
  },
  {
    key: 'phone',
    label: 'Телефон',
    type: 'tel',
  },
  {
    key: 'website',
    label: 'Веб-Сайт',
    type: 'url',
  },
];

export class Employee {
  static getFormTemplate() {
    return cloneDeep(employeeForm);
  }

  static getEmptyEmployeeRequest() {
    return cloneDeep(emptyEmployeeRequest);
  }
}
