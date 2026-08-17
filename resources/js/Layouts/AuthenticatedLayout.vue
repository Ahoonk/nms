<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const page = usePage();
const can = (permission) => page.props.auth.user?.permissions?.includes(permission);
const isSuperAdmin = () => page.props.auth.user?.is_super_admin;

const isMonitoringActive = () =>
    route().current('monitoring.*') || route().current('notifications.index');

const isAdminActive = () =>
    route().current('companies.*') ||
    route().current('users.*') ||
    route().current('sites.*') ||
    route().current('devices.*') ||
    route().current('zabbix-connections.*') ||
    route().current('activity-trail.index');
</script>

<template>
    <div>
        <div class="min-h-screen bg-gradient-to-br from-slate-50 via-slate-100 to-sky-100 text-slate-900 dark:from-slate-950 dark:via-slate-950 dark:to-slate-900 dark:text-slate-100">
            <nav
                class="relative z-50 border-b border-white/70 bg-white/90 backdrop-blur dark:border-slate-800 dark:bg-slate-950/90"
            >
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 items-center justify-between gap-4">
                        <div class="flex min-w-0 items-center gap-4">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationLogo
                                        class="block h-9 w-auto fill-current text-slate-900 dark:text-slate-100"
                                    />
                                </Link>
                            </div>

                            <div class="ms-4 hidden flex-col justify-center sm:flex">
                                <span class="text-sm font-semibold text-slate-900 dark:text-white">
                                    NMS Portal
                                </span>
                                <span class="text-xs text-slate-500 dark:text-slate-400">
                                    Portal Monitoring System
                                </span>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden min-w-0 flex-1 items-center gap-1 whitespace-nowrap sm:-my-px sm:ms-6 sm:flex lg:gap-2">
                                <NavLink
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                >
                                    Dashboard
                                </NavLink>
                                <Dropdown align="left" width="64">
                                    <template #trigger>
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-full px-2.5 py-2 text-sm font-medium transition duration-150 ease-in-out"
                                            :class="isMonitoringActive() ? 'bg-sky-500/10 text-sky-700 dark:text-sky-300' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'"
                                        >
                                            Monitoring
                                            <svg class="ms-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </template>
                                    <template #content>
                                        <DropdownLink v-if="can('monitoring.view')" :href="route('monitoring.overview')">Overview</DropdownLink>
                                        <DropdownLink v-if="can('monitoring.view')" :href="route('monitoring.problems')">Problems</DropdownLink>
                                        <DropdownLink v-if="can('monitoring.view')" :href="route('monitoring.events')">Events</DropdownLink>
                                        <DropdownLink v-if="can('monitoring.view')" :href="route('monitoring.hosts')">Hosts</DropdownLink>
                                        <DropdownLink v-if="can('monitoring.view')" :href="route('monitoring.availability')">Availability</DropdownLink>
                                        <DropdownLink v-if="can('monitoring.view')" :href="route('monitoring.graphs')">Graphs</DropdownLink>
                                        <DropdownLink v-if="can('monitoring.view')" :href="route('notifications.index')">Notifications</DropdownLink>
                                    </template>
                                </Dropdown>
                                <Dropdown v-if="isSuperAdmin()" align="left" width="64">
                                    <template #trigger>
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-full px-2.5 py-2 text-sm font-medium transition duration-150 ease-in-out"
                                            :class="isAdminActive() ? 'bg-sky-500/10 text-sky-700 dark:text-sky-300' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'"
                                        >
                                            Administration
                                            <svg class="ms-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </template>
                                    <template #content>
                                        <DropdownLink v-if="can('company.manage')" :href="route('activity-trail.index')">Activity Trail</DropdownLink>
                                        <DropdownLink :href="route('users.index')">Users</DropdownLink>
                                        <DropdownLink v-if="can('company.manage')" :href="route('companies.index')">Companies</DropdownLink>
                                        <DropdownLink v-if="can('site.manage')" :href="route('sites.index')">Sites</DropdownLink>
                                        <DropdownLink v-if="can('device.manage')" :href="route('devices.index')">Devices</DropdownLink>
                                        <DropdownLink v-if="can('zabbix.connection.manage')" :href="route('zabbix-connections.index')">Zabbix Connections</DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <div class="me-4 hidden items-center gap-2 lg:flex">
                                <span
                                    class="rounded-full border border-sky-500/20 bg-sky-500/10 px-3 py-1 text-xs font-medium text-sky-600 dark:text-sky-300"
                                >
                                    {{ $page.props.auth.user?.company?.name ?? 'Global Scope' }}
                                </span>
                                <span
                                    v-if="$page.props.auth.user?.roles?.length"
                                    class="rounded-full border border-slate-300 bg-white px-3 py-1 text-xs font-medium text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
                                >
                                    {{ $page.props.auth.user.roles[0] }}
                                </span>
                            </div>

                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-full border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-slate-500 transition duration-150 ease-in-out hover:bg-slate-100 hover:text-slate-700 focus:outline-none dark:bg-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                                        >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink
                                            :href="route('profile.edit')"
                                        >
                                            Profile
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            Log Out
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                            class="inline-flex items-center justify-center rounded-md p-2 text-slate-400 transition duration-150 ease-in-out hover:bg-white/70 hover:text-slate-500 focus:bg-white/70 focus:text-slate-500 focus:outline-none dark:text-slate-500 dark:hover:bg-slate-900 dark:hover:text-slate-300 dark:focus:bg-slate-900 dark:focus:text-slate-300"
                        >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                        >
                            Dashboard
                        </ResponsiveNavLink>
                        <div class="px-4 pt-3">
                            <div class="rounded-2xl bg-slate-100 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                Monitoring
                            </div>
                        </div>
                        <div class="grid gap-1 px-2">
                            <ResponsiveNavLink v-if="can('monitoring.view')" :href="route('monitoring.overview')" :active="route().current('monitoring.overview')">Overview</ResponsiveNavLink>
                            <ResponsiveNavLink v-if="can('monitoring.view')" :href="route('monitoring.problems')" :active="route().current('monitoring.problems')">Problems</ResponsiveNavLink>
                            <ResponsiveNavLink v-if="can('monitoring.view')" :href="route('monitoring.events')" :active="route().current('monitoring.events')">Events</ResponsiveNavLink>
                            <ResponsiveNavLink v-if="can('monitoring.view')" :href="route('monitoring.hosts')" :active="route().current('monitoring.hosts')">Hosts</ResponsiveNavLink>
                            <ResponsiveNavLink v-if="can('monitoring.view')" :href="route('monitoring.availability')" :active="route().current('monitoring.availability')">Availability</ResponsiveNavLink>
                            <ResponsiveNavLink v-if="can('monitoring.view')" :href="route('monitoring.graphs')" :active="route().current('monitoring.graphs')">Graphs</ResponsiveNavLink>
                            <ResponsiveNavLink v-if="can('monitoring.view')" :href="route('notifications.index')" :active="route().current('notifications.index')">Notifications</ResponsiveNavLink>
                        </div>

                        <template v-if="isSuperAdmin()">
                            <div class="px-4 pt-4">
                                <div class="rounded-2xl bg-slate-100 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                    Administration
                                </div>
                            </div>
                            <div class="grid gap-1 px-2">
                                <ResponsiveNavLink v-if="can('company.manage')" :href="route('activity-trail.index')" :active="route().current('activity-trail.index')">Activity Trail</ResponsiveNavLink>
                                <ResponsiveNavLink :href="route('users.index')" :active="route().current('users.*')">Users</ResponsiveNavLink>
                                <ResponsiveNavLink v-if="can('company.manage')" :href="route('companies.index')" :active="route().current('companies.index')">Companies</ResponsiveNavLink>
                                <ResponsiveNavLink v-if="can('site.manage')" :href="route('sites.index')" :active="route().current('sites.index')">Sites</ResponsiveNavLink>
                                <ResponsiveNavLink v-if="can('device.manage')" :href="route('devices.index')" :active="route().current('devices.index')">Devices</ResponsiveNavLink>
                                <ResponsiveNavLink v-if="can('zabbix.connection.manage')" :href="route('zabbix-connections.index')" :active="route().current('zabbix-connections.index')">Zabbix Connections</ResponsiveNavLink>
                            </div>
                        </template>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div
                        class="border-t border-gray-200 pb-1 pt-4 dark:border-gray-600"
                    >
                        <div class="px-4">
                            <div
                                class="text-base font-medium text-gray-800 dark:text-gray-200"
                            >
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Profile
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header
                class="relative z-0 border-b border-white/60 bg-white/60 backdrop-blur dark:border-slate-800 dark:bg-slate-950/60"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="relative z-0">
                <slot />
            </main>
        </div>
    </div>
</template>
