import cloneDeep from 'lodash.clonedeep';

// Набор атрибутов сотрудника
const employeeData = [
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
    datatype: 'number',
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
        listValueId: 1,
      },
      {
        label: 'декрет',
        listValueId: 2,
      },
      {
        label: 'уволен',
        listValueId: 3,
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
        listValueId: 1,
      },
      {
        label: 'проблемы с дисциплиной',
        listValueId: 2,
      },
      {
        label: 'не справлялся с поставленными задачами',
        listValueId: 3,
      },
      {
        label: 'сокращение',
        listValueId: 4,
      },
      {
        label: 'предложение о работе с высокой заработной платой',
        listValueId: 5,
      },
      {
        label: 'потерял ценность',
        listValueId: 6,
      },
      {
        label: 'не видит для себя профессионального развития',
        listValueId: 7,
      },
      {
        label: 'хочет сменить должность/направление',
        listValueId: 8,
      },
      {
        label: 'выгорание',
        listValueId: 9,
      },
      {
        label: 'релокация',
        listValueId: 10,
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
        listValueId: 1,
      },
      {
        label: 'принудительная',
        listValueId: 2,
      },
      {
        label: 'нежелательная',
        listValueId: 3,
      },
    ],
  },
  // 'level',
];

// Фильтры для полей с datatype = string
const stringFilters = [
  {
    iconName: 'font',
    label: 'Текст содержит..',
    value: '',
    type: 'text_contains',
  },
  {
    iconName: 'font',
    label: 'Текст не содержит..',
    value: '',
    type: 'text_not_contains',
  },
  {
    iconName: 'font',
    label: 'Текст начинается с..',
    value: '',
    type: 'text_start',
  },
  {
    iconName: 'font',
    label: 'Текст заканчивается на..',
    value: '',
    type: 'text_end',
  },
  {
    iconName: 'font',
    label: 'Текст в точности..',
    value: '',
    type: 'text_accuracy',
  },
];

// Фильтры для полей с datatype = number
const numberFilters = [
  {
    iconName: 'equals',
    label: 'Равно..',
    value: '',
    type: 'number_equal',
  },
  {
    iconName: 'not-equal',
    label: 'Не равно..',
    value: '',
    type: 'number_not_equal',
  },
  {
    iconName: 'less-than',
    label: 'Строгое неравенство..',
    valueFrom: '',
    valueTo: '',
    type: 'number_inequality',
    isStrict: true,
  },
  {
    iconName: 'less-than-equal',
    label: 'Нестрогое неравенство..',
    valueFrom: '',
    valueTo: '',
    type: 'number_inequality',
    isStrict: false,
  },
];

// Фильтры для полей с datatype = date
const dateFilters = [
  {
    iconName: 'calendar-day',
    label: 'Дата..',
    value: null,
    type: 'date_day',
  },
  {
    iconName: 'calendar-day',
    label: 'Дата до..',
    value: null,
    type: 'date_before',
  },
  {
    iconName: 'calendar-day',
    label: 'Дата после..',
    value: null,
    type: 'date_after',
  },
];

const filtersList = {
  string: stringFilters,
  number: numberFilters,
  date: dateFilters,
};

// запрос на бэкенд для добавления новой записи
let emptyEmployeeRequest = {},
  // список колонок для отображения в таблице
  columns = [],
  // изначальный набор фильтров
  filter = {};

for (let i = 0; i < employeeData.length; i++) {
  emptyEmployeeRequest[employeeData[i].key] = '';
  columns.push({
    key: employeeData[i].key,
    sortable: true,
    datatype: employeeData[i].datatype,
    tdAlign: 'center',
    thAlign: 'center',
  });
  filter[employeeData[i].key] =
    employeeData[i].datatype !== 'list'
      ? cloneDeep(filtersList[employeeData[i].datatype][0])
      : {
          iconName: '',
          label: '',
          value: '',
          type: 'list',
          listItems: employeeData[i].listItems,
        };
}

const employeeInfoTable = {
  columns: columns,
  request: emptyEmployeeRequest,
  filter: filter,
  dataInfo: employeeData,
  filtersList: filtersList,
};

export { employeeInfoTable };
