<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    mode: { type: String, required: true },
    action: { type: String, required: true },
    method: { type: String, required: true },
    site: { type: Object, default: null },
    companies: { type: Array, required: true },
    defaultCompanyId: { type: [Number, String], default: '' },
    canChooseCompany: { type: Boolean, default: false },
    statusOptions: { type: Array, required: true },
});

const form = useForm({
    company_id: props.site?.company_id ?? props.defaultCompanyId ?? '',
    name: props.site?.name ?? '',
    location: props.site?.location ?? '',
    latitude: props.site?.latitude ?? '',
    longitude: props.site?.longitude ?? '',
    wireguard_ip: props.site?.wireguard_ip ?? '',
    gateway: props.site?.gateway ?? '',
    timezone: props.site?.timezone ?? 'UTC',
    description: props.site?.description ?? '',
    status: props.site?.status ?? 'active',
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
    <Head :title="mode === 'edit' ? 'Edit Site' : 'Create Site'" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-semibold text-slate-900 dark:text-white">
                    {{ mode === 'edit' ? 'Edit Site' : 'Create Site' }}
                </h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Define physical or logical monitoring locations.</p>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <form class="space-y-6" @submit.prevent="submit">
                        <div>
                            <InputLabel for="company_id" value="Company" />
                            <select
                                id="company_id"
                                v-model="form.company_id"
                                :disabled="!canChooseCompany"
                                class="mt-1 block w-full rounded-xl border-slate-300 bg-white text-slate-900 shadow-sm focus:border-sky-500 focus:ring-sky-500 disabled:cursor-not-allowed disabled:bg-slate-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:disabled:bg-slate-900"
                            >
                                <option value="">Select company</option>
                                <option v-for="company in companies" :key="company.id" :value="company.id">
                                    {{ company.name }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.company_id" />
                        </div>

                        <div>
                            <InputLabel for="name" value="Site Name" />
                            <TextInput id="name" v-model="form.name" class="mt-1 block w-full" required autofocus />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div>
                            <InputLabel for="location" value="Location" />
                            <TextInput id="location" v-model="form.location" class="mt-1 block w-full" />
                            <InputError class="mt-2" :message="form.errors.location" />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="latitude" value="Latitude" />
                                <TextInput id="latitude" v-model="form.latitude" class="mt-1 block w-full" />
                                <InputError class="mt-2" :message="form.errors.latitude" />
                            </div>
                            <div>
                                <InputLabel for="longitude" value="Longitude" />
                                <TextInput id="longitude" v-model="form.longitude" class="mt-1 block w-full" />
                                <InputError class="mt-2" :message="form.errors.longitude" />
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="wireguard_ip" value="WireGuard IP" />
                                <TextInput id="wireguard_ip" v-model="form.wireguard_ip" class="mt-1 block w-full" />
                                <InputError class="mt-2" :message="form.errors.wireguard_ip" />
                            </div>
                            <div>
                                <InputLabel for="gateway" value="Gateway" />
                                <TextInput id="gateway" v-model="form.gateway" class="mt-1 block w-full" />
                                <InputError class="mt-2" :message="form.errors.gateway" />
                            </div>
                        </div>

                        <div>
                            <InputLabel for="timezone" value="Timezone" />
                            <TextInput id="timezone" v-model="form.timezone" class="mt-1 block w-full" />
                            <InputError class="mt-2" :message="form.errors.timezone" />
                        </div>

                        <div>
                            <InputLabel for="description" value="Description" />
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="4"
                                class="mt-1 block w-full rounded-xl border-slate-300 bg-white text-slate-900 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            />
                            <InputError class="mt-2" :message="form.errors.description" />
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
                            <Link :href="route('sites.index')" class="inline-flex items-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                                Cancel
                            </Link>
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                {{ mode === 'edit' ? 'Update Site' : 'Save Site' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
