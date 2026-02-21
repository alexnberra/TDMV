<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { applicationApi, documentApi } from '@/lib/api';
import { getActiveApplicationId } from '@/composables/useActiveApplication';
import PortalLayout from '@/layouts/PortalLayout.vue';
import DocumentUploadTile from '@/components/portal/DocumentUploadTile.vue';
import AppButton from '@/components/ui/AppButton.vue';

defineOptions({ layout: PortalLayout });

interface DocumentRecord {
    id: number;
    document_type: string;
    file_name: string;
    status: string;
    uploaded_at: string | null;
}

interface ApplicationRecord {
    id: number;
    case_number: string;
    status: string;
    documents: DocumentRecord[];
}

const documentTypes = ['insurance', 'title', 'tribal_id', 'drivers_license', 'inspection', 'proof_of_residency', 'other'];

const application = ref<ApplicationRecord | null>(null);
const selectedType = ref('insurance');
const selectedFile = ref<File | null>(null);
const loading = ref(true);
const uploading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

const applicationId = getActiveApplicationId();
const documents = computed(() => application.value?.documents ?? []);

async function loadApplication() {
    if (!applicationId) {
        errorMessage.value = 'No active application selected. Start with service selection.';
        loading.value = false;
        return;
    }

    try {
        const response = await applicationApi.one(applicationId);
        application.value = response.data.application;
    } catch (error: unknown) {
        const message = (error as { response?: { data?: { message?: string } } }).response?.data?.message;
        errorMessage.value = message ?? 'Unable to load application.';
    } finally {
        loading.value = false;
    }
}

async function uploadDocument() {
    if (!applicationId || !selectedFile.value) {
        return;
    }

    errorMessage.value = '';
    successMessage.value = '';
    uploading.value = true;

    try {
        const formData = new FormData();
        formData.append('document_type', selectedType.value);
        formData.append('file', selectedFile.value);

        await documentApi.upload(applicationId, formData);

        selectedFile.value = null;
        successMessage.value = 'Document uploaded successfully.';
        await loadApplication();
    } catch (error: unknown) {
        const message = (error as { response?: { data?: { message?: string } } }).response?.data?.message;
        errorMessage.value = message ?? 'Upload failed.';
    } finally {
        uploading.value = false;
    }
}

async function deleteDocument(documentId: number) {
    errorMessage.value = '';
    successMessage.value = '';

    try {
        await documentApi.remove(documentId);
        successMessage.value = 'Document removed.';
        await loadApplication();
    } catch (error: unknown) {
        const message = (error as { response?: { data?: { message?: string } } }).response?.data?.message;
        errorMessage.value = message ?? 'Unable to delete document.';
    }
}

onMounted(loadApplication);
</script>

<template>
    <div class="px-4 py-6 md:px-8 md:py-8">
        <div class="mx-auto max-w-5xl space-y-6">
            <header class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Step 3 of 4</p>
                    <h1 class="mt-1 text-3xl font-bold text-gray-900">Upload Documents</h1>
                    <p class="mt-2 text-sm text-gray-600">Add required files before review and payment.</p>
                </div>
                <Link href="/portal/requirements" class="focus-ring rounded-lg border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">
                    Back to Checklist
                </Link>
            </header>

            <div class="h-2 rounded-full bg-gray-200">
                <div class="h-full rounded-full bg-blue-600" :style="{ width: '75%' }" />
            </div>

            <p v-if="loading" class="surface-card p-4 text-sm text-gray-600">Loading application documents…</p>
            <p v-else-if="errorMessage && !application" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ errorMessage }}</p>

            <template v-else-if="application">
                <section class="surface-card p-5 md:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Case {{ application.case_number }}</p>
                            <p class="text-xs uppercase tracking-wide text-gray-500">Current status: {{ application.status }}</p>
                        </div>
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">{{ documents.length }} uploaded</span>
                    </div>

                    <div class="mt-5 grid gap-4 md:grid-cols-3">
                        <label class="block text-sm md:col-span-1">
                            <span class="mb-1.5 block font-semibold text-gray-700">Document type</span>
                            <select v-model="selectedType" class="focus-ring h-11 w-full rounded-lg border border-gray-300 px-3">
                                <option v-for="type in documentTypes" :key="type" :value="type">{{ type.replace('_', ' ') }}</option>
                            </select>
                        </label>

                        <label class="block text-sm md:col-span-2">
                            <span class="mb-1.5 block font-semibold text-gray-700">File upload</span>
                            <input
                                type="file"
                                accept=".jpg,.jpeg,.png,.pdf"
                                class="focus-ring h-11 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"
                                @change="(event) => selectedFile = ((event.target as HTMLInputElement).files?.[0] ?? null)"
                            />
                            <span class="mt-1.5 block text-xs text-gray-500">Accepted: PDF, JPG, PNG • Max 10MB</span>
                        </label>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <AppButton :loading="uploading" :disabled="!selectedFile" @click="uploadDocument">
                            {{ uploading ? 'Uploading...' : 'Upload Document' }}
                        </AppButton>
                    </div>
                </section>

                <p v-if="errorMessage" class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ errorMessage }}</p>
                <p v-if="successMessage" class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">{{ successMessage }}</p>

                <section class="surface-card p-5 md:p-6">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Uploaded documents</h2>
                    <p v-if="documents.length === 0" class="text-sm text-gray-600">No documents uploaded yet.</p>

                    <div v-else class="space-y-3">
                        <DocumentUploadTile v-for="document in documents" :key="document.id" :name="document.file_name" :type="document.document_type" :status="document.status">
                            <template #actions>
                                <button
                                    type="button"
                                    class="focus-ring rounded-md border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50"
                                    @click="deleteDocument(document.id)"
                                >
                                    Remove
                                </button>
                            </template>
                        </DocumentUploadTile>
                    </div>
                </section>

                <div class="flex justify-end">
                    <Link :href="`/portal/review?application_id=${application.id}`" class="focus-ring rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                        Continue to Review
                    </Link>
                </div>
            </template>
        </div>
    </div>
</template>
