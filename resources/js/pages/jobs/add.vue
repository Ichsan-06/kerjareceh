<script setup>
import axios from '@/plugins/axios'
import { formatCurrency } from '@/utils/formatters'
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const jobTypes = ref([])
const valid = ref(false)

const form = ref({
  title: '',
  job_type_id: null,
  description: '',
  total_budget: 0,
  total_slot: 1,
  start_at: '',
  end_at: '',
})

const calculatedReward = computed(() => {
  if (form.value.total_slot > 0 && form.value.total_budget > 0) {
    return formatCurrency(form.value.total_budget / form.value.total_slot)
  }
  
  return 'Rp 0'
})

const fetchJobTypes = async () => {
  try {
    const response = await axios.get('/api/job-types')

    jobTypes.value = response.data
  } catch (error) {
    console.error('Error fetching job types:', error)
  }
}

const submit = async () => {
  try {
    await axios.post('/api/jobs', form.value)
    router.push('/jobs')
  } catch (error) {
    console.error('Error creating job:', error)
    alert(error.response.data.message)
  }
}

onMounted(() => {
  fetchJobTypes()
})
</script>

<template>
  <VCard title="Pasang Pekerjaan Baru">
    <VCardText>
      <VForm
        v-model="valid"
        @submit.prevent="submit"
      >
        <VRow>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="form.title"
              label="Judul Pekerjaan"
              required
            />
          </VCol>

          <VCol
            cols="12"
            md="6"
          >
            <VSelect
              v-model="form.job_type_id"
              :items="jobTypes"
              item-title="name"
              item-value="id"
              label="Tipe Pekerjaan"
              required
            />
          </VCol>

          <VCol cols="12">
            <VTextarea
              v-model="form.description"
              label="Deskripsi"
              rows="3"
            />
          </VCol>

          <VCol
            cols="12"
            md="4"
          >
            <VTextField
              :model-value="calculatedReward"
              label="Bayaran per Pekerja"
              readonly
              variant="filled"
              hint="Dihitung otomatis dari Anggaran / Slot"
              persistent-hint
            />
          </VCol>

          <VCol
            cols="12"
            md="4"
          >
            <VTextField
              v-model="form.total_budget"
              label="Total Anggaran"
              type="number"
              min="0"
              required
            />
          </VCol>

          <VCol
            cols="12"
            md="4"
          >
            <VTextField
              v-model="form.total_slot"
              label="Total Slot"
              type="number"
              min="1"
              required
            />
          </VCol>

          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="form.start_at"
              label="Tanggal Mulai"
              type="datetime-local"
              label-visible
            />
          </VCol>

          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="form.end_at"
              label="Tanggal Selesai (Deadline)"
              type="datetime-local"
              label-visible
            />
          </VCol>

          <VCol
            cols="12"
            class="d-flex gap-4"
          >
            <VBtn
              type="submit"
              color="primary"
            >
              Kirim
            </VBtn>
            <VBtn
              variant="tonal"
              color="secondary"
              to="/jobs"
            >
              Batal
            </VBtn>
          </VCol>
        </VRow>
      </VForm>
    </VCardText>
  </VCard>
</template>
