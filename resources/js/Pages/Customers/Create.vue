<script setup>
import { ref, watch } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Checkbox from '@/Components/Checkbox.vue';

const props = defineProps({
    packages: Array,
});

const form = useForm({
    full_name: '',
    email: '',
    phone_number: '',
    address: '',
    package_id: '',
    bill: '',
    billing_start_date: '',
    status: true,
});

const selectedPackage = ref(null);

// Update bill when package is selected
watch(() => form.package_id, (newValue) => {
    if (newValue) {
        const pkg = props.packages.find(p => p.id === parseInt(newValue));
        if (pkg) {
            selectedPackage.value = pkg;
            form.bill = pkg.fee;
        }
    } else {
        selectedPackage.value = null;
        form.bill = '';
    }
});

const submit = () => {
    form.post(route('customers.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <AppLayout title="Create Customer">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Create Customer
                </h2>
                <Link 
                    :href="route('customers.index')" 
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Customers
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                        <form @submit.prevent="submit">
                            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 mb-6 border border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Customer Information</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <InputLabel for="full_name" value="Full Name" required />
                                        <TextInput
                                            id="full_name"
                                            v-model="form.full_name"
                                            type="text"
                                            class="mt-1 block w-full"
                                            required
                                            autofocus
                                            placeholder="Enter customer's full name"
                                        />
                                        <InputError :message="form.errors.full_name" class="mt-2" />
                                    </div>

                                    <div>
                                        <InputLabel for="email" value="Email (Optional)" />
                                        <TextInput
                                            id="email"
                                            v-model="form.email"
                                            type="email"
                                            class="mt-1 block w-full"
                                            placeholder="customer@example.com"
                                        />
                                        <InputError :message="form.errors.email" class="mt-2" />
                                    </div>

                                    <div>
                                        <InputLabel for="phone_number" value="Phone Number" required />
                                        <TextInput
                                            id="phone_number"
                                            v-model="form.phone_number"
                                            type="text"
                                            class="mt-1 block w-full"
                                            required
                                            placeholder="Enter phone number"
                                        />
                                        <InputError :message="form.errors.phone_number" class="mt-2" />
                                    </div>

                                    <div class="md:col-span-2">
                                        <InputLabel for="address" value="Address (Optional)" />
                                        <textarea
                                            id="address"
                                            v-model="form.address"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                            rows="3"
                                            placeholder="Enter customer's address"
                                        ></textarea>
                                        <InputError :message="form.errors.address" class="mt-2" />
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 mb-6 border border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Billing Information</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <InputLabel for="package_id" value="Package" required />
                                        <select
                                            id="package_id"
                                            v-model="form.package_id"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                            required
                                        >
                                            <option value="" disabled>Select a package</option>
                                            <option v-for="pkg in packages" :key="pkg.id" :value="pkg.id">
                                                {{ pkg.title }} - {{ pkg.fee }}
                                            </option>
                                        </select>
                                        <InputError :message="form.errors.package_id" class="mt-2" />
                                    </div>

                                    <div>
                                        <InputLabel for="bill" value="Bill Amount (PKR)" required />
                                        <TextInput
                                            id="bill"
                                            v-model="form.bill"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="mt-1 block w-full"
                                            required
                                        />
                                        <p v-if="selectedPackage" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            Auto-populated from selected package ({{ selectedPackage.title }}), but you can adjust if needed.
                                        </p>
                                        <InputError :message="form.errors.bill" class="mt-2" />
                                    </div>

                                    <div>
                                        <InputLabel for="billing_start_date" value="Billing Start Date" required />
                                        <TextInput
                                            id="billing_start_date"
                                            v-model="form.billing_start_date"
                                            type="date"
                                            class="mt-1 block w-full"
                                        />
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            The date when billing started for this customer.
                                        </p>
                                        <InputError :message="form.errors.billing_start_date" class="mt-2" />
                                    </div>

                                    <div class="flex items-center col-span-1 md:col-span-2">
                                        <div>
                                            <div class="flex items-center">
                                                <Checkbox id="status" v-model:checked="form.status" />
                                                <InputLabel for="status" value="Active" class="ml-2" />
                                            </div>
                                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                Mark as active if the customer is currently subscribed.
                                            </p>
                                            <InputError :message="form.errors.status" class="mt-2" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end">
                                <PrimaryButton 
                                    :class="{ 'opacity-25': form.processing }" 
                                    :disabled="form.processing"
                                    class="bg-indigo-600 hover:bg-indigo-700"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    Create Customer
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
