import { filtersList } from './Filters';

const dataColumns = [
  {
    name: 'fullName',
    datatype: 'string',
  },
  {
    name: 'dateOfEmployment',
    datatype: 'date',
  },
  {
    name: 'workExperience',
    datatype: 'no',
  },
  {
    name: 'department',
    datatype: 'string',
  },
  {
    name: 'position',
    datatype: 'string',
  },
  {
    name: 'status',
    datatype: 'list',
    listItems: [
      {
        label: 'работает',
        num: '1',
      },
      {
        label: 'декрет',
        num: '2',
      },
      {
        label: 'уволен',
        num: '3',
      },
    ],
  },
  {
    name: 'dateOfDismissal',
    datatype: 'date',
  },
  {
    name: 'reasonForDismissal',
    datatype: 'list',
    listItems: [
      {
        label: 'не пройден испытательный срок',
        num: '1',
      },
      {
        label: 'проблемы с дисциплиной',
        num: '2',
      },
      {
        label: 'не справлялся с поставленными задачами',
        num: '3',
      },
      {
        label: 'сокращение',
        num: '4',
      },
      {
        label: 'предложение о работе с высокой заработной платой',
        num: '5',
      },
      {
        label: 'потерял ценность',
        num: '6',
      },
      {
        label: 'не видит для себя профессионального развития',
        num: '7',
      },
      {
        label: 'хочет сменить должность/направление',
        num: '8',
      },
      {
        label: 'выгорание',
        num: '9',
      },
      {
        label: 'релокация',
        num: '10',
      },
    ],
  },
  {
    name: 'categoryOfDismissal',
    datatype: 'list',
    listItems: [
      {
        label: 'добровольная',
        num: '1',
      },
      {
        label: 'принудительная',
        num: '2',
      },
      {
        label: 'нежелательная',
        num: '3',
      },
    ],
  },
  // 'level',
];

// запрос на бэкенд для добавления новой записи
let emptyEmployeeRequest = {},
  // список колонок для отображения в таблице
  columns = [],
  // изначальный набор фильтров
  filter = {};

for (let i = 0; i < dataColumns.length; i++) {
  emptyEmployeeRequest[dataColumns[i]] = '';
  columns.push({
    key: dataColumns[i].name,
    sortable: true,
    datatype: dataColumns[i].datatype,
    tdAlign: 'center',
    thAlign: 'center',
  });
  if (dataColumns[i].name === 'workExperience') {
    continue;
  }
  filter[dataColumns[i].name] =
    dataColumns[i].datatype !== 'list'
      ? cloneDeep(filtersList[dataColumns[i].datatype][0])
      : {
          iconName: '',
          label: '',
          value: '',
          type: 'list',
          listItems: dataColumns[i].listItems,
        };
}

const employeeForm = [
  {
    key: 'fullName',
    label: 'ФИО',
    type: 'text',
    // rule: [value => value === 'Ben' || 'Should be Ben'],
  },
  {
    key: 'dateOfEmployment',
    label: 'дата трудоустройства',
    type: 'text',
    // rule: [value => value === 'Ben' || 'Should be Ben'],
  },
  {
    key: 'workExperience',
    label: 'стаж работы',
    type: 'text',
    // rule: [value => value === 'Ben' || 'Should be Ben'],
  },
  {
    key: 'department',
    label: 'отдел',
    type: 'text',
    // rule: [value => value === 'Ben' || 'Should be Ben'],
  },
  {
    key: 'position',
    label: 'должность',
    type: 'text',
    // rule: [value => value === 'Ben' || 'Should be Ben'],
  },
  {
    key: 'level',
    label: 'уровень',
    type: 'text',
    // rule: [value => value === 'Ben' || 'Should be Ben'],
  },
  {
    key: 'status',
    label: 'статус',
    type: 'text',
    // rule: [value => value === 'Ben' || 'Should be Ben'],
  },
  {
    key: 'dateOfDismissal',
    label: 'дата увольнения',
    type: 'text',
    // rule: [value => value === 'Ben' || 'Should be Ben'],
  },
  {
    key: 'reasonForDismissal',
    label: 'причина увольнения',
    type: 'text',
    // rule: [value => value === 'Ben' || 'Should be Ben'],
  },
  {
    key: 'categoryOfDismissal',
    label: 'категория увольнения',
    type: 'text',
    // rule: [value => value === 'Ben' || 'Should be Ben'],
  },
];

import { EmployeeApi } from '../../api/employee/EmployeeApi';
import cloneDeep from 'lodash.clonedeep';

class Employee {
  // получение списка сотрудников
  static async getEmployees(filter, page, perPage) {
    for (let key in filter) {
      delete filter[key].iconName;
      delete filter[key].label;
      if (filter[key].type !== 'number_inequality' && filter[key].value.trim() === '') {
        delete filter[key];
      } else if (
        filter[key].type === 'number_inequality' &&
        filter[key].valueTo.trim() === '' &&
        filter[key].valueFrom.trim() === ''
      ) {
        delete filter[key];
      } else if (filter[key].type === 'list') {
        delete filter[key].listItems;
      }
    }

    return EmployeeApi.getEmployees({
      filter: JSON.stringify(filter),
      page: page,
      perPage: perPage,
    });
  }

  // получение сотрудника по id
  static async getEmployeeById(id) {
    // TODO with API
    console.log(id);
    // return employees.find(x => x.id === id);
  }

  // удаление сотрудников по id
  static async deleteEmployeesByIds(ids) {
    // TODO with API
    console.log(ids);
  }
}

export { emptyEmployeeRequest, employeeForm, filter, columns, Employee };
