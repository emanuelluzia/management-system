<script setup>
defineProps({
  task: Object
})
</script>

<template>
  <div>
    <h1>Edit Task</h1>
    <form :action="`/tasks/${task.id}`" method="POST">
      <input type="hidden" name="_method" value="PUT" />
      <input type="text" name="title" :value="task.title" />
      <label>
        <input type="checkbox" name="completed" :checked="task.completed" value="1" />
        Completed
      </label>
      <button type="submit">Update</button>
    </form>
  </div>
</template>
