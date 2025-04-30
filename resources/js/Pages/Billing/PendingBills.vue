<template>
    <AppLayout title="Pending Bills">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Pending Bills
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-semibold">Customers with Pending Bills</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Showing overdue bills and those due within the next 7 days</p>
                        </div>
                        <div>
                            <Link :href="route('billing.generate')" method="post" as="button" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                Generate Monthly Bills
                            </Link>
                        </div>
                    </div>

                    <div v-if="pendingBills.length === 0" class="text-center py-10">
                        <p class="text-gray-600 dark:text-gray-400">No overdue or upcoming bills found.</p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Customer
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Contact
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Package
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Monthly Bill
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Next Billing
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Days Left
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="bill in pendingBills" :key="bill.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ bill.full_name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500 dark:text-gray-300">{{ bill.email }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-300">{{ bill.phone_number }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">{{ bill.package }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">{{ formatCurrency(bill.monthly_bill) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">{{ bill.next_billing_date }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="getDaysRemainingClass(bill.days_remaining)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                            <template v-if="bill.days_remaining >= 0">
                                                {{ bill.days_remaining }} days
                                            </template>
                                            <template v-else>
                                                Overdue by {{ Math.abs(bill.days_remaining) }} days
                                            </template>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link :href="route('billing.record-payment.show', bill.id)" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-200 mr-4">
                                            Record Payment
                                        </Link>
                                        <Link :href="route('billing.customer-history', bill.id)" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                                            History
                                        </Link>
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
    pendingBills: Array,
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'PKR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

const getDaysRemainingClass = (days) => {
    if (days < 0) {
        return 'bg-red-500 text-white dark:bg-red-600 dark:text-white'; // Overdue bills
    } else if (days <= 2) {
        return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100'; // Due very soon
    } else if (days <= 5) {
        return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100'; // Due soon
    } else {
        return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100'; // Due later
    }
};
</script>
