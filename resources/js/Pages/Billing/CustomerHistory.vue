<template>
    <AppLayout title="Customer Billing History">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Customer Billing History
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Customer Info Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6 mb-6">
                    <div class="flex flex-col md:flex-row justify-between">
                        <div>
                            <h3 class="text-lg font-semibold">{{ customer.full_name }}</h3>
                            <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-y-2 gap-x-8">
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Email</p>
                                    <p class="text-sm text-gray-900 dark:text-white">{{ customer.email || 'Not provided' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Phone</p>
                                    <p class="text-sm text-gray-900 dark:text-white">{{ customer.phone_number }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Address</p>
                                    <p class="text-sm text-gray-900 dark:text-white">{{ customer.address || 'Not provided' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Status</p>
                                    <p class="text-sm text-gray-900 dark:text-white">{{ customer.status }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 md:mt-0 md:text-right">
                            <div class="mb-2">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Package</span>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ customer.package }}</p>
                            </div>
                            <div class="mb-2">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Monthly Bill</span>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ formatCurrency(customer.monthly_bill) }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Billing Start Date</span>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ customer.billing_start_date || 'Not set' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <Link :href="route('billing.record-payment.show', customer.id)" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 ml-2">
                            Record Payment
                        </Link>
                    </div>
                </div>

                <!-- Billing History -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex justify-between mb-6">
                        <h3 class="text-lg font-semibold">Billing History</h3>
                        <Link :href="route('billing.all')" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                            Back to All Bills
                        </Link>
                    </div>

                    <div v-if="billingHistory.length === 0" class="text-center py-10">
                        <p class="text-gray-600 dark:text-gray-400">No billing history found for this customer.</p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Billing Period
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Amount
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Payment Date
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Payment Method
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Reference #
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="transaction in billingHistory" :key="transaction.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">{{ transaction.billing_period }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">{{ formatCurrency(transaction.amount) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="getStatusClass(transaction.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                            {{ capitalize(transaction.status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">{{ transaction.payment_date || 'Not paid' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">{{ transaction.payment_method || '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">{{ transaction.reference_number || '-' }}</div>
                                        <div v-if="transaction.notes" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            {{ transaction.notes }}
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    customer: Object,
    billingHistory: Array,
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'PKR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

const getStatusClass = (status) => {
    if (status === 'paid') {
        return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100';
    } else if (status === 'pending') {
        return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100';
    } else {
        return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100';
    }
};

const capitalize = (string) => {
    return string.charAt(0).toUpperCase() + string.slice(1);
};
</script>
