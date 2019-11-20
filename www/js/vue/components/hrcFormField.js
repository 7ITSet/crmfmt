Vue.component('hrc-form-field', {
  render: function (createElement) {
    return createElement(
      'h' + this.value,   // имя тега
      this.$slots.default // массив дочерних элементов
    )
  },
  props: {
    value: {
      type: String,
      required: false
    }
  }
})