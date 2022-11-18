<template>
  <hr-modal ref="employeeModal" v-if="form && form.length">
    <template v-if="!cardLoading">
      <va-content class="content">
        <h4>{{ modalTitle }}</h4>
      </va-content>
      <va-form ref="form" tag="form" @validation="validation = $event" @submit.prevent="handleSubmit">
        <div class="d-flex flex-column">
          <va-input
            v-for="(inpInfo, key) in form"
            :key="key"
            v-model="request[inpInfo.key]"
            :label="inpInfo.label"
            :rules="inpInfo?.rule ? inpInfo?.rule : []"
            :type="inpInfo.type"
            class="mb-3"
          ></va-input>
          <va-button type="submit" class="mb-3"> Создать </va-button>
        </div>
      </va-form>
    </template>
    <hr-spinner v-else />
  </hr-modal>
  <template v-if="!loading">
    <div class="d-flex align-center">
      <va-button class="mr-3 mb-2" color="primary" icon="person_add" @click="showAddEmployeeModal">Добавить</va-button>
      <va-button class="mr-3 mb-2" color="danger" icon="clear" @click="deleteEmployees">Удалить</va-button>
      <va-select
        v-model="tablePaginator.perPage"
        :label="`Показать записи (${tablePaginator.perPage})`"
        :options="countRowsOptions"
        class="mr-3 mb-2"
        color="#6D39CC"
      />
    </div>
    <va-data-table
      :animated="true"
      :clickable="true"
      :columns="table.columns"
      :item-size="tableSettings.itemsSize"
      :items="table.items"
      :selectable="true"
      :striped="true"
      :wrapper-size="tableSettings.wrapperSize"
      items-track-by="id"
      selected-color="#44296b"
      sticky-header
      virtual-scroller
      @selectionChange="table.selectedItems = $event.currentSelectedItems"
      @row:dblclick="handleDblClick"
    >
      <template #headerAppend>
        <tr>
          <td> </td>
          <td v-for="(col, key) in table.columns" :key="key">
            <template v-if="col.datatype !== 'list' && col.datatype !== 'no'">
              <template v-if="tableFilter.filter[col.key].type !== 'number_inequality'">
                <va-input
                  v-model="tableFilter.filter[col.key].value"
                  :label="tableFilter.filter[col.key].label"
                  class="w-100"
                  @change="search"
                >
                  <template #prependInner>
                    <font-awesome-icon :icon="tableFilter.filter[col.key].iconName" />
                  </template>
                  <template #prepend>
                    <va-icon
                      :id="`${col.key}--dropdown`"
                      class="cursor-pointer ml-1 mr-1"
                      data-bs-toggle="dropdown"
                      name="filter_list"
                    />
                    <ul class="dropdown-menu" :aria-labelledby="`${col.key}--dropdown`">
                      <li v-for="(fil, filKey) in filtersList[col.datatype]" :key="filKey">
                        <a class="dropdown-item cursor-pointer" @click="changeFilter(col.key, fil)">
                          <font-awesome-icon :icon="fil.iconName" />
                          {{ fil.label }}
                        </a>
                      </li>
                    </ul>
                  </template>
                </va-input>
              </template>
              <div class="d-flex align-items-center" v-else>
                <va-input
                  v-model="tableFilter.filter[col.key].valueFrom"
                  label="От"
                  class="number_inequality mr-2"
                  @change="search"
                >
                  <template #prepend>
                    <va-icon
                      :id="`${col.key}--dropdown`"
                      class="cursor-pointer ml-1 mr-1"
                      data-bs-toggle="dropdown"
                      name="filter_list"
                    />
                    <ul class="dropdown-menu" :aria-labelledby="`${col.key}--dropdown`">
                      <li v-for="(fil, filKey) in filtersList[col.datatype]" :key="filKey">
                        <a class="dropdown-item cursor-pointer" @click="changeFilter(col.key, fil)">
                          <font-awesome-icon :icon="fil.iconName" />
                          {{ fil.label }}
                        </a>
                      </li>
                    </ul>
                  </template>
                </va-input>
                <font-awesome-icon :icon="tableFilter.filter[col.key].iconName" />
                <va-input
                  v-model="tableFilter.filter[col.key].valueTo"
                  label="До"
                  class="number_inequality ml-2"
                  @change="search"
                >
                </va-input>
              </div>
            </template>
            <template v-else-if="col.datatype === 'list'">
              <va-select
                v-model="tableFilter.filter[col.key].value"
                :label="
                  tableFilter.filter[col.key].label
                    ? tableFilter.filter[col.key].listItems.find(x => x.num === tableFilter.filter[col.key].value).label
                    : 'Выбор'
                "
                color="#6D39CC"
                class="w-100"
                :options="tableFilter.filter[col.key].listItems"
                text-by="label"
                value-by="num"
                @update:model-value="search"
                clearable
              ></va-select>
            </template>
          </td>
        </tr>
      </template>
    </va-data-table>
    <div class="table-example--pagination">
      <va-pagination v-model="tablePaginator.page" input :pages="pages">
        <template #prevPageLink="{ onClick, disabled }">
          <va-button :disabled="disabled" aria-label="go prev page" outline @click="onClick">Назад</va-button>
        </template>
        <template #nextPageLink="{ onClick, disabled }">
          <va-button :disabled="disabled" aria-label="go next page" outline @click="onClick">Далее</va-button>
        </template>
      </va-pagination>
    </div>
  </template>
  <hr-spinner style="min-height: 75vh" v-else />
