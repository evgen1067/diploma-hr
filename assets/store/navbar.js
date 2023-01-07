export default {
  actions: {
    toggleSidebarMinimized(ctx) {
      ctx.commit('updateSidebarMinimized');
    },
  },
  mutations: {
    updateSidebarMinimized(state) {
      state.isSidebarMinimized = !state.isSidebarMinimized;
    },
  },
  state: {
    isSidebarMinimized: false,
  },
  getters: {
    isSidebarMinimized(state) {
      return state.isSidebarMinimized;
    },
  },
  modules: {},
};
