<template>
    <AppLayout title="All Bills">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                All Bills
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex flex-col md:flex-row md:justify-between mb-6">
                        <div class="mb-4 md:mb-0">
                            <h3 class="text-lg font-semibold">Customer Billing Status</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Overview of all customers and their billing status</p>
                        </div>
                        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                            <Link :href="route('billing.pending-bills')" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-center">
                                Pending Bills
                            </Link>
                            <Link :href="route('billing.monthly-report')" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-center">
                                Monthly Report
                            </Link>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="relative">
                            <input 
                                type="text" 
                                v-model="search" 
                                placeholder="Search customers..." 
                                class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            >
                        </div>
                    </div>

                    <div v-if="filteredCustomers.length === 0" class="text-center py-10">
                        <p class="text-gray-600 dark:text-gray-400">No customers found matching your search.</p>
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
                                        Status
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="customer in filteredCustomers" :key="customer.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ customer.full_name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500 dark:text-gray-300">{{ customer.email }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-300">{{ customer.phone_number }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">{{ customer.package }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">{{ formatCurrency(customer.monthly_bill) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div v-if="customer.next_billing_date" class="text-sm text-gray-900 dark:text-white">
                                            {{ customer.next_billing_date }}
                                        </div>
                                        <div v-else class="text-sm text-gray-500 dark:text-gray-400">
                                            Not set
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="getStatusClass(customer.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                            {{ customer.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-2">
                                            <Link v-if="customer.status === 'Pending'" :href="route('billing.record-payment.show', customer.id)" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-200">
                                                Pay
                                            </Link>
                                            <Link :href="route('billing.customer-history', customer.id)" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                                                History
                                            </Link>
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
import { ref, computed } from 'vue';

const props = defineProps({
    customers: Array,
});

const search = ref('');

const filteredCustomers = computed(() => {
    if (!search.value) return props.customers;
    
    const searchTerm = search.value.toLowerCase();
    return props.customers.filter(customer => 
        customer.full_name.toLowerCase().includes(searchTerm) ||
        (customer.email && customer.email.toLowerCase().includes(searchTerm)) ||
        customer.phone_number.toLowerCase().includes(searchTerm) ||
        customer.package.toLowerCase().includes(searchTerm)
    );
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
    if (status === 'Paid') {
        return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100';
    } else {
        return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100';
    }
};
</script>
