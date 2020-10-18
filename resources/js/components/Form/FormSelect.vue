<template>
  <div class="form-group row">
    <label
      v-bind:for="id"
      v-html="label"
      class="col-sm-4 col-form-label" />

    <div class="col-sm-8">
      <select 
        class="form-control"
        v-bind:id="id"
        v-bind:name="name"
        v-model="internalValue">
          <option
            v-for="(option, i) in generatedOptions"
            v-bind:key="i"
            v-bind:value="option.value"
            v-bind:disabled="option.disabled">
            {{ option.label }}
          </option>
      </select>
    </div>
  </div>
</template>

<script>
let count = 0;

export default {
  name: 'FormSelect',

  props: {
    label: {
      type: String,
      required: true,
    },
    options: {
      type: Array,
    },
    hasPlaceholder: {
      type: Boolean,
      default: true,
    },
    name: {
      type: String,
      required: true,
    },
  },

  data() {
    return {
      internalValue: '',
    };
  },

  watch: {
    internalValue (newInternalValue) {
      this.$emit('input', {
        name: this.name,
        value: newInternalValue.trim(),
      });
    },
  },

  computed: {
    id () {
      return `form-select-${count++}`;
    },

    generatedOptions() {
      const generatedOptions = [];
      
      if (this.hasPlaceholder) {
        generatedOptions.push({
          value: '',
          label: 'Select an option',
          disabled: false,
        });
      }

      this.options.forEach((option) => {
        generatedOptions.push({
          value: option.value,
          label: option.label || option.value,
          disabled: option.disabled || false,
        });
      });

      return generatedOptions;
    }
  },
}
</script>