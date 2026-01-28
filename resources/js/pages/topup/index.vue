<script setup>
import axios from '@/plugins/axios'
import { formatCurrency } from '@/utils/formatters'
import { onMounted, ref } from 'vue'

const activeTab = ref(0)

const form = ref({
  amount: 20000,
  payment_method: 'bank_transfer',
  proof: null,
})

const history = ref([])
const loading = ref(false)
const submitting = ref(false)
const errors = ref({})

const presetAmounts = [20000, 50000, 100000, 200000, 500000]

const onFileChange = e => {
  const file = e.target.files[0]

  form.value.proof = file
}

const fetchHistory = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/topup')

    history.value = response.data
  } catch (error) {
    console.error('Error fetching history:', error)
  } finally {
    loading.value = false
  }
}

const submitTopUp = async () => {
  submitting.value = true
  errors.value = {}

  const formData = new FormData()

  formData.append('amount', form.value.amount)
  formData.append('payment_method', form.value.payment_method)
  if (form.value.proof) {
    formData.append('proof', form.value.proof)
  }

  try {
    await axios.post('/api/topup', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })

    // Reset and switch to history
    form.value = { amount: 20000, payment_method: 'bank_transfer', proof: null }
    activeTab.value = 1
    fetchHistory()
  } catch (error) {
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors
    } else {
      console.error('Error submitting top up:', error)
    }
  } finally {
    submitting.value = false
  }
}

const resolveStatusColor = status => {
  if (status === 'approved') return 'success'
  if (status === 'rejected') return 'error'
  
  return 'warning'
}

onMounted(() => {
  fetchHistory()
})
</script>

<template>
  <VContainer>
    <div class="d-flex align-center gap-4 mb-6">
      <VIcon
        icon="bx-wallet"
        size="32"
        color="primary"
      />
      <h2 class="text-h4">
        Top Up Saldo
      </h2>
    </div>

    <VCard>
      <VTabs v-model="activeTab">
        <VTab>Isi Saldo</VTab>
        <VTab>Riwayat</VTab>
      </VTabs>

      <VCardText>
        <VWindow v-model="activeTab">
          <!-- Top Up Form -->
          <VWindowItem>
            <VForm @submit.prevent="submitTopUp">
              <VRow>
                <VCol
                  cols="12"
                  md="6"
                >
                  <VLabel>Pilih Nominal</VLabel>
                  <div class="d-flex flex-wrap gap-2 my-2">
                    <VChip
                      v-for="amount in presetAmounts"
                      :key="amount"
                      :color="form.amount === amount ? 'primary' : 'default'"
                      class="cursor-pointer"
                      @click="form.amount = amount"
                    >
                      {{ formatCurrency(amount) }}
                    </VChip>
                  </div>
                  <VTextField
                    v-model="form.amount"
                    label="Nominal Lainnya"
                    type="number"
                    prefix="Rp"
                    :error-messages="errors.amount"
                    class="mt-4"
                  />
                </VCol>

                <VCol
                  cols="12"
                  md="6"
                >
                  <VSelect
                    v-model="form.payment_method"
                    label="Metode Pembayaran"
                    :items="[
                      { title: 'Transfer Bank (BCA)', value: 'bank_transfer' },
                      { title: 'E-Wallet (GoPay/OVO)', value: 'e_wallet' }
                    ]"
                    :error-messages="errors.payment_method"
                  />
                  
                  <VFileInput
                    label="Upload Bukti Transfer"
                    accept="image/*,application/pdf"
                    :error-messages="errors.proof"
                    class="mt-4"
                    prepend-icon="bx-upload"
                    @change="onFileChange"
                  />
                </VCol>

                <VCol cols="12">
                  <VAlert
                    type="info"
                    variant="tonal"
                    class="mb-4"
                  >
                    Silakan transfer ke:<br>
                    <b>BCA 6995110192 (a.n M Ichsan)</b><br>
                    Total Bayar: <b>{{ formatCurrency(form.amount) }}</b>
                  </VAlert>

                  <VAlert
                    type="danger"
                    variant="tonal"
                    class="mb-4"
                  >
                    Silakan transfer ke:<br>
                    <b>GOPAY 085173476478 (a.n M Ichsan)</b><br>
                    Total Bayar: <b>{{ formatCurrency(form.amount) }}</b>
                  </VAlert>
                  <VBtn
                    type="submit"
                    block
                    color="primary"
                    :loading="submitting"
                  >
                    Konfirmasi Top Up
                  </VBtn>
                </VCol>
              </VRow>
            </VForm>
          </VWindowItem>

          <!-- History Table -->
          <VWindowItem>
            <VDataTable
              :headers="[
                { title: 'Tanggal', key: 'created_at' },
                { title: 'Metode', key: 'payment_method' },
                { title: 'Nominal', key: 'amount' },
                { title: 'Status', key: 'status' },
              ]"
              :items="history"
              :loading="loading"
            >
              <template #item.created_at="{ item }">
                {{ new Date(item.created_at).toLocaleString() }}
              </template>
              
              <template #item.amount="{ item }">
                {{ formatCurrency(item.amount) }}
              </template>

              <template #item.payment_method="{ item }">
                <span class="text-capitalize">{{ item.payment_method.replace('_', ' ') }}</span>
              </template>

              <template #item.status="{ item }">
                <VChip
                  :color="resolveStatusColor(item.status)"
                  size="small"
                  label
                  class="text-capitalize"
                >
                  {{ item.status }}
                </VChip>
              </template>
            </VDataTable>
          </VWindowItem>
        </VWindow>
      </VCardText>
    </VCard>
  </VContainer>
</template>
