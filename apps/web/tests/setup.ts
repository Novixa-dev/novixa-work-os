// Nuxt auto-imports Vue APIs; expose them globally for component tests.
import * as vue from 'vue'

Object.assign(globalThis, {
  ref: vue.ref,
  computed: vue.computed,
  watch: vue.watch,
  reactive: vue.reactive,
  readonly: vue.readonly,
  onMounted: vue.onMounted,
})
