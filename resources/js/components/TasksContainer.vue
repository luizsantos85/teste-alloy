<script setup>
import { ref, onMounted, defineExpose } from 'vue'

const tasks = ref([])
const loading = ref(true)
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

async function fetchTasks() {
    loading.value = true
    try {
        const response = await fetch('/api/tasks')
        if (!response.ok) throw new Error('Erro ao buscar tarefas')
        tasks.value = await response.json()
    } catch (e) {
        tasks.value = []
    } finally {
        loading.value = false
    }
}

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
        let response
        if (isEdit.value) {
            response = await fetch(`/api/tasks/${form.value.id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(form.value)
            })
        } else {
            response = await fetch('/api/tasks', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(form.value)
            })
        }
        if (!response.ok) {
            const data = await response.json()
            throw new Error(data.message || 'Erro ao salvar tarefa')
        }
        showModal.value = false
        await fetchTasks()
    } catch (e) {
        error.value = e.message
    }
}

async function deleteTask(id) {
    if (!confirm('Tem certeza que deseja deletar esta tarefa?')) return
    try {
        const response = await fetch(`/api/tasks/${id}`, { method: 'DELETE' })
        if (!response.ok) throw new Error('Erro ao deletar tarefa')
        tasks.value = tasks.value.filter(task => task.id !== id)
    } catch (e) {
        alert('Erro ao deletar tarefa')
    }
}

async function toggleCompleted(task) {
    try {
        const response = await fetch(`/api/tasks/${task.id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ...task, completed: !task.completed })
        })
        if (!response.ok) throw new Error('Erro ao atualizar tarefa')
        task.completed = !task.completed
    } catch (e) {
        alert('Erro ao atualizar status da tarefa')
    }
}

onMounted(fetchTasks)
defineExpose({ openAddModal })
</script>

<template>
    <div class="tasks-container">
        <div v-if="loading" class="loading">Carregando...</div>
        <ul v-else class="tasks-list">
            <li v-for="task in tasks" :key="task.id" class="task-item">
                <div class="task-info">
                    <div class="task-actions">
                        <label class="toggle-switch">
                            <input type="checkbox" :checked="task.completed" @change="toggleCompleted(task)" />
                            <span class="slider"></span>
                        </label>
                    </div>
                    <span :class="['task-title', task.completed ? 'completed' : '']">
                        {{ task.nome }}
                    </span>
                    <span v-if="!task.completed && task.data_limite && new Date(task.data_limite) < new Date()"
                        class="status status-overdue">
                        Vencida
                    </span>
                    <span v-else class="status" :class="task.completed ? 'status-done' : 'status-pending'">
                        {{ task.completed ? 'Concluída' : 'Pendente' }}
                    </span>
                    <span v-if="task.data_limite" class="task-date">
                        {{ new Date(task.data_limite).toLocaleDateString('pt-BR') }}
                    </span>
                </div>
                <div class="task-actions">
                    <button @click="openEditModal(task)" class="btn btn-edit">Editar</button>
                    <button @click="deleteTask(task.id)" class="btn btn-delete">Deletar</button>
                </div>
            </li>
        </ul>
        <div v-if="!loading && tasks.length === 0" class="no-tasks">
            Nenhuma tarefa encontrada.
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="modal-overlay">
            <div class="modal">
                <button @click="showModal = false" class="modal-close">&times;</button>
                <h3>{{ isEdit ? 'Editar Tarefa' : 'Nova Tarefa' }}</h3>
                <form @submit.prevent="saveTask" class="modal-form">
                    <div class="form-group">
                        <label>Nome</label>
                        <input v-model="form.nome" type="text" required
                            :class="{ 'input-error': error && !form.nome }" />
                    </div>
                    <div class="form-group">
                        <label>Descrição</label>
                        <input v-model="form.descricao" type="text" />
                    </div>
                    <div class="form-group">
                        <label>Data Limite</label>
                        <input v-model="form.data_limite" type="date" />
                    </div>
                    <div v-if="error" class="form-error">{{ error }}</div>
                    <div class="modal-actions">
                        <button type="button" @click="showModal = false" class="btn btn-cancel">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<style scoped>
.tasks-container {
    max-width: 800px;
    margin: 40px auto;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 16px rgba(0, 0, 0, 0.08);
    padding: 32px 24px;
    font-family: Arial, sans-serif;
}

.btn {
    padding: 8px 18px;
    border: none;
    border-radius: 6px;
    font-size: 1rem;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-primary {
    background: #2563eb;
    color: #fff;
}

.btn-primary:hover {
    background: #1d4ed8;
}

.btn-edit {
    background: #facc15;
    color: #7c5700;
    margin-right: 8px;
}

.btn-edit:hover {
    background: #fde047;
}

.btn-delete {
    background: #f87171;
    color: #fff;
}

.btn-delete:hover {
    background: #dc2626;
}

.loading {
    text-align: center;
    color: #2563eb;
    font-size: 1.1rem;
    padding: 32px 0;
}

.tasks-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.task-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 0;
    border-bottom: 1px solid #f1f1f1;
}

.task-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.task-title {
    font-weight: 500;
    font-size: 1.1rem;
    color: #222;
}

.task-title.completed {
    text-decoration: line-through;
    color: #bdbdbd;
}

.status {
    padding: 2px 10px;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: bold;
}

.status-overdue {
    background: #fee2e2;
    color: #dc2626;
}

.status-done {
    background: #bbf7d0;
    color: #166534;
}

.status-pending {
    background: #fef9c3;
    color: #a16207;
}

.task-date {
    font-size: 0.85rem;
    color: #888;
    margin-left: 8px;
}

.task-actions {
    display: flex;
    gap: 6px;
}

.no-tasks {
    text-align: center;
    color: #888;
    padding: 32px 0;
}

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.13);
    padding: 32px 28px 24px 28px;
    min-width: 320px;
    position: relative;
}

.modal-close {
    position: absolute;
    top: 12px;
    right: 16px;
    background: none;
    border: none;
    font-size: 2rem;
    color: #bbb;
    cursor: pointer;
}

.modal-close:hover {
    color: #222;
}

.modal-form .form-group {
    margin-bottom: 18px;
    display: flex;
    flex-direction: column;
}

.modal-form label {
    font-size: 0.95rem;
    margin-bottom: 4px;
    color: #333;
}

.modal-form input[type="text"],
.modal-form input[type="date"] {
    padding: 8px 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 1rem;
}

.input-error {
    border-color: #dc2626 !important;
}

.form-check {
    flex-direction: row;
    align-items: center;
    gap: 8px;
}

.form-error {
    color: #dc2626;
    font-size: 0.95rem;
    margin-bottom: 8px;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 12px;
}

.btn-cancel {
    background: #e5e7eb;
    color: #222;
}

.btn-cancel:hover {
    background: #d1d5db;
}

.toggle-switch {
    position: relative;
    display: inline-block;
    width: 38px;
    height: 22px;
    margin-right: 12px;
    vertical-align: middle;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .3s;
    border-radius: 22px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 16px;
    width: 16px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .3s;
    border-radius: 50%;
}

.toggle-switch input:checked+.slider {
    background-color: #2563eb;
}

.toggle-switch input:checked+.slider:before {
    transform: translateX(16px);
}
</style>
