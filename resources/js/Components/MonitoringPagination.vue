<script setup>
defineProps({
    pagination: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['change']);
</script>

<template>
    <div v-if="pagination && pagination.last_page > 1" class="mt-6 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:flex-row sm:items-center sm:justify-between">
        <div class="text-sm text-slate-500 dark:text-slate-400">
            Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }}
        </div>
        <div class="flex items-center gap-2">
            <button
                type="button"
                class="rounded-xl border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                :disabled="pagination.current_page <= 1"
                @click="emit('change', pagination.current_page - 1)"
            >
                Previous
            </button>
            <span class="rounded-xl bg-slate-100 px-3 py-2 text-sm font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                Page {{ pagination.current_page }} / {{ pagination.last_page }}
            </span>
            <button
                type="button"
                class="rounded-xl border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                :disabled="pagination.current_page >= pagination.last_page"
                @click="emit('change', pagination.current_page + 1)"
            >
                Next
            </button>
        </div>
    </div>
</template>
