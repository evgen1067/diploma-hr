<template>
  <va-navbar class="app-layout-navbar">
    <template #left>
      <div class="left">
        <va-icon-menu-collapsed
          class="va-navbar__item"
          :class="{ 'x-flip': isSidebarMinimized }"
          @click="toggleSidebar"
          color="#4056A1"
        />
        <va-icon v-if="$route.name !== 'home'" class="cursor-pointer" name="home" color="#4056A1" @click="goHome" />
      </div>
    </template>
  </va-navbar>
</template>

<script>
import { VaNavbar } from 'vuestic-ui';
import VaIconMenuCollapsed from '@/components/icons/VaIconMenuCollapsed';
export default {
  name: 'HrNavbar',
  components: { VaIconMenuCollapsed, VaNavbar },
  methods: {
    toggleSidebar() {
      this.$store.dispatch('toggleSidebarMinimized');
    },
    goHome() {
      this.$router.push({
        name: 'home',
      });
    },
  },
  computed: {
    isSidebarMinimized() {
      return this.$store.getters.isSidebarMinimized;
    },
  },
};
</script>

<style lang="scss" scoped>
.va-navbar {
  box-shadow: var(--va-box-shadow);
  z-index: 2;
  @media screen and (max-width: 950px) {
    .left {
      width: 100%;
    }
    .app-navbar__actions {
      width: 100%;
      display: flex;
      justify-content: space-between;
    }
  }
}
.left {
  display: flex;
  align-items: center;
  & > * {
    margin-right: 1.5rem;
  }
  & > *:last-child {
    margin-right: 0;
  }
}
.x-flip {
  transform: scaleX(-100%);
}
.app-navbar-center {
  display: flex;
  align-items: center;
}

.cursor-pointer {
  cursor: pointer;
}
</style>
