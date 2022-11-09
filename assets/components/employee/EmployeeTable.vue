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
      <va-button icon="person_add" color="primary" class="mr-3 mb-2" @click="showAddEmployeeModal">Добавить</va-button>
      <va-button icon="clear" color="danger" class="mr-3 mb-2">Удалить</va-button>
    </div>
  </template>
  <hr-spinner v-else />
</template>

<script>
import { VaButton, VaContent, VaForm, VaInput } from 'vuestic-ui';
import HrModal from '../../ui/hrModal/HrModal';
import { Employee } from './Employee';
import HrSpinner from '../../ui/hrSpinner/HrSpinner';
export default {
  name: 'EmployeeTable',
  components: { HrSpinner, VaForm, VaInput, HrModal, VaButton, VaContent },
  data: () => ({
    loading: true,
    cardLoading: false,
    request: {},
    form: [],
    modalTitle: 'Новый сотрудник',
    validation: null,
    // флаг, если true - запрос к добавлению, false - к обновлению
    add: true,
  }),
  created() {
    this.loading = true;

    // this.loading = false;
  },
  methods: {
    // открытие модального окна на добавление сотрудника
    showAddEmployeeModal() {
      this.modalTitle = 'Новый сотрудник';
      this.request = Employee.getEmptyEmployeeRequest();
      this.form = Employee.getFormTemplate();
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
        } else {
          // апдейт существующего
        }
        this.cardLoading = false;
      }
    },
  },
};
</script>

<style scoped></style>
