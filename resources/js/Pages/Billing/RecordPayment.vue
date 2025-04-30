<template>
    <AppLayout title="Record Payment">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Record Payment
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex justify-between mb-6">
                        <h3 class="text-lg font-semibold">Record Payment for {{ customer.full_name }}</h3>
                        <Link :href="route('billing.customer-history', customer.id)" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                            View Billing History
                        </Link>
                    </div>

                    <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded-md mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Package</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ customer.package }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Amount Due</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ formatCurrency(customer.monthly_bill) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Billing Period</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ transaction.billing_period_start }} to {{ transaction.billing_period_end }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <form @submit.prevent="submit">
                        <input type="hidden" v-model="form.transaction_id" />
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <InputLabel for="payment_method" value="Payment Method" />
                                <select
                                    id="payment_method"
                                    v-model="form.payment_method"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    required
                                >
                                    <option value="">Select Payment Method</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                    <option value="Credit Card">Credit Card</option>
                                    <option value="Mobile Payment">Mobile Payment</option>
                                    <option value="Other">Other</option>
                                </select>
                                <InputError :message="errors.payment_method" class="mt-2" />
                            </div>
                            
                            <div>
                                <InputLabel for="payment_date" value="Payment Date" />
                                <TextInput
                                    id="payment_date"
                                    type="date"
                                    class="mt-1 block w-full"
                                    v-model="form.payment_date"
                                    required
                                />
                                <InputError :message="errors.payment_date" class="mt-2" />
                            </div>
                            
                            <div>
                                <InputLabel for="reference_number" value="Reference Number (Optional)" />
                                <TextInput
                                    id="reference_number"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.reference_number"
                                    placeholder="Transaction reference, receipt number, etc."
                                />
                                <InputError :message="errors.reference_number" class="mt-2" />
                            </div>
                            
                            <div>
                                <InputLabel for="notes" value="Notes (Optional)" />
                                <textarea
                                    id="notes"
                                    v-model="form.notes"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    placeholder="Any additional information"
                                    rows="3"
                                ></textarea>
                                <InputError :message="errors.notes" class="mt-2" />
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-end">
                            <Link :href="route('billing.pending-bills')" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 mr-4">
                                Cancel
                            </Link>
                            <PrimaryButton class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Record Payment
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    customer: Object,
    transaction: Object,
    errors: Object,
});

const form = useForm({
    transaction_id: props.transaction.id,
    payment_method: '',
    reference_number: '',
    payment_date: new Date().toISOString().substr(0, 10), // Default to today
    notes: '',
});

const submit = () => {
    form.post(route('billing.record-payment.store', props.customer.id));
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'PKR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};
</script>
