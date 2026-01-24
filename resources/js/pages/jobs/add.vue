<script setup>
import axios from '@/plugins/axios';
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const jobTypes = ref([]);
const valid = ref(false);

const form = ref({
  title: '',
  job_type_id: null,
  description: '',
  total_budget: 0,
  total_slot: 1,
  start_at: '',
  end_at: '',
});

const calculatedReward = computed(() => {
    if (form.value.total_slot > 0 && form.value.total_budget > 0) {
        return (form.value.total_budget / form.value.total_slot).toFixed(2);
    }
    return '0.00';
});

const fetchJobTypes = async () => {
  try {
    const response = await axios.get('/api/job-types');
    jobTypes.value = response.data;
  } catch (error) {
    console.error('Error fetching job types:', error);
  }
};

const submit = async () => {
  try {
    await axios.post('/api/jobs', form.value);
    router.push('/jobs');
  } catch (error) {
    console.error('Error creating job:', error);
    alert('Failed to create job');
  }
};

onMounted(() => {
  fetchJobTypes();
});
</script>

<template>
  <VCard title="Post New Job">
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

          <VCol cols="12">
            <VTextarea
              v-model="form.description"
              label="Description"
              rows="3"
            />
          </VCol>

          <VCol cols="12" md="4">
            <VTextField
              :model-value="calculatedReward"
              label="Reward per Worker ($)"
              readonly
              variant="filled"
              hint="Auto-calculated from Budget / Slots"
              persistent-hint
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
            <VBtn type="submit" color="primary">Submit</VBtn>
            <VBtn variant="tonal" color="secondary" to="/jobs">Cancel</VBtn>
          </VCol>
        </VRow>
      </VForm>
    </VCardText>
  </VCard>
</template>
