<template>
  <div class="h-100">
    <va-sidebar :minimized="minimized" :width="width">
      <template v-for="(item, key) in routes" :key="key" >
        <va-sidebar-item @click="toRoute(item.routeName)">
          <va-sidebar-item-content>
            <va-icon color="#8338ec" :name="item.meta.icon" />
            <va-sidebar-item-title>
              {{ item.title }}
            </va-sidebar-item-title>
          </va-sidebar-item-content>
        </va-sidebar-item>
      </template>
    </va-sidebar>
  </div>
</template>

<script>
import sidebarRoutes from '@/components/layout/sidebar/SidebarRoutes';
import { VaIcon, VaSidebar, VaSidebarItem, VaSidebarItemContent, VaSidebarItemTitle } from 'vuestic-ui';
export default {
  name: 'HrSidebar',
  props: {
    minimized: {
      type: Boolean,
      required: true,
    },
    width: {
      type: String,
      required: false,
      default: '350px',
    },
  },
  components: { VaIcon, VaSidebarItemContent, VaSidebarItemTitle, VaSidebarItem, VaSidebar },
  methods: {
    toRoute(routeName) {
      this.$router.push({
        name: routeName,
      });
    },
  },
  computed: {
    routes() {
      return sidebarRoutes.routes;
    },
  },
};
</script>

<style lang="scss">
.va-sidebar {
  &__menu {
    padding: 2rem 0;
  }
  &-item {
    cursor: pointer;
    &__icon {
      width: 1.5rem;
      height: 1.5rem;
      display: flex;
      justify-content: center;
      align-items: center;
    }
  }
}
</style>
