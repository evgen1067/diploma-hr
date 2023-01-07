<template>
  <template v-if="column.datatype !== 'list' && column.datatype !== 'no'">
    <template v-if="filter.type !== 'number_inequality'">
      <va-input
        v-model="filter.value"
        :label="filter.label"
        class="w-100"
        @change="search"
        v-if="column.datatype !== 'date'"
        clearable
        @clear="search"
      >
        <template #prependInner>
          <font-awesome-icon :icon="filter.iconName" />
        </template>
        <template #prepend>
          <va-icon
            :id="`${column.key}--dropdown`"
            class="cursor-pointer ml-1 mr-1"
            data-bs-toggle="dropdown"
            name="filter_list"
          />
          <ul class="dropdown-menu" :aria-labelledby="`${column.key}--dropdown`">
            <li v-for="(fil, filKey) in filtersList[column.datatype]" :key="filKey">
              <a class="dropdown-item cursor-pointer" @click="changeFilter(column.key, fil)">
                <font-awesome-icon :icon="fil.iconName" />
                {{ fil.label }}
              </a>
            </li>
          </ul>
        </template>
        <template #append>
          <va-sort-icon @sort-change="sortChange" :column-key="column.key" :sort="sort" />
        </template>
      </va-input>
      <va-date-input
        v-else
        v-model="filter.value"
        :label="filter.label"
        class="w-100"
        manual-input
        clearable
        @update:model-value="search"
        @clear="search"
        :reset-on-close="false"
      >
        <template #prependInner>
          <font-awesome-icon :icon="filter.iconName" />
        </template>
        <template #prepend>
          <va-icon
            :id="`${column.key}--dropdown`"
            class="cursor-pointer ml-1 mr-1"
            data-bs-toggle="dropdown"
            name="filter_list"
          />
          <ul class="dropdown-menu" :aria-labelledby="`${column.key}--dropdown`">
            <li v-for="(fil, filKey) in filtersList[column.datatype]" :key="filKey">
              <a class="dropdown-item cursor-pointer" @click="changeFilter(column.key, fil)">
                <font-awesome-icon :icon="fil.iconName" />
                {{ fil.label }}
              </a>
            </li>
          </ul>
        </template>
        <template #append>
          <va-sort-icon @sort-change="sortChange" :column-key="column.key" :sort="sort" />
        </template>
      </va-date-input>
    </template>
    <div class="d-flex align-items-center" v-else>
      <va-input
        v-model="filter.valueFrom"
        label="От"
        class="number_inequality mr-2"
        @change="search"
        @clear="search"
        clearable
      >
        <template #prepend>
          <va-icon
            :id="`${column.key}--dropdown`"
            class="cursor-pointer ml-1 mr-1"
            data-bs-toggle="dropdown"
            name="filter_list"
          />
          <ul class="dropdown-menu" :aria-labelledby="`${column.key}--dropdown`">
            <li v-for="(fil, filKey) in filtersList[column.datatype]" :key="filKey">
              <a class="dropdown-item cursor-pointer" @click="changeFilter(column.key, fil)">
                <font-awesome-icon :icon="fil.iconName" />
                {{ fil.label }}
              </a>
            </li>
          </ul>
        </template>
      </va-input>
      <font-awesome-icon :icon="filter.iconName" />
      <va-input
        v-model="filter.valueTo"
        label="До"
        class="number_inequality ml-2"
        @change="search"
        @clear="search"
        clearable
      >
        <template #append>
          <va-sort-icon @sort-change="sortChange" :column-key="column.key" :sort="sort" />
        </template>
      </va-input>
    </div>
  </template>
  <template v-else-if="column.datatype === 'list'">
    <va-select
      v-model="filter.value"
      :label="filter.label ? filter.listItems.find(x => x.listValueId === filter.value).label : 'Выбор'"
      color="#4056A1"
      class="w-100"
      :options="filter.listItems"
      text-by="label"
      value-by="listValueId"
      @update:model-value="search"
      clearable
    >
      <template #append>
        <va-sort-icon @sort-change="sortChange" :column-key="column.key" :sort="sort" />
      </template>
    </va-select>
  </template>
</template>

<script>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import VaSortIcon from '../../icons/VaSortIcon.vue';
import { employeeInfoTable } from '../Employee';
import { VaDateInput, VaIcon } from 'vuestic-ui';
export default {
  name: 'FilterPanel',
  components: {
    VaSortIcon,
    FontAwesomeIcon,
    VaDateInput,
    VaIcon,
  },
  emits: ['sortChange', 'search', 'changeFilter'],
  props: {
    // col
    column: {
      type: Object,
      required: true,
    },
    // tableFilter.filter[col.key]
    columnFilter: {
      type: Object,
      required: true,
    },
    // tableFilter.sort
    sort: {
      type: Object,
      required: true,
    },
  },
  data: () => ({
    filter: null,
    filtersList: employeeInfoTable.filtersList,
  }),
  created() {
    this.filter = this.columnFilter;
  },
  methods: {
    sortChange(e) {
      this.$emit('sortChange', e);
    },
    search() {
      this.$emit('search', {
        key: this.column.key,
        filter: this.filter,
      });
    },
    changeFilter(columnKey, filter) {
      this.$emit('changeFilter', {
        key: columnKey,
        filter: filter,
      });
    },
  },
};
</script>

<style scoped></style>
