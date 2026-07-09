<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';

defineOptions({ layout: AppLayout });

const props = defineProps({
    rfc: Object,
});

const submitForm = useForm({});
const rejectForm = useForm({ comments: '' });

const handleSubmit = () => {
    submitForm.post(route('rfcs.submit', props.rfc.id), { preserveScroll: true });
};

const handleApprove = () => {
    submitForm.post(route('rfcs.approve', props.rfc.id), { preserveScroll: true });
};

const handleReject = () => {
    rejectForm.post(route('rfcs.reject', props.rfc.id), { preserveScroll: true });
};

const handleRecall = () => {
    if (!confirm('Are you sure you want to recall this RFC?')) return;
    submitForm.post(route('rfcs.recall', props.rfc.id), { preserveScroll: true });
};

const handleResubmit = () => {
    if (!confirm('Resubmit this RFC for approval?')) return;
    submitForm.post(route('rfcs.resubmit', props.rfc.id), { preserveScroll: true });
};

const handleSchedule = useForm({ scheduled_at: '' });
const submitSchedule = () => {
    handleSchedule.post(route('rfcs.schedule', props.rfc.id), { preserveScroll: true });
};
</script>

<template>
    <Head :title="rfc.subject" />

    <div class="max-w-4xl">
        <div class="md:flex md:items-center md:justify-between">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-3">
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl">{{ rfc.subject }}</h2>
                    <span :class="['px-2 inline-flex text-xs leading-5 font-semibold rounded-full', rfc.status === 'pending_approval' ? 'bg-yellow-100 text-yellow-800' : rfc.status === 'approved' ? 'bg-green-100 text-green-800' : rfc.status === 'scheduled' ? 'bg-blue-100 text-blue-800' : rfc.status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800']">
                        {{ rfc.status.replace('_', ' ') }}
                    </span>
                </div>
                <p class="mt-1 text-sm text-gray-500">RFC #{{ rfc.id }} · Created by {{ rfc.creator?.name }} on {{ rfc.created_at }}</p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4 gap-2">
                <Link v-if="rfc.status === 'draft' || rfc.status === 'recalled'" @click.prevent="handleSubmit" href="#" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                    Submit for Approval
                </Link>
                <button v-if="rfc.status === 'draft'" :href="route('rfcs.edit', rfc.id)" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Edit
                </button>
                <Link v-if="rfc.status === 'pending_approval'" @click.prevent="handleRecall" href="#" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                    Recall
                </Link>
                <Link v-if="rfc.status === 'rejected' || rfc.status === 'recalled'" @click.prevent="handleResubmit" href="#" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                    Resubmit
                </Link>
                <Link v-if="rfc.isFullyApproved && !rfc.scheduled_at" :href="route('rfcs.edit', rfc.id)" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    Schedule
                </Link>
                <button v-if="rfc.status === 'scheduled'" @click="submitForm.post(route('rfcs.start', rfc.id))" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    Start Change
                </button>
                <button v-if="rfc.status === 'in_progress'" @click="submitForm.post(route('rfcs.complete', rfc.id))" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700">
                    Complete
                </button>
            </div>
        </div>

        <div class="mt-6 space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow px-4 py-5 sm:p-6 rounded-lg">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">Details</h3>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Severity</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 capitalize">{{ rfc.severity }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Priority</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 capitalize">{{ rfc.priority }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Downtime Possible</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ rfc.downtime_possible ? 'Yes' : 'No' }}</dd>
                    </div>
                </div>
                <div class="mt-4">
                    <dt class="text-sm font-medium text-gray-500">Description</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ rfc.description }}</dd>
                </div>
                <div v-if="rfc.rejection_reason" class="mt-4">
                    <dt class="text-sm font-medium text-red-500">Rejection Reason</dt>
                    <dd class="mt-1 text-sm text-red-700 dark:text-red-300">{{ rfc.rejection_reason }}</dd>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <div class="bg-white dark:bg-gray-800 shadow px-4 py-5 sm:p-6 rounded-lg">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">Performers</h3>
                    <ul class="mt-4 divide-y divide-gray-200 dark:divide-gray-700">
                        <li v-for="performer in rfc.performers" :key="performer.id" class="py-2">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ performer.name }}</p>
                            <p class="text-xs text-gray-500">Reminder: {{ performer.pivot?.reminder_minutes ?? 30 }} min</p>
                        </li>
                    </ul>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow px-4 py-5 sm:p-6 rounded-lg">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">Stakeholders</h3>
                    <ul class="mt-4 divide-y divide-gray-200 dark:divide-gray-700">
                        <li v-for="stakeholder in rfc.stakeholders" :key="stakeholder.id" class="py-2">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ stakeholder.name }}</p>
                            <p class="text-xs text-gray-500">Notified: {{ stakeholder.pivot?.created_at }}</p>
                        </li>
                    </ul>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow px-4 py-5 sm:p-6 rounded-lg">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">Approvers</h3>
                    <ul class="mt-4 divide-y divide-gray-200 dark:divide-gray-700">
                        <li v-for="approver in rfc.approvers" :key="approver.id" class="py-2">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ approver.name }}</p>
                                    <p class="text-xs text-gray-500 capitalize">{{ approver.pivot?.type }}</p>
                                </div>
                                <span :class="['px-2 py-1 text-xs font-medium rounded-full', approver.pivot?.status === 'approved' ? 'bg-green-100 text-green-800' : approver.pivot?.status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800']">
                                    {{ approver.pivot?.status }}
                                </span>
                            </div>
                            <p v-if="approver.pivot?.comments" class="mt-1 text-xs text-gray-600 dark:text-gray-400">{{ approver.pivot.comments }}</p>
                        </li>
                    </ul>
                </div>
            </div>

            <div v-if="rfc.audits?.length" class="bg-white dark:bg-gray-800 shadow px-4 py-5 sm:p-6 rounded-lg">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">Audit History</h3>
                <ul class="mt-4 divide-y divide-gray-200 dark:divide-gray-700">
                    <li v-for="log in rfc.audits" :key="log.id" class="py-3">
                        <div class="flex justify-between">
                            <div>
                                <p class="text-sm text-gray-900 dark:text-gray-100">
                                    <span class="font-medium">{{ log.user?.name }}</span> - {{ log.action }}
                                </p>
                                <p class="text-xs text-gray-500">{{ log.created_at }}</p>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
