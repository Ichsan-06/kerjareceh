<script setup>
import axios from '@/plugins/axios';
import { formatCurrency } from '@/utils/formatters';
import { can } from '@/utils/permissions';
import { onMounted, ref } from 'vue';

const requests = ref([]);
const loading = ref(false);
const processing = ref(false);
const dialog = ref(false);
const rejectDialog = ref(false);
const selectedRequest = ref(null);
const rejectionReason = ref('');

// Config for image preview
const baseUrl = import.meta.env.VITE_API_BASE_URL || ''; 
// Assuming Laravel storage link is set up accessing public/storage. 
// Proof path stored as 'proofs/filename'. URL should be /storage/proofs/filename if symlinked.
// Or if simply served via PHP artisan serve, directly via storage path logic.
// Adjusting path logic to match typical Laravel Setup:
const getProofUrl = (path) => {
    if (!path) return '';
    // If using 'php artisan storage:link', accessible at /storage/...
    return `/storage/${path}`;
};

const headers = [
  { title: 'User', key: 'user.name' },
  { title: 'Amount', key: 'amount' },
  { title: 'Method', key: 'payment_method' },
  { title: 'Status', key: 'status' },
  { title: 'Date', key: 'created_at' },
  { title: 'Actions', key: 'actions', sortable: false },
];

const fetchRequests = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/admin/topup?status=pending');
    requests.value = response.data;
  } catch (error) {
    console.error('Error fetching requests:', error);
  } finally {
    loading.value = false;
  }
};

const openVerifyDialog = (item) => {
    selectedRequest.value = item;
    dialog.value = true;
};

const approveParams = async () => {
    if(!confirm('Approve this Top Up? User balance will be updated.')) return;
    
    processing.value = true;
    try {
        await axios.post(`/api/admin/topup/${selectedRequest.value.id}/approve`);
        dialog.value = false;
        fetchRequests();
    } catch (error) {
        console.error('Error approving:', error);
        alert('Failed to approve');
    } finally {
        processing.value = false;
    }
};

const openRejectDialog = () => {
    rejectDialog.value = true;
};

const submitReject = async () => {
    if(!rejectionReason.value) return;

    processing.value = true;
    try {
        await axios.post(`/api/admin/topup/${selectedRequest.value.id}/reject`, {
            reason: rejectionReason.value
        });
        rejectDialog.value = false;
        dialog.value = false; // Close main dialog too
        rejectionReason.value = '';
        fetchRequests();
    } catch (error) {
        console.error('Error rejecting:', error);
    } finally {
        processing.value = false;
    }
};

onMounted(() => {
    if (can('read admin_topup')) {
        fetchRequests();
    }
});
</script>

<template>
  <VContainer v-if="can('read admin_topup')">
    <h2 class="text-h4 mb-6">Verifikasi Top Up</h2>

    <VCard>
        <VDataTable
            :headers="headers"
            :items="requests"
            :loading="loading"
        >
            <template #item.amount="{ item }">
                {{ formatCurrency(item.amount) }}
            </template>
            
            <template #item.created_at="{ item }">
                {{ new Date(item.created_at).toLocaleString() }}
            </template>

            <template #item.status="{ item }">
                <VChip color="warning" size="small" label class="text-capitalize">{{ item.status }}</VChip>
            </template>

            <template #item.actions="{ item }">
                <VBtn
                    v-if="can('approve topup')"
                    size="small"
                    color="primary"
                    @click="openVerifyDialog(item)"
                >
                    Verify
                </VBtn>
            </template>
        </VDataTable>
    </VCard>

    <!-- Verification Dialog -->
    <VDialog v-model="dialog" max-width="600">
        <VCard v-if="selectedRequest">
            <VCardTitle class="d-flex justify-space-between align-center">
                <span>Verifikasi Transaksi #{{ selectedRequest.id }}</span>
                <VBtn icon="bx-x" variant="text" @click="dialog = false" />
            </VCardTitle>
            
            <VCardText>
                <VRow>
                    <VCol cols="6">User</VCol>
                    <VCol cols="6" class="font-weight-bold">{{ selectedRequest.user.name }}</VCol>
                    
                    <VCol cols="6">Nominal</VCol>
                    <VCol cols="6" class="font-weight-bold text-success">{{ formatCurrency(selectedRequest.amount) }}</VCol>

                    <VCol cols="12">
                        <div class="text-caption mb-1">Bukti Pembayaran</div>
                        <div class="border rounded pa-2 d-flex justify-center bg-grey-100">
                            <!-- Basic img handling, might need lightbox or new tab for PDF -->
                            <a :href="getProofUrl(selectedRequest.proof_path)" target="_blank">
                                View Proof (New Tab)
                            </a>
                        </div>
                    </VCol>
                </VRow>
            </VCardText>

            <VCardActions class="justify-end gap-2 pa-4">
                <VBtn color="error" variant="outlined" @click="openRejectDialog">
                    Reject
                </VBtn>
                <VBtn color="success" variant="elevated" :loading="processing" @click="approveParams">
                    Approve
                </VBtn>
            </VCardActions>
        </VCard>
    </VDialog>

    <!-- Rejection Dialog -->
    <VDialog v-model="rejectDialog" max-width="400">
        <VCard title="Alasan Penolakan">
            <VCardText>
                <VTextarea
                    v-model="rejectionReason"
                    label="Alasan"
                    rows="3"
                    auto-grow
                />
            </VCardText>
            <VCardActions>
                <VSpacer />
                <VBtn variant="text" @click="rejectDialog = false">Batal</VBtn>
                <VBtn color="error" :loading="processing" @click="submitReject">Tolak</VBtn>
            </VCardActions>
        </VCard>
    </VDialog>
  </VContainer>
</template>
