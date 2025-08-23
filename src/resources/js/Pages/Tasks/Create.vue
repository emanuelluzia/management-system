<template>
  <div>
    <h1>Create Task</h1>
    <form method="POST" action="/tasks">
      <input type="text" name="title" placeholder="Task title" />
      <button type="submit">Save</button>
    </form>
  </div>
</template>
