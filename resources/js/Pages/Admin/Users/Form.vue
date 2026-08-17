<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    mode: {
        type: String,
        required: true,
    },
    action: {
        type: String,
        required: true,
    },
    method: {
        type: String,
        required: true,
    },
    user: {
        type: Object,
        default: null,
    },
    companies: {
        type: Array,
        required: true,
    },
    roleOptions: {
        type: Array,
        required: true,
    },
    statusOptions: {
        type: Array,
        required: true,
    },
});

const form = useForm({
    name: props.user?.name ?? '',
    email: props.user?.email ?? '',
    company_id: props.user?.company_id ?? '',
    role: props.user?.role ?? 'Viewer',
    status: props.user?.status ?? 'active',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    if (props.mode === 'edit') {
        form.put(props.action);
        return;
    }

    form.post(props.action);
};
</script>

<template>
    <Head :title="mode === 'edit' ? 'Edit User' : 'Create User'" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-semibold text-slate-900 dark:text-white">
                    {{ mode === 'edit' ? 'Edit User' : 'Create User' }}
                </h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Control account access, company scope, and role assignments from one place.
                </p>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <form class="space-y-6" @submit.prevent="submit">
                        <div>
                            <InputLabel for="name" value="Full Name" />
                            <TextInput id="name" v-model="form.name" class="mt-1 block w-full" required autofocus />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div>
                            <InputLabel for="email" value="Email" />
                            <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="company_id" value="Company" />
                                <select
                                    id="company_id"
                                    v-model="form.company_id"
                                    class="mt-1 block w-full rounded-xl border-slate-300 bg-white text-slate-900 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                                >
                                    <option value="">Global</option>
                                    <option v-for="company in companies" :key="company.id" :value="company.id">
                                        {{ company.name }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.company_id" />
                            </div>

                            <div>
                                <InputLabel for="role" value="Role" />
                                <select
                                    id="role"
                                    v-model="form.role"
                                    class="mt-1 block w-full rounded-xl border-slate-300 bg-white text-slate-900 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                                >
                                    <option v-for="option in roleOptions" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.role" />
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="password" :value="mode === 'edit' ? 'New Password' : 'Password'" />
                                <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full" :required="mode === 'create'" />
                                <InputError class="mt-2" :message="form.errors.password" />
                                <p v-if="mode === 'edit'" class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                                    Leave blank to keep the current password.
                                </p>
                            </div>

                            <div>
                                <InputLabel for="password_confirmation" value="Confirm Password" />
                                <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password" class="mt-1 block w-full" :required="mode === 'create'" />
                            </div>
                        </div>

                        <div>
                            <InputLabel for="status" value="Status" />
                            <select
                                id="status"
                                v-model="form.status"
                                class="mt-1 block w-full rounded-xl border-slate-300 bg-white text-slate-900 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            >
                                <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.status" />
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <Link
                                :href="route('users.index')"
                                class="inline-flex items-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            >
                                Cancel
                            </Link>
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                {{ mode === 'edit' ? 'Update User' : 'Save User' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
