import { defineStore } from "pinia";

export const useTaskStore = defineStore("tasks", {
    state: () => ({
        tasks: [],
        loading: false,
    }),
    actions: {
        async fetchTasks() {
            this.loading = true;
            try {
                const response = await fetch("/api/tasks");
                if (!response.ok) throw new Error("Erro ao buscar tarefas");
                this.tasks = await response.json();
            } catch (e) {
                this.tasks = [];
            } finally {
                this.loading = false;
            }
        },
        async createTask(task) {
            this.loading = true;
            try {
                const response = await fetch("/api/tasks", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(task),
                });
                if (!response.ok) throw new Error("Erro ao criar tarefa");
                const newTask = await response.json();
                this.tasks.push(newTask);
                return newTask;
            } finally {
                this.loading = false;
            }
        },
        async updateTask(id, task) {
            this.loading = true;
            try {
                const response = await fetch(`/api/tasks/${id}`, {
                    method: "PUT",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(task),
                });
                if (!response.ok) throw new Error("Erro ao atualizar tarefa");
                const updated = await response.json();
                const idx = this.tasks.findIndex((t) => t.id === id);
                if (idx !== -1) this.tasks[idx] = updated;
                return updated;
            } finally {
                this.loading = false;
            }
        },
        async deleteTask(id) {
            this.loading = true;
            try {
                const response = await fetch(`/api/tasks/${id}`, {
                    method: "DELETE",
                });
                if (!response.ok) throw new Error("Erro ao deletar tarefa");
                this.tasks = this.tasks.filter((t) => t.id !== id);
            } finally {
                this.loading = false;
            }
        },
        async toggleTask(id) {
            const task = this.tasks.find((t) => t.id === id);
            if (!task) return;
            this.loading = true;
            try {
                const response = await fetch(`/api/tasks/${id}/toggle`, {
                    method: "PATCH",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        ...task,
                        finalizado: !task.finalizado,
                    }),
                });
                if (!response.ok) throw new Error("Erro ao atualizar status");
                const updated = await response.json();
                const idx = this.tasks.findIndex((t) => t.id === id);
                if (idx !== -1) this.tasks[idx] = updated;
            } finally {
                this.loading = false;
            }
        },
    },
});
