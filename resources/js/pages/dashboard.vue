<script setup>
import axios from '@/plugins/axios'
import { formatCurrency, formatDate } from '@/utils/formatters'
import CardStatisticsVertical from '@core/components/cards/CardStatisticsVertical.vue'
import { onMounted, ref } from 'vue'

// 👉 Images
import chart from '@images/cards/chart-success.png'
import card from '@images/cards/credit-card-primary.png'
import paypal from '@images/cards/paypal-error.png'
import walletImage from '@images/cards/wallet-info.png'

const dashboardData = ref(null)
const loading = ref(false)

const fetchDashboardData = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/dashboard')

    dashboardData.value = response.data
  } catch (error) {
    console.error('Error fetching dashboard data:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchDashboardData()
})
</script>

<template>
  <VRow v-if="loading">
    <VCol
      cols="12"
      class="text-center"
    >
      <VProgressCircular
        indeterminate
        color="primary"
      />
    </VCol>
  </VRow>
  
  <VRow v-else-if="dashboardData">
    <!-- 👉 Congratulations -->
    <VCol
      cols="12"
      md="8"
    >
      <AnalyticsCongratulations />
    </VCol>

    <VCol
      cols="12"
      sm="4"
    >
      <VRow>
        <!-- 👉 Balance -->
        <VCol
          cols="12"
          md="6"
        >
          <CardStatisticsVertical
            v-bind="{
              title: 'Saldo Saat Ini',
              image: walletImage,
              stats: formatCurrency(dashboardData.stats.balance),
              change: 0, 
            }"
          />
        </VCol>

        <!-- 👉 Earnings -->
        <VCol
          cols="12"
          md="6"
        >
          <CardStatisticsVertical
            v-bind="{
              title: 'Total Penghasilan',
              image: chart,
              stats: formatCurrency(dashboardData.stats.total_earned),
              change: 0,
            }"
          />
        </VCol>
      </VRow>
    </VCol>

    <!-- 👉 Jobs Stats -->
    <VCol
      cols="12"
      md="8"
    >
      <VRow>
        <VCol
          cols="12"
          md="4"
        >
          <CardStatisticsVertical
            v-bind="{
              title: 'Pekerjaan Selesai',
              image: card,
              stats: dashboardData.stats.jobs_completed_count.toString(),
              change: 0,
            }"
          />
        </VCol>
        <VCol
          cols="12"
          md="4"
        >
          <CardStatisticsVertical
            v-bind="{
              title: 'Pekerjaan Diambil',
              image: paypal,
              stats: dashboardData.stats.jobs_taken_count.toString(),
              change: 0,
            }"
          />
        </VCol>
        <VCol
          cols="12"
          md="4"
        >
          <CardStatisticsVertical
            v-bind="{
              title: 'Pekerjaan Aktif Diposting',
              image: walletImage,
              stats: dashboardData.stats.jobs_posted_count.toString(),
              change: 0,
            }"
          />
        </VCol>
      </VRow>
    </VCol>

    <VCol
      cols="12"
      md="4"
    >
      <!-- Recent Activity / Transactions Brief -->
      <VCard title="Aktivitas Terbaru">
        <VList density="compact">
          <VListItem
            v-for="transaction in dashboardData.recent_activity"
            :key="transaction.id"
          >
            <template #prepend>
              <VIcon 
                :icon="transaction.type === 'fee' || transaction.type === 'topup' ? 'bx-up-arrow-circle' : 'bx-down-arrow-circle'" 
                :color="transaction.type === 'fee' || transaction.type === 'topup' ? 'success' : 'error'"
              />
            </template>
            <VListItemTitle class="font-weight-medium text-capitalize">
              {{ transaction.type }}
            </VListItemTitle>
            <VListItemSubtitle>
              {{ formatDate(transaction.created_at) }}
            </VListItemSubtitle>
            <template #append>
              <span :class="transaction.type === 'fee' || transaction.type === 'topup' ? 'text-success' : 'text-error'">
                {{ transaction.type === 'fee' || transaction.type === 'topup' ? '+' : '-' }}
                {{ formatCurrency(transaction.amount) }}
              </span>
            </template>
          </VListItem>
          <VListItem v-if="dashboardData.recent_activity.length === 0">
            <VListItemTitle class="text-medium-emphasis text-center">
              Belum ada aktivitas.
            </VListItemTitle>
          </VListItem>
        </VList>
        <VCardActions>
          <VBtn
            block
            variant="tonal"
            to="/wallet"
          >
            Lihat Dompet
          </VBtn>
        </VCardActions>
      </VCard>
    </VCol>

    <!-- 👉 Worker Guide -->
    <VCol
      cols="12"
      md="6"
    >
      <VCard title="🧠 CARA MENGAMBIL PEKERJAAN">
        <VCardText>
          <VTimeline
            side="end"
            align="start"
            truncate-line="both"
            density="compact"
            class="v-timeline-density-compact"
          >
            <VTimelineItem
              dot-color="primary"
              size="x-small"
            >
              <div class="d-flex justify-space-between align-center flex-wrap">
                <h6 class="text-base font-weight-semibold me-3">
                  Registrasi Akun
                </h6>
              </div>
              <p class="mb-0">
                Daftarkan akun dan lengkapi data yang dibutuhkan.
              </p>
            </VTimelineItem>

            <VTimelineItem
              dot-color="success"
              size="x-small"
            >
              <div class="d-flex justify-space-between align-center flex-wrap">
                <h6 class="text-base font-weight-semibold me-3">
                  Pilih Pekerjaan
                </h6>
              </div>
              <p class="mb-0">
                Pilih pekerjaan yang masih memiliki slot tersedia.
              </p>
            </VTimelineItem>

            <VTimelineItem
              dot-color="info"
              size="x-small"
            >
              <div class="d-flex justify-space-between align-center flex-wrap">
                <h6 class="text-base font-weight-semibold me-3">
                  Ambil Slot Pekerjaan
                </h6>
              </div>
              <p class="mb-0">
                Setelah mengambil pekerjaan, slot akan dikunci untuk Anda dalam waktu tertentu.
              </p>
            </VTimelineItem>

            <VTimelineItem
              dot-color="warning"
              size="x-small"
            >
              <div class="d-flex justify-space-between align-center flex-wrap">
                <h6 class="text-base font-weight-semibold me-3">
                  Laksanakan Tugas
                </h6>
              </div>
              <p class="mb-0">
                Kerjakan tugas sesuai instruksi yang diberikan.
              </p>
            </VTimelineItem>

            <VTimelineItem
              dot-color="error"
              size="x-small"
            >
              <div class="d-flex justify-space-between align-center flex-wrap">
                <h6 class="text-base font-weight-semibold me-3">
                  Submit Bukti Pekerjaan
                </h6>
              </div>
              <p class="mb-0">
                Unggah bukti pengerjaan sesuai ketentuan.
              </p>
            </VTimelineItem>

            <VTimelineItem
              dot-color="secondary"
              size="x-small"
            >
              <div class="d-flex justify-space-between align-center flex-wrap">
                <h6 class="text-base font-weight-semibold me-3">
                  Proses Verifikasi
                </h6>
              </div>
              <p class="mb-0">
                Pekerjaan akan divalidasi oleh pemberi kerja atau admin.
              </p>
            </VTimelineItem>

            <VTimelineItem
              dot-color="primary"
              size="x-small"
            >
              <div class="d-flex justify-space-between align-center flex-wrap">
                <h6 class="text-base font-weight-semibold me-3">
                  Penerimaan Komisi
                </h6>
              </div>
              <p class="mb-0">
                Jika disetujui, komisi akan otomatis masuk ke saldo akun Anda.
              </p>
            </VTimelineItem>
          </VTimeline>
        </VCardText>
      </VCard>
    </VCol>

    <!-- 👉 Employer Guide -->
    <VCol
      cols="12"
      md="6"
    >
      <VCard title="🧠 CARA KERJA UNTUK PEMBERI KERJA">
        <VCardText>
          <VTimeline
            side="end"
            align="start"
            truncate-line="both"
            density="compact"
            class="v-timeline-density-compact"
          >
            <VTimelineItem
              dot-color="primary"
              size="x-small"
            >
              <div class="d-flex justify-space-between align-center flex-wrap">
                <h6 class="text-base font-weight-semibold me-3">
                  Registrasi Akun Pemberi Kerja
                </h6>
              </div>
              <p class="mb-0">
                Daftarkan akun dan lengkapi data yang diperlukan.
              </p>
            </VTimelineItem>

            <VTimelineItem
              dot-color="success"
              size="x-small"
            >
              <div class="d-flex justify-space-between align-center flex-wrap">
                <h6 class="text-base font-weight-semibold me-3">
                  Melakukan Top Up Saldo
                </h6>
              </div>
              <p class="mb-0">
                Saldo digunakan sebagai anggaran pekerjaan dan akan dikunci selama job aktif.
              </p>
            </VTimelineItem>

            <VTimelineItem
              dot-color="info"
              size="x-small"
            >
              <div class="d-flex justify-space-between align-center flex-wrap">
                <h6 class="text-base font-weight-semibold me-3">
                  Membuat Pekerjaan
                </h6>
              </div>
              <p class="mb-0">
                Tentukan jenis pekerjaan, bayaran per pekerja, dan total anggaran.
              </p>
            </VTimelineItem>

            <VTimelineItem
              dot-color="warning"
              size="x-small"
            >
              <div class="d-flex justify-space-between align-center flex-wrap">
                <h6 class="text-base font-weight-semibold me-3">
                  Publikasi Pekerjaan
                </h6>
              </div>
              <p class="mb-0">
                Setelah dipublikasikan, pekerjaan dapat langsung diambil oleh pekerja.
              </p>
            </VTimelineItem>

            <VTimelineItem
              dot-color="error"
              size="x-small"
            >
              <div class="d-flex justify-space-between align-center flex-wrap">
                <h6 class="text-base font-weight-semibold me-3">
                  Proses Verifikasi
                </h6>
              </div>
              <p class="mb-0">
                Tinjau bukti pengerjaan dari pekerja dan lakukan persetujuan atau penolakan.
              </p>
            </VTimelineItem>

            <VTimelineItem
              dot-color="secondary"
              size="x-small"
            >
              <div class="d-flex justify-space-between align-center flex-wrap">
                <h6 class="text-base font-weight-semibold me-3">
                  Distribusi Pembayaran
                </h6>
              </div>
              <p class="mb-0">
                Pembayaran dilakukan secara otomatis untuk setiap pekerjaan yang disetujui.
              </p>
            </VTimelineItem>

            <VTimelineItem
              dot-color="primary"
              size="x-small"
            >
              <div class="d-flex justify-space-between align-center flex-wrap">
                <h6 class="text-base font-weight-semibold me-3">
                  Penyelesaian Pekerjaan
                </h6>
              </div>
              <p class="mb-0">
                Pekerjaan akan ditutup secara otomatis sesuai ketentuan yang berlaku.
              </p>
            </VTimelineItem>
          </VTimeline>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
