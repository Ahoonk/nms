<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
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
    company: {
        type: Object,
        default: null,
    },
    statusOptions: {
        type: Array,
        required: true,
    },
});

const form = useForm({
    name: props.company?.name ?? '',
    address: props.company?.address ?? '',
    logo: props.company?.logo ?? '',
    email: props.company?.email ?? '',
    phone: props.company?.phone ?? '',
    status: props.company?.status ?? 'active',
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
    <Head :title="mode === 'edit' ? 'Edit Company' : 'Create Company'" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-semibold text-slate-900 dark:text-white">
                    {{ mode === 'edit' ? 'Edit Company' : 'Create Company' }}
                </h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Maintain tenant metadata for multi-company monitoring.
                </p>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <form class="space-y-6" @submit.prevent="submit">
                        <div>
                            <InputLabel for="name" value="Company Name" />
                            <TextInput id="name" v-model="form.name" class="mt-1 block w-full" required autofocus />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div>
                            <InputLabel for="email" value="Email" />
                            <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <div>
                            <InputLabel for="phone" value="Phone" />
                            <TextInput id="phone" v-model="form.phone" class="mt-1 block w-full" />
                            <InputError class="mt-2" :message="form.errors.phone" />
                        </div>

                        <div>
                            <InputLabel for="logo" value="Logo URL" />
                            <TextInput id="logo" v-model="form.logo" class="mt-1 block w-full" />
                            <InputError class="mt-2" :message="form.errors.logo" />
                        </div>

                        <div>
                            <InputLabel for="address" value="Address" />
                            <textarea
                                id="address"
                                v-model="form.address"
                                rows="4"
                                class="mt-1 block w-full rounded-xl border-slate-300 bg-white text-slate-900 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            />
                            <InputError class="mt-2" :message="form.errors.address" />
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
                                :href="route('companies.index')"
                                class="inline-flex items-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            >
                                Cancel
                            </Link>
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                {{ mode === 'edit' ? 'Update Company' : 'Save Company' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