</template>

<script>
import { VaButton, VaContent, VaDataTable, VaForm, VaIcon, VaInput, VaPagination, VaSelect } from 'vuestic-ui';
import HrModal from '../../ui/hrModal/HrModal';
import { employeeForm, emptyEmployeeRequest, columns, filter, Employee } from './Employee';
import { filtersList } from './Filters';
import HrSpinner from '../../ui/hrSpinner/HrSpinner';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import cloneDeep from 'lodash.clonedeep';
export default {
  name: 'EmployeeTable',
  components: {
    VaSelect,
    VaIcon,
    FontAwesomeIcon,
    VaPagination,
    VaDataTable,
    HrSpinner,
    VaForm,
    VaInput,
    HrModal,
    VaButton,
    VaContent,
  },
  data: () => ({
    // лоадеры
    // флаг загрузки на странице
    loading: true,
    // флаг загрузки в карточке
    cardLoading: false,
    // таблица и фильтры
    tablePaginator: {
      page: 1,
      perPage: 10,
    },
    // объект фильтра
    tableFilter: {
      filter: {},
    },
    // объект таблицы
    table: null,

    // модальное окно
    // реквест для отправки на backend
    request: {},
    // форма для вывода инпутов в модальное окно
    form: [],
    // заголовок модального окна (добавление / апдейт)
    modalTitle: 'Новый сотрудник',
    // валидация
    validation: null,
    // флаг, если true - запрос к добавлению, false - к обновлению
    add: true,

    // список фильтров
    filtersList: filtersList,
    countRowsOptions: [10, 50, 100, 'Все'],
    tableSettings: {
      wrapperSize: 528,
      itemsSize: 46,
    },
  }),
  async created() {
    this.tableFilter.filter = filter;
    await this.updateTableData();
  },
  // вотчер на изменение страницы и фильтра
  watch: {
    tablePaginator: {
      immediate: true,
      deep: true,
      async handler() {
        if (!this.loading) {
          await this.updateTableData();
        }
      },
    },
  },
  methods: {
    // обновление информации по таблице
    async updateTableData() {
      this.loading = true;

      this.table = await Employee.getEmployees(
        cloneDeep(this.tableFilter.filter),
        this.tablePaginator.page,
        this.tablePaginator.perPage,
      );
      this.table.columns = columns;
      this.table.selectedItems = [];
      this.loading = false;

      this.$vaToast.init({
        message: 'Данные загружены',
        color: 'success',
        position: 'bottom-right',
      });
    },
    // изменение текущего фильтра
    changeFilter(columnKey, filter) {
      this.loading = true;
      this.tableFilter.filter[columnKey] = cloneDeep(filter);
      this.$nextTick(() => {
        this.loading = false;
      });
    },
    // поиск по столбцам
    search() {
      this.updateTableData();
    },
    // открытие модального окна на добавление сотрудника
    showAddEmployeeModal() {
      this.add = true;
      this.modalTitle = 'Создание информации о новом сотруднике';
      this.request = cloneDeep(emptyEmployeeRequest);
      this.form = cloneDeep(employeeForm);

      this.$nextTick(() => {
        this.$refs.employeeModal.openModal();
      });
    },
    // удаление выбранных сотрудников
    async deleteEmployees() {
      if (this.table.selectedItems && this.table.selectedItems.length) {
        this.loading = true;
        await Employee.deleteEmployeesByIds(this.table.selectedItems);
        await this.updateTableData(this.filter);
        this.loading = false;
      } else {
        this.$vaToast.init({
          message: 'Вы не выбрали ни одного сотрудника',
          color: 'danger',
          position: 'bottom-right',
        });
      }
    },
    // двойной клик по сотруднику
    async handleDblClick(event) {
      this.loading = true;
      this.add = false;
      // получение информации о сотруднике
      this.modalTitle = 'Обновление информации о существующем сотруднике';
      this.request = await Employee.getEmployeeById(event.item.id);
      this.form = cloneDeep(employeeForm);

      // отключаем лоадер
      this.loading = false;
      // открываем модальное окно
      this.$nextTick(() => {
        this.$refs.employeeModal.openModal();
      });
    },
    // submit формы
    handleSubmit() {
      // валидация формы
      this.$refs.form.validate();

      // если прошли, запускаем лоадер и делаем запрос к апи
      if (this.validation) {
        this.cardLoading = true;
        if (this.add) {
          // создание нового сотрудника
          // TODO
        } else {
          // апдейт существующего
          // TODO
        }
        this.cardLoading = false;
      }
    },
  },
  computed: {
    pages() {
      return this.tablePaginator.perPage && this.tablePaginator.perPage !== 0
        ? Math.ceil(this.table.totalCount / this.tablePaginator.perPage)
        : this.table.totalCount.length;
    },
  },
};
</script>

<style lang="scss">
.table-example--pagination {
  margin-top: 15px;
  display: flex;
  justify-content: center;
}

.cursor-pointer {
  cursor: pointer;
}

.dropdown-menu {
  z-index: 3000;
}

.number_inequality {
  max-width: 150px;
}
</style>
