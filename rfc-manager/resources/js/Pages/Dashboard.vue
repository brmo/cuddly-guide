<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

defineOptions({ layout: AppLayout });
</script>

<template>
    <div class="space-y-6">
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <div v-for="(stat, label) in {Total: $page.props.stats.total, 'Pending Approval': $page.props.stats.pending_approval, Approved: $page.props.stats.approved, Scheduled: $page.props.stats.scheduled}" :key="label" class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="text-3xl">{{ stat }}</div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">{{ label }}</dt>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="$page.props.stats.in_progress > 0 || $page.props.stats.completed > 0" class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <dl>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-500">In Progress</span>
                            <span class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $page.props.stats.in_progress }}</span>
                        </div>
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-sm font-medium text-gray-500">Completed</span>
                            <span class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $page.props.stats.completed }}</span>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">My Pending Approvals</h3>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    <p class="text-3xl font-semibold text-indigo-600">{{ $page.props.myPendingApprovals }}</p>
                    <p class="mt-1 text-sm text-gray-500">RFCs waiting for your approval</p>
                    <div class="mt-4">
                        <Link :href="route('rfcs.index')" class="text-sm text-indigo-600 hover:text-indigo-900">View all pending</Link>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">Upcoming Changes</h3>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    <ul v-if="$page.props.upcomingChanges.length > 0" class="divide-y divide-gray-200">
                        <li v-for="change in $page.props.upcomingChanges" :key="change.id" class="py-2">
                            <Link :href="route('rfcs.show', change.id)" class="text-sm text-gray-900 dark:text-gray-100 hover:text-indigo-600">
                                {{ change.subject }}
                            </Link>
                            <p class="text-xs text-gray-500">{{ change.scheduled_at }}</p>
                        </li>
                    </ul>
                    <p v-else class="text-sm text-gray-500">No upcoming changes.</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">Recent RFCs</h3>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    <ul v-if="$page.props.recentRfcs.length" class="divide-y divide-gray-200">
                        <li v-for="rfc in $page.props.recentRfcs" :key="rfc.id" class="py-2 flex justify-between items-center">
                            <Link :href="route('rfcs.show', rfc.id)" class="text-sm text-gray-900 dark:text-gray-100 hover:text-indigo-600 truncate flex-1">
                                {{ rfc.subject }}
                            </Link>
                            <span class="text-xs text-gray-500">{{ rfc.status }}</span>
                        </li>
                    </ul>
                    <p v-else class="text-sm text-gray-500">No recent RFCs.</p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">Recent Activity</h3>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    <ul v-if="$page.props.recentAudits.length" class="divide-y divide-gray-200">
                        <li v-for="log in $page.props.recentAudits" :key="log.id" class="py-2">
                            <div class="flex justify-between items-center">
                                <p class="text-sm text-gray-900 dark:text-gray-100">
                                    <span class="font-medium">{{ log.user?.name }}</span>
                                    {{ log.action }}
                                    on
                                    <Link v-if="log.rfc" :href="route('rfcs.show', log.rfc.id)" class="text-indigo-600 hover:text-indigo-900">
                                        #{{ log.rfc.id }}
                                    </Link>
                                </p>
                                <span class="text-xs text-gray-500">{{ log.created_at }}</span>
                            </div>
                        </li>
                    </ul>
                    <p v-else class="text-sm text-gray-500">No activity yet.</p>
                </div>
            </div>
        </div>
    </div>
</template>
