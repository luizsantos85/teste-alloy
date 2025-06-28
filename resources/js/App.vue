<script setup>
import Appbar from '@/components/Appbar.vue';
import Footer from '@/components/Footer.vue';
import TasksContainer from '@/components/TasksContainer.vue';

import { ref } from 'vue'
const tasksContainerRef = ref(null)

function openAddModalFromAppbar() {
    tasksContainerRef.value?.openAddModal()
}
</script>

<template>
    <div class="content-tasks">
        <Appbar @add-task="openAddModalFromAppbar" />
        <TasksContainer ref="tasksContainerRef" />
        <Footer />
    </div>
</template>
