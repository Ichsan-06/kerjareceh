<script setup>
import axios from '@/plugins/axios';
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();
const jobId = route.params.id;

const jobTypes = ref([]);
const valid = ref(false);

const form = ref({
  title: '',
  job_type_id: null,
  description: '',
  reward_per_worker: 0,
  total_budget: 0,
  total_slot: 1,
  start_at: '',
  end_at: '',
  status: 'draft',
});

const statusOptions = ['draft', 'active', 'paused', 'completed', 'cancelled'];

const fetchJobTypes = async () => {
  try {
    const response = await axios.get('/api/job-types');
    jobTypes.value = response.data;
  } catch (error) {
    console.error('Error fetching job types:', error);
  }
};

const fetchJob = async () => {
    try {
        const response = await axios.get(`/api/jobs/${jobId}`);
        const job = response.data;
        // Format dates if needed for datetime-local input
        // Simple assignment for now, improvement might be needed for date format 'YYYY-MM-DDTHH:mm'
        form.value = {
            ...job,
            start_at: job.start_at ? job.start_at.slice(0, 16) : '',
            end_at: job.end_at ? job.end_at.slice(0, 16) : ''
        };
    } catch (error) {
        console.error('Error fetching job:', error);
        router.push('/jobs');
    }
}

const submit = async () => {
  try {
    await axios.put(`/api/jobs/${jobId}`, form.value);
    router.push('/jobs');
  } catch (error) {
    console.error('Error updating job:', error);
    alert('Failed to update job');
  }
};

onMounted(() => {
  fetchJobTypes();
  fetchJob();
});
</script>

<template>
  <VCard title="Edit Job">
    <VCardText>
      <VForm v-model="valid" @submit.prevent="submit">
        <VRow>
          <VCol cols="12" md="6">
            <VTextField
              v-model="form.title"
              label="Job Title"
              required
            />
          </VCol>

          <VCol cols="12" md="6">
            <VSelect
              v-model="form.job_type_id"
              :items="jobTypes"
              item-title="name"
              item-value="id"
              label="Job Type"
              required
            />
          </VCol>

           <VCol cols="12" md="6">
            <VSelect
              v-model="form.status"
              :items="statusOptions"
              label="Status"
              required
              class="text-capitalize"
            />
          </VCol>


          <VCol cols="12">
            <VTextarea
              v-model="form.description"
              label="Description"
              rows="3"
            />
          </VCol>

          <VCol cols="12" md="4">
            <VTextField
              v-model="form.reward_per_worker"
              label="Reward per Worker ($)"
              type="number"
              min="0"
              required
            />
          </VCol>

          <VCol cols="12" md="4">
            <VTextField
              v-model="form.total_budget"
              label="Total Budget ($)"
              type="number"
              min="0"
              required
            />
          </VCol>

          <VCol cols="12" md="4">
            <VTextField
              v-model="form.total_slot"
              label="Total Slots"
              type="number"
              min="1"
              required
            />
          </VCol>

           <VCol cols="12" md="6">
            <VTextField
              v-model="form.start_at"
              label="Start Date"
              type="datetime-local"
              label-visible
            />
          </VCol>

           <VCol cols="12" md="6">
            <VTextField
              v-model="form.end_at"
              label="End Date"
              type="datetime-local"
              label-visible
            />
          </VCol>

          <VCol cols="12" class="d-flex gap-4">
            <VBtn type="submit" color="primary">Update</VBtn>
            <VBtn variant="tonal" color="secondary" to="/jobs">Cancel</VBtn>
          </VCol>
        </VRow>
      </VForm>
    </VCardText>
  </VCard>
</template>
