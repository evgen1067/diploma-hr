const textFilters = [
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

const dateFilters = [
  {
    iconName: 'calendar-day',
    label: 'Дата..',
    value: '',
    type: 'date_day',
  },
  {
    iconName: 'calendar-day',
    label: 'Дата до..',
    value: '',
    type: 'date_before',
  },
  {
    iconName: 'calendar-day',
    label: 'Дата после..',
    value: '',
    type: 'date_after',
  },
];

// лист возможных фильтров
const filtersList = {
  string: textFilters,
  number: numberFilters,
  date: dateFilters,
};

export { filtersList };
