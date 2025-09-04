<template>
  <div :class="['p-4 rounded-lg shadow', cardClasses]">
    <h3 :class="['text-lg font-semibold', titleClasses]">{{ title }}</h3>
    <p class="text-2xl font-bold">{{ value }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  value: {
    type: [String, Number],
    required: true
  },
  color: {
    type: String,
    default: 'gray'
  }
});

const cardClasses = computed(() => {
  return {
    'bg-gray-50': props.color === 'gray',
    'bg-yellow-50': props.color === 'yellow',
    'bg-blue-50': props.color === 'blue',
    'bg-green-50': props.color === 'green',
    'bg-red-50': props.color === 'red',
  };
});

const titleClasses = computed(() => {
  return {
    'text-gray-600': props.color === 'gray',
    'text-yellow-600': props.color === 'yellow',
    'text-blue-600': props.color === 'blue',
    'text-green-600': props.color === 'green',
    'text-red-600': props.color === 'red',
  };
});
</script>