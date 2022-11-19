<template>
  <hr-modal ref="employeeModal" v-if="form && form.length">
    <template v-if="!cardLoading">
      <div class="d-flex flex-column">
        <div class="row">
          <div class="col-6">
            <img src="@/assets/employee.png" class="w-100" alt="">
          </div>
          <div class="col-6">
            <va-content class="content">
              <h4>{{ modalTitle }}</h4>
            </va-content>
            <va-form ref="form" tag="form" @validation="validation = $event" @submit.prevent="handleSubmit">
              <template v-for="(inpInfo, key) in form" :key="key">
                <va-input
                  v-if="inpInfo.datatype === 'string'"
                  v-model="request[inpInfo.key]"
                  :label="inpInfo.label"
                  :rules="inpInfo?.rule ? inpInfo?.rule : []"
                  class="w-100 mb-3"
                  type="text"
                ></va-input>
                <va-input
                  v-if="inpInfo.datatype === 'date'"
                  v-model="request[inpInfo.key]"
                  :label="inpInfo.label"
                  :rules="inpInfo?.rule ? inpInfo?.rule : []"
                  class="w-100 mb-3"
                  type="date"
                ></va-input>
                <va-select
                  v-if="inpInfo.datatype === 'list'"
                  v-model="request[inpInfo.key]"
                  :label="inpInfo.label"
                  :options="inpInfo.listItems"
                  :rules="inpInfo?.rule ? inpInfo?.rule : []"
                  text-by="label"
                  value-by="num"
                  class="w-100 mb-3"
                  clearable
                  color="#6D39CC"
                ></va-select>
              </template>
              <va-button type="submit" class="w-100 mb-3"> Создать </va-button>
            </va-form>
          </div>
        </div>
      </div>
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
      selected-color="#6D39CC"
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
                  v-if="col.datatype !== 'date'"
                  clearable
                  @clear="search"
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
                <va-date-input
                  v-else
                  v-model="tableFilter.filter[col.key].value"
                  :label="tableFilter.filter[col.key].label"
                  class="w-100"
                  manual-input
                  clearable
                  @update:model-value="search"
                  @clear="search"
                  :reset-on-close="false"
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
                </va-date-input>
              </template>
              <div class="d-flex align-items-center" v-else>
                <va-input
                  v-model="tableFilter.filter[col.key].valueFrom"
                  label="От"
                  class="number_inequality mr-2"
                  @change="search"
                  @clear="search"
                  clearable
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
                  @clear="search"
                  clearable
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
import {
  VaButton,
  VaContent,
  VaDataTable,
  VaDateInput,
  VaForm,
  VaIcon,
  VaInput,
  VaPagination,
  VaSelect,
} from 'vuestic-ui';
import HrModal from '../../ui/hrModal/HrModal';
import { dataColumns, emptyEmployeeRequest, columns, filter, Employee } from './Employee';
import { filtersList } from './Filters';
import HrSpinner from '../../ui/hrSpinner/HrSpinner';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import cloneDeep from 'lodash.clonedeep';
export default {
  name: 'EmployeeTable',
  components: {
    VaDateInput,
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

      if (this.table.items.length) {
        this.$vaToast.init({
          message: 'Данные загружены',
          color: 'success',
          position: 'bottom-right',
        });
      } else {
        this.$vaToast.init({
          message: 'Данных не найдено',
          color: 'warning',
          position: 'bottom-right',
        });
      }
    },
    // удаление выбранных сотрудников
    async deleteEmployees() {
      if (this.table.selectedItems && this.table.selectedItems.length) {
        if (confirm('Вы уверены, что желаете удалить выбранных сотрудников?')) {
          this.loading = true;
          await Employee.deleteEmployeesByIds(this.table.selectedItems);
          await this.updateTableData(this.filter);
          this.loading = false;
          this.$vaToast.init({
            message: 'Сотрудники успешно удалены',
            color: 'success',
            position: 'bottom-right',
          });
        }
      } else {
        this.$vaToast.init({
          message: 'Вы не выбрали ни одного сотрудника',
          color: 'danger',
          position: 'bottom-right',
        });
      }
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
      this.form = cloneDeep(dataColumns);

      this.$nextTick(() => {
        this.$refs.employeeModal.openModal();
      });
    },
    // двойной клик по сотруднику
    async handleDblClick(event) {
      this.loading = true;
      this.add = false;
      // получение информации о сотруднике
      this.modalTitle = 'Обновление информации о существующем сотруднике';
      let result = await Employee.getEmployeeById(event.item.id);
      this.form = cloneDeep(dataColumns);

      // отключаем лоадер
      this.loading = false;
      if (result.status !== 404) {
        this.request = result;
        // открываем модальное окно
        this.$nextTick(() => {
          this.$refs.employeeModal.openModal();
        });
      } else {
        this.$vaToast.init({
          message: result.data.message,
          color: 'success',
          position: 'bottom-right',
        });
      }
    },
    // submit формы
    async handleSubmit() {
      // валидация формы
      this.$refs.form.validate();

      // если прошли, запускаем лоадер и делаем запрос к апи
      if (this.validation) {
        this.cardLoading = true;
        if (this.add) {
          // создание нового сотрудника
          let result = await Employee.createEmployee(this.request);
          if (result.status === 400) {
            result.data.forEach(x => {
              this.$vaToast.init({
                message: x.message,
                color: 'danger',
                position: 'bottom-right',
              });
            });
          } else {
            this.$vaToast.init({
              message: result.data.message,
              color: 'success',
              position: 'bottom-right',
            });
            this.$refs.employeeModal.closeModal();
          }
        } else {
          // апдейт существующего
          let result = await Employee.editEmployee(this.request);
          if (result.status === 400) {
            result.data.forEach(x => {
              this.$vaToast.init({
                message: x.message,
                color: 'danger',
                position: 'bottom-right',
              });
            });
          } else {
            this.$vaToast.init({
              message: result.data.message,
              color: 'success',
              position: 'bottom-right',
            });
            this.$refs.employeeModal.closeModal();
          }
        }
        this.cardLoading = false;
        await this.updateTableData();
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
