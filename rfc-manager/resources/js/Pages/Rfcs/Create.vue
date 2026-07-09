<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

defineOptions({ layout: AppLayout });

const props = defineProps({
    users: Array,
    statuses: Array,
    severities: Array,
    priorities: Array,
});

const form = useForm({
    subject: '',
    description: '',
    severity: 'medium',
    downtime_possible: false,
    priority: 'medium',
    performers: [],
    stakeholders: [],
    approvers: [],
    notes: '',
    recurrent_reminder_enabled: true,
});

const submit = () => {
    form.post(route('rfcs.store'), {
        onSuccess: () => {
            form.reset();
        },
    });
};

const toggleSelection = (type, userId) => {
    const index = form[type].indexOf(userId);
    if (index === -1) {
        form[type].push(userId);
    } else {
        form[type].splice(index, 1);
    }
};

const isSelected = (type, userId) => form[type].includes(userId);
</script>

<template>
    <Head title="New RFC" />

    <div class="max-w-3xl">
        <div class="md:flex md:items-center md:justify-between">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">New Request For Change</h2>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <Link :href="route('rfcs.index')" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Cancel
                </Link>
            </div>
        </div>

        <form @submit.prevent="submit" class="mt-6 space-y-6 bg-white dark:bg-gray-800 shadow px-4 py-5 sm:p-6 rounded-lg">
            <div v-if="$page.props.flash?.error" class="rounded-md bg-red-50 p-4">
                <p class="text-sm text-red-800">{{ $page.props.flash.error }}</p>
            </div>

            <div v-if="form.errors.subject" class="rounded-md bg-red-50 p-4">
                <p class="text-sm text-red-800">{{ form.errors.subject }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subject</label>
                <input v-model="form.subject" type="text" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" placeholder="Brief summary of the change" />
                <p v-if="form.errors.subject" class="mt-1 text-sm text-red-600">{{ form.errors.subject }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                <textarea v-model="form.description" rows="4" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" placeholder="Detailed description of the change" />
                <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Severity</label>
                    <select v-model="form.severity" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                        <option v-for="(label, key) in severities" :key="key" :value="key">{{ label }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Priority</label>
                    <select v-model="form.priority" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                        <option v-for="(label, key) in priorities" :key="key" :value="key">{{ label }}</option>
                    </select>
                </div>
                <div class="flex items-center h-full pt-6">
                    <input v-model="form.downtime_possible" id="downtime_possible" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" />
                    <label for="downtime_possible" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">Downtime possible</label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Performers</label>
                <p class="text-sm text-gray-500">Select the individuals performing the work.</p>
                <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2">
                    <label v-for="user in users" :key="'performer-' + user.id" class="flex items-center p-3 border rounded cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 dark:border-gray-600">
                        <input type="checkbox" :value="user.id" :checked="isSelected('performers', user.id)" @change="toggleSelection('performers', user.id)" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" />
                        <span class="ml-3 text-sm text-gray-900 dark:text-gray-100">{{ user.name }}</span>
                    </label>
                </div>
                <p v-if="form.errors.performers" class="mt-1 text-sm text-red-600">{{ form.errors.performers }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stakeholders</label>
                <p class="text-sm text-gray-500">Select stakeholders to notify.</p>
                <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2">
                    <label v-for="user in users" :key="'stakeholder-' + user.id" class="flex items-center p-3 border rounded cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 dark:border-gray-600">
                        <input type="checkbox" :value="user.id" :checked="isSelected('stakeholders', user.id)" @change="toggleSelection('stakeholders', user.id)" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" />
                        <span class="ml-3 text-sm text-gray-900 dark:text-gray-100">{{ user.name }}</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Approvers</label>
                <p class="text-sm text-gray-500">Select approvers and set their type.</p>
                <div class="mt-2 space-y-2">
                    <div v-for="user in users" :key="'approver-' + user.id" class="flex items-center gap-2 p-3 border rounded dark:border-gray-600">
                        <input type="checkbox" :value="user.id" :checked="isSelected('approvers', user.id)" @change="toggleSelection('approvers', user.id)" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" />
                        <span class="text-sm text-gray-900 dark:text-gray-100">{{ user.name }}</span>
                    </div>
                </div>
                <p v-if="form.errors.approvers" class="mt-1 text-sm text-red-600">{{ form.errors.approvers }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                <textarea v-model="form.notes" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" placeholder="Any additional notes" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reminder Settings</label>
                <label class="inline-flex items-center mt-2">
                    <input v-model="form.recurrent_reminder_enabled" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" />
                    <span class="ml-2 text-sm text-gray-900 dark:text-gray-100">Enable recurrent reminders</span>
                </label>
            </div>

            <div class="flex justify-end">
                <button type="submit" :disabled="form.processing" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50">
                    Save RFC
                </button>
            </div>
        </form>
    </div>
</template>
