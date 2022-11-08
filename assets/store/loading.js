export default {
  actions: {
    toggleLoading(ctx, value) {
      ctx.commit('updateLoading', value);
    },
  },
  mutations: {
    updateLoading(state, value) {
      state.loading = value;
    },
  },
  state: {
    loading: false,
  },
  getters: {
    loading(state) {
      return state.loading;
    },
  },
  modules: {},
};
