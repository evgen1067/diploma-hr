<template>
  <hr-modal ref="employeeModal" v-if="form && form.length">
    <template v-if="!cardLoading">
      <va-content class="content">
        <h4>{{ modalTitle }}</h4>
      </va-content>
      <va-form ref="form" tag="form" @submit.prevent="handleSubmit">
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
            value-by="listValueId"
            class="w-100 mb-3"
            clearable
            color="#4056A1"
          ></va-select>
        </template>
        <va-button type="submit" class="w-100 mb-3"> Создать </va-button>
      </va-form>
    </template>
    <hr-spinner v-else />
  </hr-modal>
  <template v-if="!loading">
    <div class="d-flex align-content-center">
      <div class="mr-3 mb-2">
        <va-popover
          icon="info"
          color="#4056A1"
          title="Создание сотрудника"
          :hover-out-timeout="30"
          message="Нажмите, чтобы открыть окно для создания сотрудника"
          placement="bottom-start"
          open
        >
          <va-button color="#4056A1" icon="person_add" @click="showAddEmployeeModal">Добавить</va-button>
        </va-popover>
      </div>
      <div class="mr-3 mb-2">
        <va-popover
          icon="info"
          color="#4056A1"
          title="Удаление сотрудника"
          :hover-out-timeout="30"
          message="Выберите с помощью чекбоксов удаляемых сотрудников и нажмите кнопку"
          placement="bottom-start"
          open
        >
          <va-button color="#F13C20" icon="clear" @click="deleteEmployees">Удалить</va-button>
        </va-popover>
      </div>

      <div class="mr-3 mb-2">
        <va-popover
          icon="info"
          color="#4056A1"
          title="Количество записей"
          :hover-out-timeout="30"
          message="Выберите количество записей, показываемых в таблице"
          placement="bottom-start"
          open
        >
          <va-select
            v-model="tablePaginator.perPage"
            :label="`Показать записи (${tablePaginator.perPage})`"
            :options="countRowsOptions"
            class="mr-3 mb-2"
            color="#4056A1"
          />
        </va-popover>
      </div>
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
      no-data-html="Сотрудников не найдено"
      selected-color="#4056A1"
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
                    ? tableFilter.filter[col.key].listItems.find(x => x.listValueId === tableFilter.filter[col.key].value).label
                    : 'Выбор'
                "
                color="#4056A1"
                class="w-100"
                :options="tableFilter.filter[col.key].listItems"
                text-by="label"
                value-by="listValueId"
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
          <va-button color="#4056A1" :disabled="disabled" aria-label="go prev page" outline @click="onClick"
            >Назад</va-button
          >
        </template>
        <template #nextPageLink="{ onClick, disabled }">
          <va-button color="#4056A1" :disabled="disabled" aria-label="go next page" outline @click="onClick"
            >Далее</va-button
          >
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
  VaPopover,
  VaSelect,
} from 'vuestic-ui';
import { employeeInfoTable } from './Employee';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { cloneDeep } from "lodash";
import { EmployeeApi } from '@/api/employee/EmployeeApi';
import HrSpinner from '@/ui/hrSpinner/HrSpinner';
import HrModal from '@/ui/hrModal/HrModal';
export default {
  name: 'EmployeeTable',
  components: {
    VaPopover,
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
    // флаг, если true - запрос к добавлению, false - к обновлению
    add: true,

    // список фильтров
    filtersList: employeeInfoTable.filtersList,
    countRowsOptions: [10, 50, 100, 'Все'],
    tableSettings: {
      wrapperSize: 528,
      itemsSize: 46,
    },
  }),
  async created() {
    this.tableFilter.filter = employeeInfoTable.filter;
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
    // уведомления
    toast(message, color) {
      this.$vaToast.init({
        message: message,
        color: color,
        position: 'bottom-right',
      });
    },
    // обновление информации по таблице
    async updateTableData() {
      this.loading = true;
      this.table = await EmployeeApi.getEmployees({
        filter: JSON.stringify(this.clearFilter()),
        page: this.tablePaginator.page,
        perPage: this.tablePaginator.perPage,
      });
      this.table.columns = employeeInfoTable.columns;
      this.table.selectedItems = [];
      this.loading = false;

      if (this.table.items.length) {
        this.toast('Данные загружены', 'success');
      } else {
        this.toast('Данных не найдено', 'warning');
      }
    },
    // удаление выбранных сотрудников
    async deleteEmployees() {
      if (this.table.selectedItems && this.table.selectedItems.length) {
        if (confirm('Вы уверены, что желаете удалить выбранных сотрудников?')) {
          this.loading = true;
          await EmployeeApi.deleteEmployees(this.table.selectedItems);
          await this.updateTableData(this.filter);
          this.loading = false;
          this.toast('Сотрудники успешно удалены', 'success');
        }
      } else {
        this.toast('Вы не выбрали ни одного сотрудника', 'danger');
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
      this.request = cloneDeep(employeeInfoTable.request);
      this.form = cloneDeep(employeeInfoTable.dataInfo);

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
      let result = await EmployeeApi.getEmployee(event.item.id);
      this.form = cloneDeep(dataInfo);

      // отключаем лоадер
      this.loading = false;
      if (result.status !== 404) {
        this.request = result;
        // открываем модальное окно
        this.$nextTick(() => {
          this.$refs.employeeModal.openModal();
        });
      } else {
        this.toast(result.data.message, 'success');
      }
    },
    // submit формы
    async handleSubmit() {
      // валидация формы
      this.$refs.form.validate();

      // если прошли, запускаем лоадер и делаем запрос к апи
      this.cardLoading = true;
      let result = '';
      if (this.add) {
        // создание нового сотрудника
        result = await EmployeeApi.createEmployee(this.request);
      } else {
        // апдейт существующего
        result = await EmployeeApi.updateEmployee(this.request.id, this.request);
      }
      if (result.status === 400) {
        result.data.forEach(x => {
          this.toast(x.message, 'danger');
        });
      } else {
        this.toast(result.data.message, 'success');
        this.$refs.employeeModal.closeModal();
      }
      this.cardLoading = false;
      await this.updateTableData();
    },
    clearFilter() {
      let filter = cloneDeep(this.tableFilter.filter);
      for (let key in filter) {
        delete filter[key].iconName;
        delete filter[key].label;
        delete filter[key]?.listItems;
        if (filter[key]?.value === '' || !filter[key]?.value) {
          delete filter[key];
        } else if (key.includes('date')) {
          filter[key].value = filter[key].value.toLocaleDateString();
        }
      }
      return filter;
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
