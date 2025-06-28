<script setup>
import { ref, onMounted, defineExpose } from 'vue'
import { useTaskStore } from '@/stores/taskStore'

const showModal = ref(false)
const isEdit = ref(false)
const form = ref({
    id: null,
    nome: '',
    descricao: '',
    data_limite: '',
    completed: false
})
const error = ref('')

const taskStore = useTaskStore()

onMounted(() => {
    taskStore.fetchTasks()
})

function openAddModal() {
    isEdit.value = false
    form.value = { id: null, nome: '', descricao: '', data_limite: '', completed: false }
    error.value = ''
    showModal.value = true
}

function openEditModal(task) {
    isEdit.value = true
    form.value = { ...task }
    error.value = ''
    showModal.value = true
}

async function saveTask() {
    error.value = ''
    if (!form.value.nome) {
        error.value = 'O nome é obrigatório.'
        return
    }
    try {
        if (isEdit.value) {
            await taskStore.updateTask(form.value.id, form.value)
        } else {
            await taskStore.createTask(form.value)
        }
        showModal.value = false
    } catch (e) {
        error.value = e.message
    }
}

async function deleteTask(id) {
    if (!confirm('Tem certeza que deseja deletar esta tarefa?')) return
    await taskStore.deleteTask(id)
}

async function toggleCompleted(task) {
    await taskStore.toggleTask(task.id)
}

defineExpose({ openAddModal })
</script>

<template>
    <div class="max-w-2xl mx-auto mt-10 bg-white rounded-xl shadow-lg p-8 font-sans">
        <div v-if="taskStore.loading" class="text-center py-8 text-blue-500">
            <svg class="animate-spin h-6 w-6 mx-auto mb-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg>
            Carregando...
        </div>
        <ul v-else class="divide-y divide-gray-200">
            <li v-for="task in taskStore.tasks" :key="task.id" class="flex items-center justify-between py-4">
                <div class="flex items-center gap-3">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" :checked="task.finalizado"
                            @change="toggleCompleted(task)" />
                        <div
                            class="w-10 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-400 rounded-full peer peer-checked:bg-blue-600 transition">
                        </div>
                        <div
                            class="absolute left-1 top-1 bg-white w-4 h-4 rounded-full shadow peer-checked:translate-x-4 transition">
                        </div>
                    </label>
                    <span :class="['font-medium', task.finalizado ? 'line-through text-gray-400' : 'text-gray-800']">
                        {{ task.nome }}
                    </span>
                    <span v-if="!task.finalizado && task.data_limite && new Date(task.data_limite) < new Date()"
                        class="inline-block px-2 py-1 text-xs rounded bg-red-100 text-red-700 font-semibold">
                        Vencida
                    </span>
                    <span v-else class="inline-block px-2 py-1 text-xs rounded font-semibold"
                        :class="task.finalizado ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'">
                        {{ task.finalizado ? 'Concluída' : 'Pendente' }}
                    </span>
                    <span v-if="task.data_limite" class="ml-2 text-xs text-gray-400">
                        {{ new Date(task.data_limite).toLocaleDateString('pt-BR') }}
                    </span>
                </div>
                <div class="flex gap-2">
                    <button @click="openEditModal(task)"
                        class="px-3 py-1 text-xs bg-yellow-100 text-yellow-700 rounded hover:bg-yellow-200 transition font-medium shadow-sm">
                        Editar
                    </button>
                    <button @click="deleteTask(task.id)"
                        class="px-3 py-1 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200 transition font-medium shadow-sm">
                        Deletar
                    </button>
                </div>
            </li>
        </ul>
        <div v-if="!taskStore.loading && taskStore.tasks.length === 0" class="text-center text-gray-500 py-8">
            Nenhuma tarefa encontrada.
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center"
            style="background: rgba(0,0,0,0.4);">
            <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md relative">
                <button @click="showModal = false"
                    class="absolute top-2 right-2 text-gray-400 hover:text-gray-600 text-xl">&times;</button>
                <h3 class="text-xl font-bold mb-4">{{ isEdit ? 'Editar Tarefa' : 'Nova Tarefa' }}</h3>
                <form @submit.prevent="saveTask" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nome</label>
                        <input v-model="form.nome" type="text" required
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-400"
                            :class="{ 'border-red-500': error && !form.nome }" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Descrição</label>
                        <input v-model="form.descricao" type="text"
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-400" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Data Limite</label>
                        <input v-model="form.data_limite" type="date"
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-400" />
                    </div>
                    <div v-if="error" class="text-red-600 text-sm">{{ error }}</div>
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" @click="showModal = false"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-200 text-white rounded hover:bg-blue-400 transition font-semibold">
                            Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
