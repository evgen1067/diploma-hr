<template>
  <hr-heading>Управление сотрудниками</hr-heading>
  <employee-table />
</template>

<script>
import EmployeeTable from '@/components/employee/EmployeeTable';
import HrHeading from '@/ui/hrHeading/HrHeading';

export default {
  name: 'EmployeeView',
  components: { HrHeading, EmployeeTable },
};
</script>

<style scoped></style>
