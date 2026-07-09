<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const showingNavigationDropdown = ref(false);

const navigation = [
    { name: 'Dashboard', href: route('dashboard'), icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { name: 'RFCs', href: route('rfcs.index'), icon: 'M9 12h3.75M9 15h3.75M9 18h3.75m9 0h-3.75M9 12h3.75M9 6h9M9 9h9' },
    { name: 'Users', href: route('users.index'), icon: 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.992 4.992 0 00-3.002-8.259M12 12a4 4 0 100-8 4 4 0 000 8zm0 0v7m0-7H9m3 3h-3' },
    { name: 'Audit Logs', href: route('audit.index'), icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01' },
];
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <nav class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="shrink-0 flex items-center">
                            <Link :href="route('dashboard')" class="text-xl font-bold text-gray-900 dark:text-gray-100">
                                {{ $page.props.appName }}
                            </Link>
                        </div>

                        <div class="hidden sm:flex sm:space-x-8 sm:ml-6">
                            <template v-for="item in navigation" :key="item.name">
                                <Link
                                    :href="item.href"
                                    :class="[route().current(item.href.split('/').pop()) ? 'border-indigo-500 text-gray-900 dark:text-gray-100' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300', 'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition duration-150']"
                                >
                                    {{ item.name }}
                                </Link>
                            </template>
                        </div>
                    </div>

                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <div class="ml-3 relative">
                            <template x-if="false">
                                <div class="flex items-center gap-3">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">
                                        {{ $page.props.auth.user.name }}
                                    </span>
                                    <Link
                                        :href="route('logout')"
                                        method="post"
                                        as="button"
                                        class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                                    >
                                        Log Out
                                    </Link>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="-mr-2 flex items-center sm:hidden">
                        <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out dark:hover:bg-gray-700">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div :class="{'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown}" class="sm:hidden">
                <div class="pt-2 pb-3 space-y-1">
                    <template v-for="item in navigation" :key="item.name">
                        <Link
                            :href="item.href"
                            :class="[route().current(item.href.split('/').pop()) ? 'bg-indigo-50 border-l-4 border-indigo-400 text-indigo-700 dark:bg-gray-700 dark:text-gray-300' : 'border-l-4 border-transparent text-gray-600 hover:bg-gray-50 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-gray-700', 'block pl-3 pr-4 py-2 text-base font-medium']"
                        >
                            {{ item.name }}
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <main class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <slot />
            </div>
        </main>
    </div>
</template>
