<script setup>
import axios from '@/plugins/axios'
import { formatCurrency, formatDate } from '@/utils/formatters'
import { onMounted, ref } from 'vue'

const jobs = ref([])
const loading = ref(false)

const headers = [
  { title: 'Judul', key: 'title' },
  { title: 'Tipe', key: 'job_type.name' },
  { title: 'Anggaran', key: 'total_budget' },
  { title: 'Progres', key: 'progress' },
  { title: 'Status', key: 'status' },
  { title: 'Dibuat', key: 'created_at' },
  { title: 'Aksi', key: 'actions', sortable: false },
]

const fetchPostedJobs = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/posted-jobs')


    // API returns paginated response
    jobs.value = response.data.data ? response.data.data : response.data
  } catch (error) {
    console.error('Error fetching posted jobs:', error)
  } finally {
    loading.value = false
  }
}

const resolveStatusVariant = status => {
  if (status === 'active') return 'success'
  if (status === 'draft') return 'secondary'
  if (status === 'completed') return 'info'
  if (status === 'cancelled') return 'error'
  
  return 'primary'
}

onMounted(() => {
  fetchPostedJobs()
})
</script>

<template>
  <VContainer>
    <div class="d-flex justify-space-between align-center mb-6">
      <h2 class="text-h4">
        Pekerjaan Saya
      </h2>
      <VBtn
        to="/jobs/add"
        prepend-icon="bx-plus"
      >
        Buat Pekerjaan Baru
      </VBtn>
    </div>

    <VCard>
      <VDataTable
        :headers="headers"
        :items="jobs"
        :loading="loading"
        class="text-no-wrap"
      >
        <template #item.title="{ item }">
          <span class="font-weight-medium">{{ item.title }}</span>
        </template>

        <template #item.total_budget="{ item }">
          <span class="text-success font-weight-medium">{{ formatCurrency(item.total_budget) }}</span>
        </template>

        <template #item.progress="{ item }">
          <div class="d-flex align-center gap-2">
            <VProgressLinear
              :model-value="(item.slots_count / item.total_slot) * 100"
              color="primary"
              height="8"
              rounded
              style="width: 100px"
            />
            <span class="text-caption">{{ item.slots_count }} / {{ item.total_slot }}</span>
          </div>
        </template>

        <template #item.status="{ item }">
          <VChip
            :color="resolveStatusVariant(item.status)"
            size="small"
            label
            class="text-capitalize"
          >
            {{ item.status }}
          </VChip>
        </template>

        <template #item.created_at="{ item }">
          {{ formatDate(item.created_at) }}
        </template>

        <template #item.actions="{ item }">
          <VBtn
            icon
            variant="text"
            color="primary"
            :to="`/jobs/${item.id}`"
          >
            <VIcon icon="bx-show" />
          </VBtn>
          <VBtn
            icon
            variant="text"
            color="secondary"
            :to="`/jobs/${item.id}/edit`"
          >
            <VIcon icon="bx-edit" />
          </VBtn>
        </template>
            
        <template #no-data>
          <div class="text-center pa-4">
            <p class="text-medium-emphasis">
              Anda belum memposting pekerjaan apapun.
            </p>
          </div>
        </template>
      </VDataTable>
    </VCard>
  </VContainer>
</template>
