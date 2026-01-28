<script setup>
import axios from '@/plugins/axios'
import { formatCurrency, formatDate } from '@/utils/formatters'
import { onMounted, ref } from 'vue'

const wallet = ref(null)
const loading = ref(false)

const headers = [
  { title: 'Tanggal', key: 'created_at' },
  { title: 'Tipe', key: 'type' },
  { title: 'Jumlah', key: 'amount' },
  { title: 'Referensi', key: 'reference' },
]

const fetchWallet = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/wallet')

    wallet.value = response.data
  } catch (error) {
    console.error('Error fetching wallet:', error)
  } finally {
    loading.value = false
  }
}

const resolveTypeVariant = type => {
  if (type === 'topup' || type === 'release' || type === 'refund' || type === 'fee') return 'success'
  if (type === 'payout' || type === 'lock') return 'error'
  
  return 'primary'
}

const formatAmount = (amount, type) => {
  const value = formatCurrency(amount)
  const prefix = (type === 'payout' || type === 'lock') ? '-' : '+'

  // Remove Rp prefix from value if it exists to formatting cleaner with +/-
  return `${prefix} ${value}`
}

onMounted(() => {
  fetchWallet()
})
</script>

<template>
  <VContainer>
    <div class="d-flex justify-space-between align-center mb-6">
      <h2 class="text-h4">
        Dompet Saya
      </h2>
    </div>

    <div
      v-if="loading"
      class="text-center my-6"
    >
      <VProgressCircular
        indeterminate
        color="primary"
      />
    </div>

    <div v-else-if="wallet">
      <VRow>
        <!-- Balance Card -->
        <VCol
          cols="12"
          md="6"
        >
          <VCard class="mb-6 bg-primary text-white">
            <VCardText>
              <div class="text-subtitle-1 mb-2">
                Saldo Tersedia
              </div>
              <h3 class="text-h3 font-weight-bold mb-4">
                {{ formatCurrency(wallet.balance) }}
              </h3>
              <VDivider class="mb-4 border-opacity-25" />
              <div class="d-flex justify-space-between">
                <span>Saldo Tertahan</span>
                <span class="font-weight-medium">{{ formatCurrency(wallet.locked_balance) }}</span>
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
        
      <!-- Transactions Table -->
      <VCard title="Riwayat Transaksi">
        <VDataTable
          :headers="headers"
          :items="wallet.transactions"
          class="text-no-wrap"
          :items-per-page="10"
        >
          <template #item.created_at="{ item }">
            {{ formatDate(item.created_at) }}
          </template>

          <template #item.type="{ item }">
            <VChip
              :color="resolveTypeVariant(item.type)"
              size="small"
              label
              class="text-capitalize"
            >
              {{ item.type }}
            </VChip>
          </template>

          <template #item.amount="{ item }">
            <span
              :class="resolveTypeVariant(item.type) === 'error' ? 'text-error' : 'text-success'"
              class="font-weight-medium"
            >
              {{ formatAmount(item.amount, item.type) }}
            </span>
          </template>
                
          <template #item.reference="{ item }">
            <span v-if="item.reference_id">ID: {{ item.reference_id }}</span>
            <span v-else>-</span>
          </template>

          <template #no-data>
            <div class="text-center pa-4">
              <p class="text-medium-emphasis">
                Belum ada transaksi.
              </p>
            </div>
          </template>
        </VDataTable>
      </VCard>
    </div>
  </VContainer>
</template>
