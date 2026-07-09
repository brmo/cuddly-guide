<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

defineOptions({ layout: AppLayout });

const props = defineProps({
    rfcs: Object,
});

const statusColors = {
    draft: 'bg-gray-100 text-gray-800',
    pending_approval: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    scheduled: 'bg-blue-100 text-blue-800',
    in_progress: 'bg-indigo-100 text-indigo-800',
    completed: 'bg-emerald-100 text-emerald-800',
    rejected: 'bg-red-100 text-red-800',
    recalled: 'bg-orange-100 text-orange-800',
};

const form = useForm({});

const handleSubmit = (rfc) => {
    form.post(route('rfcs.submit', rfc.id), { preserveScroll: true });
};

const handleRecall = (rfc) => {
    if (!confirm('Are you sure you want to recall this RFC?')) return;
    form.post(route('rfcs.recall', rfc.id), { preserveScroll: true });
};

const handleResubmit = (rfc) => {
    if (!confirm('Resubmit this RFC for approval?')) return;
    form.post(route('rfcs.resubmit', rfc.id), { preserveScroll: true });
};
</script>

<template>
    <Head title="RFCs" />

    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Requests For Change</h1>
            <Link :href="route('rfcs.create')" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                New RFC
            </Link>
        </div>

        <div v-if="$page.props.flash?.success" class="rounded-md bg-green-50 dark:bg-green-900/20 p-4">
            <p class="text-sm text-green-800 dark:text-green-200">{{ $page.props.flash.success }}</p>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                <li v-for="rfc in rfcs.data" :key="rfc.id">
                    <Link :href="route('rfcs.show', rfc.id)" class="block hover:bg-gray-50 dark:hover:bg-gray-700 px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-medium text-indigo-600 truncate">{{ rfc.subject }}</p>
                                    <span :class="['px-2 inline-flex text-xs leading-5 font-semibold rounded-full', statusColors[rfc.status] || 'bg-gray-100 text-gray-800']">
                                        {{ rfc.status.replace('_', ' ') }}
                                    </span>
                                </div>
                                <div class="mt-2 flex items-center text-sm text-gray-500">
                                    <span>By {{ rfc.creator?.name }}</span>
                                    <span class="mx-2">·</span>
                                    <span>Priority: {{ rfc.priority }}</span>
                                    <span class="mx-2">·</span>
                                    <span>Scheduled: {{ rfc.scheduled_at || 'TBD' }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2" @click.stop>
                                <button v-if="rfc.status === 'draft' || rfc.status === 'recalled'" @click="handleSubmit(rfc)" class="text-sm text-green-600 hover:text-green-900">Submit</button>
                                <button v-if="rfc.status === 'pending_approval'" @click="handleRecall(rfc)" class="text-sm text-red-600 hover:text-red-900">Recall</button>
                                <button v-if="rfc.status === 'rejected' || rfc.status === 'recalled'" @click="handleResubmit(rfc)" class="text-sm text-blue-600 hover:text-blue-900">Resubmit</button>
                            </div>
                        </div>
                    </Link>
                </li>
                <li v-if="!rfcs.data?.length" class="px-4 py-8 text-center text-gray-500">
                    No RFCs found. Create your first one to get started.
                </li>
            </ul>
        </div>

        <div v-if="rfcs.links" class="flex items-center justify-between border-t border-gray-200 pt-4">
            <Link v-if="rfcs.links.prev" :href="rfcs.links.prev" class="text-sm text-gray-600 hover:text-gray-900">Previous</Link>
            <span v-else class="text-sm text-gray-400">Previous</span>
            <span class="text-sm text-gray-500">Page {{ rfcs.current_page }} of {{ rfcs.last_page }}</span>
            <Link v-if="rfcs.links.next" :href="rfcs.links.next" class="text-sm text-gray-600 hover:text-gray-900">Next</Link>
            <span v-else class="text-sm text-gray-400">Next</span>
        </div>
    </div>
</template>
