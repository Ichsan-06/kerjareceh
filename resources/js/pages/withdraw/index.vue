<script setup>
import axios from '@/plugins/axios';
import { formatCurrency, formatDate } from '@/utils/formatters';
import { onMounted, ref } from 'vue';

const withdrawHistory = ref([]);
const loading = ref(false);
const submitting = ref(false);
const showDialog = ref(false);

const formData = ref({
    amount: '',
    bank_name: '',
    account_number: '',
    account_holder_name: '',
});

const formErrors = ref({});

const fetchHistory = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/withdraw');
        withdrawHistory.value = response.data;
    } catch (error) {
        console.error('Error fetching history:', error);
    } finally {
        loading.value = false;
    }
};

const submitWithdraw = async () => {
    submitting.value = true;
    formErrors.value = {};
    try {
         await axios.post('/api/withdraw', {
             ...formData.value,
             amount: parseInt(formData.value.amount) // Ensure numeric
         });
         showDialog.value = false;
         // Reset form
         formData.value = { amount: '', bank_name: '', account_number: '', account_holder_name: '' };
         fetchHistory();
    } catch (error) {
        if (error.response?.data?.errors) {
            formErrors.value = error.response.data.errors;
        } else {
             alert(error.response?.data?.message || 'Failed to request withdraw');
        }
    } finally {
        submitting.value = false;
    }
};

const resolveStatusColor = (status) => {
    if (status === 'approved') return 'success';
    if (status === 'rejected') return 'error';
    return 'warning';
};

onMounted(() => {
    fetchHistory();
});
</script>

<template>
    <VContainer>
        <div class="d-flex justify-space-between align-center mb-6">
            <h2 class="text-h4">Pencairan Saldo</h2>
            <VBtn color="primary" prepend-icon="bx-money" @click="showDialog = true">
                Tarik Saldo
            </VBtn>
        </div>

        <VCard title="Riwayat Penarikan">
            <VTable>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jumlah</th>
                        <th>Bank / E-Wallet</th>
                        <th>Info Rekening</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in withdrawHistory" :key="item.id">
                        <td>{{ formatDate(item.created_at) }}</td>
                        <td class="font-weight-bold">{{ formatCurrency(item.amount) }}</td>
                        <td>{{ item.bank_name }}</td>
                        <td>
                            <div>{{ item.account_number }}</div>
                            <small class="text-medium-emphasis">{{ item.account_holder_name }}</small>
                        </td>
                        <td>
                            <VChip :color="resolveStatusColor(item.status)" size="small" label class="text-capitalize">
                                {{ item.status }}
                            </VChip>
                        </td>
                        <td>
                            <div v-if="item.status === 'rejected'" class="text-error text-caption">
                                Alasan: {{ item.rejection_reason }}
                            </div>
                            <div v-else-if="item.status === 'approved' && item.proof_path">
                                <a :href="`/storage/${item.proof_path}`" target="_blank" class="text-decoration-underline text-caption">Lihat Bukti</a>
                            </div>
                            <span v-else>-</span>
                        </td>
                    </tr>
                    <tr v-if="withdrawHistory.length === 0">
                        <td colspan="6" class="text-center py-4 text-medium-emphasis">Belum ada riwayat penarikan.</td>
                    </tr>
                </tbody>
            </VTable>
        </VCard>

        <!-- Request Dialog -->
        <VDialog v-model="showDialog" max-width="500">
            <VCard title="Form Penarikan Saldo">
                <VCardText>
                    <VForm @submit.prevent="submitWithdraw">
                        <VTextField
                            v-model="formData.amount"
                            label="Jumlah Penarikan (Rp)"
                            type="number"
                            :error-messages="formErrors.amount"
                            placeholder="Min. 10.000"
                            class="mb-3"
                        />
                         <VTextField
                            v-model="formData.bank_name"
                            label="Bank / E-Wallet"
                            :error-messages="formErrors.bank_name"
                            placeholder="Contoh: BCA, GoPay, OVO"
                             class="mb-3"
                        />
                        <VTextField
                            v-model="formData.account_number"
                            label="Nomor Rekening / HP"
                            :error-messages="formErrors.account_number"
                            type="number"
                             class="mb-3"
                        />
                        <VTextField
                            v-model="formData.account_holder_name"
                            label="Nama Pemilik Rekening"
                             :error-messages="formErrors.account_holder_name"
                             class="mb-3"
                        />
                    </VForm>
                </VCardText>
                <VCardActions>
                    <VSpacer />
                    <VBtn color="secondary" variant="text" @click="showDialog = false">Batal</VBtn>
                    <VBtn color="primary" :loading="submitting" @click="submitWithdraw">Kirim Permintaan</VBtn>
                </VCardActions>
            </VCard>
        </VDialog>
    </VContainer>
</template>
