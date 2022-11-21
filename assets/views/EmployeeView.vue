<template>
  <va-content class="content">
    <h3 class="fw-bolder text-center">Управление сотрудниками</h3>
  </va-content>
  <employee-table />
</template>

<script>
import { VaContent } from 'vuestic-ui';
import EmployeeTable from '../components/employee/EmployeeTable';

export default {
  name: 'EmployeeView',
  components: { EmployeeTable, VaContent },
};
</script>

<style scoped></style>
