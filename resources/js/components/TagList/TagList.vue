<template>
  <ul class="list-unstyled d-flex flex-row m-n1">
    <slot></slot>
  </ul>
</template>

<script>
export default {
  name: 'TagList',
}
</script>

<style scoped>
.tag-list {
  padding: 0;
  margin: 0;
  list-style: none;
}
</style>