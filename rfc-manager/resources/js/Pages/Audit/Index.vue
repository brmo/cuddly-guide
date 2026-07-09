<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineOptions({ layout: AppLayout });

const props = defineProps({
    auditLogs: Object,
    filters: Object,
    rfcs: Array,
    users: Array,
    actions: Array,
});

const form = useForm({
    rfc_id: props.filters.rfc_id || '',
    user_id: props.filters.user_id || '',
    action: props.filters.action || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
});

const submit = () => {
    form.get(route('audit.index'), { preserveScroll: true });
};
</script>

<template>
    <Head title="Audit Logs" />

    <div class="space-y-6">
        <div class="md:flex md:items-center md:justify-between">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl">Audit Logs</h2>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow px-4 py-5 sm:p-6 rounded-lg">
            <form @submit.prevent="submit" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">RFC</label>
                    <select v-model="form.rfc_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                        <option value="">All</option>
                        <option v-for="rfc in rfcs" :key="rfc.id" :value="String(rfc.id)">#{{ rfc.id }} - {{ rfc.subject }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">User</label>
                    <select v-model="form.user_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                        <option value="">All</option>
                        <option v-for="user in users" :key="user.id" :value="String(user.id)">{{ user.name }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Action</label>
                    <select v-model="form.action" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                        <option value="">All</option>
                        <option v-for="action in actions" :key="action" :value="action">{{ action }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">From</label>
                    <input v-model="form.date_from" type="date" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">To</label>
                    <input v-model="form.date_to" type="date" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" />
                </div>

                <div class="sm:col-span-2 lg:col-span-5 flex justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-md">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">RFC</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Details</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="log in auditLogs.data" :key="log.id">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ log.created_at }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ log.user?.name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100" v-if="log.rfc">
                            <Link :href="route('rfcs.show', log.rfc.id)" class="text-indigo-600 hover:text-indigo-900">
                                #{{ log.rfc.id }} - {{ log.rfc.subject }}
                            </Link>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100" v-else>-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ log.action }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span v-if="log.new_values" class="text-xs">{{ JSON.stringify(log.new_values).slice(0, 100) }}...</span>
                        </td>
                    </tr>
                    <tr v-if="!auditLogs.data?.length">
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No audit logs found.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
