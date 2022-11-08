<template>
  <div class="h-100">
    <va-sidebar :minimized="minimized" :width="width">
      <template v-for="(item, key) in routes" :key="key">
        <va-sidebar-item @click="toRoute(item.routeName)" active-color="#6D39CC" :active="isRouteActive(item.routeName)" hover-color="#6D39CC">
          <va-sidebar-item-content>
            <va-icon v-if="!isRouteActive(item.routeName)" color="#6D39CC" :name="item.meta.icon" />
            <va-icon v-else color="#FFFFFF" :name="item.meta.icon" />
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
    isRouteActive(route) {
      return route === this.$route.name;
    }
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
