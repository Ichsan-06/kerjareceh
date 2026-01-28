<script setup>
import axios from '@/plugins/axios'
import { formatCurrency, formatDate } from '@/utils/formatters'
import { onMounted, ref } from 'vue'

const requests = ref([])
const loading = ref(false)
const processing = ref(false)

const approveDialog = ref(false)
const rejectDialog = ref(false)
const selectedRequest = ref(null)

const proofFile = ref(null)
const rejectionReason = ref('')

const fetchRequests = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/admin/withdraw')

    requests.value = response.data
  } catch (error) {
    console.error('Error fetching requests:', error)
  } finally {
    loading.value = false
  }
}

const openApprove = req => {
  selectedRequest.value = req
  proofFile.value = null
  approveDialog.value = true
}

const openReject = req => {
  selectedRequest.value = req
  rejectionReason.value = ''
  rejectDialog.value = true
}

const submitApprove = async () => {
  if (!proofFile.value) {
    alert('Mohon upload bukti transfer')
    
    return
  }
    
  processing.value = true

  const formData = new FormData()

  formData.append('proof', proofFile.value)

  try {
    await axios.post(`/api/admin/withdraw/${selectedRequest.value.id}/approve`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    approveDialog.value = false
    fetchRequests()
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to approve')
  } finally {
    processing.value = false
  }
}

const submitReject = async () => {
  if (!rejectionReason.value) {
    alert('Mohon isi alasan penolakan')
    
    return
  }

  processing.value = true
  try {
    await axios.post(`/api/admin/withdraw/${selectedRequest.value.id}/reject`, {
      reason: rejectionReason.value,
    })
    rejectDialog.value = false
    fetchRequests()
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to reject')
  } finally {
    processing.value = false
  }
}

const resolveStatusColor = status => {
  if (status === 'approved') return 'success'
  if (status === 'rejected') return 'error'
  
  return 'warning'
}

onMounted(() => {
  fetchRequests()
})
</script>

<template>
  <VContainer>
    <h2 class="text-h4 mb-6">
      Verifikasi Penarikan Saldo
    </h2>

    <VCard>
      <VTable>
        <thead>
          <tr>
            <th>User</th>
            <th>Jumlah</th>
            <th>Bank Info</th>
            <th>Tanggal Request</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="req in requests"
            :key="req.id"
          >
            <td>
              <div class="font-weight-bold">
                {{ req.user.name }}
              </div>
              <div class="text-caption text-medium-emphasis">
                {{ req.user.email }}
              </div>
            </td>
            <td class="font-weight-bold">
              {{ formatCurrency(req.amount) }}
            </td>
            <td>
              <div>{{ req.bank_name }}</div>
              <div>{{ req.account_number }}</div>
              <small>{{ req.account_holder_name }}</small>
            </td>
            <td>{{ formatDate(req.created_at) }}</td>
            <td>
              <VChip
                :color="resolveStatusColor(req.status)"
                size="small"
                label
                class="text-capitalize"
              >
                {{ req.status }}
              </VChip>
            </td>
            <td>
              <div
                v-if="req.status === 'pending'"
                class="d-flex gap-2"
              >
                <VBtn
                  size="small"
                  color="success"
                  icon="bx-check"
                  variant="text"
                  title="Approve"
                  @click="openApprove(req)"
                />
                <VBtn
                  size="small"
                  color="error"
                  icon="bx-x"
                  variant="text"
                  title="Reject"
                  @click="openReject(req)"
                />
              </div>
              <div
                v-else
                class="text-caption text-medium-emphasis"
              >
                {{ req.approved_at ? formatDate(req.approved_at) : '-' }}
              </div>
            </td>
          </tr>
          <tr v-if="requests.length === 0">
            <td
              colspan="6"
              class="text-center py-4 text-medium-emphasis"
            >
              Tidak ada permintaan penarikan.
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <!-- Approve Dialog -->
    <VDialog
      v-model="approveDialog"
      max-width="500"
    >
      <VCard title="Setujui Penarikan">
        <VCardText>
          <p class="mb-4">
            Pastikan anda telah mentransfer dana sebesar <strong>{{ selectedRequest ? formatCurrency(selectedRequest.amount) : 0 }}</strong> ke:
            <br>
            {{ selectedRequest?.bank_name }} - {{ selectedRequest?.account_number }} a.n {{ selectedRequest?.account_holder_name }}
          </p>
          <VFileInput 
            v-model="proofFile" 
            label="Upload Bukti Transfer" 
            accept="image/*" 
            prepend-icon="bx-camera" 
            show-size
          />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn
            color="secondary"
            variant="text"
            @click="approveDialog = false"
          >
            Batal
          </VBtn>
          <VBtn
            color="success"
            :loading="processing"
            @click="submitApprove"
          >
            Konfirmasi & Upload
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Reject Dialog -->
    <VDialog
      v-model="rejectDialog"
      max-width="500"
    >
      <VCard title="Tolak Penarikan">
        <VCardText>
          <p>Dana akan dikembalikan ke saldo wallet user.</p>
          <VTextarea 
            v-model="rejectionReason"
            label="Alasan Penolakan"
            rows="3"
            class="mt-2"
          />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn
            color="secondary"
            variant="text"
            @click="rejectDialog = false"
          >
            Batal
          </VBtn>
          <VBtn
            color="error"
            :loading="processing"
            @click="submitReject"
          >
            Tolak Permintaan
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </VContainer>
</template>
