import { filtersList } from './Filters';
import { EmployeeApi } from '../../api/employee/EmployeeApi';
import cloneDeep from 'lodash.clonedeep';

const dataColumns = [
  {
    key: 'fullName',
    datatype: 'string',
    label: 'ФИО',
  },
  {
    key: 'dateOfEmployment',
    datatype: 'date',
    label: 'Дата трудоустройства',
  },
  {
    key: 'workExperience',
    datatype: 'no',
    label: 'Опыт работы',
  },
  {
    key: 'department',
    datatype: 'string',
    label: 'Отдел',
  },
  {
    key: 'position',
    datatype: 'string',
    label: 'Должность',
  },
  {
    key: 'status',
    datatype: 'list',
    label: 'Статус',
    listItems: [
      {
        label: 'работает',
        num: 1,
      },
      {
        label: 'декрет',
        num: 2,
      },
      {
        label: 'уволен',
        num: 3,
      },
    ],
  },
  {
    key: 'dateOfDismissal',
    datatype: 'date',
    label: 'Дата увольнения',
  },
  {
    key: 'reasonForDismissal',
    datatype: 'list',
    label: 'Причина увольнения',
    listItems: [
      {
        label: 'не пройден испытательный срок',
        num: 1,
      },
      {
        label: 'проблемы с дисциплиной',
        num: 2,
      },
      {
        label: 'не справлялся с поставленными задачами',
        num: 3,
      },
      {
        label: 'сокращение',
        num: 4,
      },
      {
        label: 'предложение о работе с высокой заработной платой',
        num: 5,
      },
      {
        label: 'потерял ценность',
        num: 6,
      },
      {
        label: 'не видит для себя профессионального развития',
        num: 7,
      },
      {
        label: 'хочет сменить должность/направление',
        num: 8,
      },
      {
        label: 'выгорание',
        num: 9,
      },
      {
        label: 'релокация',
        num: 10,
      },
    ],
  },
  {
    key: 'categoryOfDismissal',
    datatype: 'list',
    label: 'Категория увольнения',
    listItems: [
      {
        label: 'добровольная',
        num: 1,
      },
      {
        label: 'принудительная',
        num: 2,
      },
      {
        label: 'нежелательная',
        num: 3,
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
  emptyEmployeeRequest[dataColumns[i].key] = '';
  columns.push({
    key: dataColumns[i].key,
    sortable: true,
    datatype: dataColumns[i].datatype,
    tdAlign: 'center',
    thAlign: 'center',
  });
  if (dataColumns[i].key === 'workExperience') {
    continue;
  }
  filter[dataColumns[i].key] =
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

class Employee {
  // получение списка сотрудников
  static async getEmployees(filter, page, perPage) {
    for (let key in filter) {
      delete filter[key].iconName;
      delete filter[key].label;
      if (filter[key].type !== 'number_inequality' && filter[key].value === '') {
        delete filter[key];
      } else if (
        filter[key].type === 'number_inequality' &&
        filter[key].valueTo.trim() === '' &&
        filter[key].valueFrom.trim() === ''
      ) {
        delete filter[key];
      } else if (filter[key].type === 'list') {
        delete filter[key].listItems;
      } else if (filter[key].type.includes('date')) {
        if (!filter[key]?.value || filter[key]?.value.length < 1) {
          delete filter[key];
        } else {
          filter[key].value = filter[key].value.toLocaleDateString();
          console.log(filter[key].value);
        }
      }
    }

    return EmployeeApi.getEmployees({
      filter: JSON.stringify(filter),
      page: page,
      perPage: perPage,
    });
  }

  static async createEmployee(data) {
    return await EmployeeApi.createEmployee(data);
  }

  // удаление сотрудников по id
  static async deleteEmployeesByIds(data) {
    return await EmployeeApi.deleteEmployees(data);
  }

  // получение сотрудника по id
  static async getEmployeeById(id) {
    return await EmployeeApi.getEmployee(id);
  }
}

export { emptyEmployeeRequest, dataColumns, filter, columns, Employee };
