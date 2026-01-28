<script setup>
import axios from '@/plugins/axios'
import { ref, onMounted } from 'vue'

const form = ref({
  nominal: 5000,
  message: '',
})

const loading = ref(false)
const result = ref(null)
const error = ref(null)
const donations = ref([])
const loadingCheck = ref(null)

const checkStatus = async refId => {
  loadingCheck.value = refId
  try {
    const { data } = await axios.get(`/api/donations/${refId}/check`)
    if (data.status) {
      const index = donations.value.findIndex(d => d.ref_id === refId)
      if (index !== -1) {
        donations.value[index].status = data.status
      }
    }
  } catch (e) {
    console.error("Check status failed", e)
  } finally {
    loadingCheck.value = null
  }
}

const presets = [5000, 10000, 25000, 50000, 100000]

const fetchDonations = async () => {
  try {
    const { data } = await axios.get('/api/donations')

    donations.value = data
  } catch (e) {
    console.error("Failed to fetch donations", e)
  }
}

const submitDonation = async () => {
  loading.value = true
  error.value = null
  result.value = null

  try {
    const response = await axios.post('/api/donations', form.value)

    result.value = response.data
    fetchDonations() // Refresh list
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Gagal membuat donasi. Silakan coba lagi.'
  } finally {
    loading.value = false
  }
}


onMounted(() => {
  fetchDonations()
})
</script>

<template>
  <VRow justify="center">
    <VCol
      cols="12"
      md="8"
      lg="6"
    >
      <VCard class="pa-4">
        <VCardItem>
          <template #prepend>
            <VIcon
              icon="bx-coffee-togo"
              size="32"
              color="primary"
              class="me-2"
            />
          </template>
          <VCardTitle class="text-h5 font-weight-bold">
            Traktir Admin ☕
          </VCardTitle>
          <VCardSubtitle>
            Dukung kami agar terus semangat mengembangkan platform ini!
          </VCardSubtitle>
        </VCardItem>

        <VCardText
          v-if="!result"
          class="mt-4"
        >
          <VForm @submit.prevent="submitDonation">
            <label class="text-caption text-medium-emphasis mb-2 d-block text-start">Pilih Nominal</label>
            <div class="d-flex flex-wrap gap-2 mb-4">
              <VChip
                v-for="amount in presets"
                :key="amount"
                :color="form.nominal === amount ? 'primary' : 'secondary'"
                variant="tonal"
                @click="form.nominal = amount"
              >
                {{ amount.toLocaleString('id-ID') }}
              </VChip>
            </div>

            <VTextField
              v-model="form.nominal"
              label="Nominal"
              type="number"
              class="mb-4"
              prefix="Rp"
            />

            <VTextarea
              v-model="form.message"
              label="Pesan (Opsional)"
              placeholder="Semangat min! Coding terus pantang mundur..."
              rows="3"
              class="mb-4"
            />

            <VBtn
              block
              type="submit"
              :loading="loading"
              color="primary"
            >
              Traktir Sekarang
            </VBtn>
          </VForm>
        </VCardText>

        <!-- Result State: QRIS Display -->
        <VCardText
          v-else
          class="text-center mt-4"
        >
          <VIcon
            icon="bx-check-circle"
            color="success"
            size="64"
            class="mb-4"
          />
          <h3 class="text-h5 font-weight-bold mb-2">
            Terima Kasih!
          </h3>
          <p class="text-body-2 mb-6">
            Silakan scan QRIS di bawah ini untuk menyelesaikan traktiranmu.
          </p>

          <div
            class="d-flex justify-center mb-6 pa-4 bg-grey-50 rounded border mx-auto"
            style="max-width: 300px;"
          >
            <!-- If the content is an image URL -->
            <VImg
              v-if="result.payment_response.data.qr_image && result.payment_response.data.qr_image.startsWith('https')"
              :src="result.payment_response.data.qr_image"
              width="250"
              height="250"
            />
            <div
              v-else
              class="text-break w-100"
            >
              <p class="mb-2">
                Scan QRIS pada aplikasi pembayaran Anda:
              </p>
              <!-- If it's pure string/payload, maybe we need a QR code generator, but user asked specifically for image output if available -->
              <div class="bg-grey-200 pa-2 rounded text-caption font-mono">
                {{ result.payment_response.data.qr_string }}
              </div>
            </div>
          </div>

          <VBtn
            variant="tonal"
            block
            class="mb-2"
            @click="result = null"
          >
            Kirim Lagi
          </VBtn>
        </VCardText>
        
        <VCardText
          v-if="error"
          class="text-center text-error"
        >
          {{ error }}
        </VCardText>
      </VCard>
    </VCol>

    <VCol
      cols="12"
      md="8"
      lg="6"
    >
      <VCard title="Riwayat Donasi Terakhir">
        <VTable>
          <thead>
            <tr>
              <th class="text-uppercase">
                Waktu
              </th>
              <th class="text-uppercase">
                Nominal
              </th>
              <th class="text-uppercase">
                Pesan
              </th>
              <th class="text-uppercase">
                Status
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="item in donations"
              :key="item.id"
            >
              <td>
                {{ new Date(item.created_at).toLocaleString('id-ID') }}
              </td>
              <td>
                Rp {{ item.nominal.toLocaleString('id-ID') }}
              </td>
              <td>
                {{ item.message || '-' }}
              </td>
              <td>
                <div class="d-flex align-center gap-2">
                  <VChip
                    :color="item.status === 'paid' ? 'success' : item.status === 'pending' ? 'warning' : 'error'"
                    size="small"
                    label
                  >
                    {{ item.status }}
                  </VChip>
                  <VBtn
                    v-if="item.status === 'pending'"
                    size="x-small"
                    color="info"
                    variant="text"
                    icon="bx-refresh"
                    :loading="loadingCheck === item.ref_id"
                    @click="checkStatus(item.ref_id)"
                  />
                </div>
              </td>
            </tr>
            <tr v-if="donations.length === 0">
              <td
                colspan="4"
                class="text-center text-medium-emphasis"
              >
                Belum ada donasi.
              </td>
            </tr>
          </tbody>
        </VTable>
      </VCard>
    </VCol>
  </VRow>
</template>

<style lang="scss">
// Removed auth page styles
</style>
