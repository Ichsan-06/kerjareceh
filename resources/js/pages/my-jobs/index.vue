<script setup>
import axios from '@/plugins/axios';
import { onMounted, ref } from 'vue';

const slots = ref([]);
const loading = ref(false);

const headers = [
  { title: 'Job Title', key: 'job.title' },
  { title: 'Reward', key: 'job.reward_per_worker' },
  { title: 'Status', key: 'status' },
  { title: 'Taken At', key: 'created_at' },
  { title: 'Actions', key: 'actions', sortable: false },
];

const fetchMyJobs = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/my-jobs');
    // API returns paginated response or list, checking structure from JobSlotController@index
    // Controller returns paginate(10) which wraps data in 'data'
    slots.value = response.data.data ? response.data.data : response.data;
  } catch (error) {
    console.error('Error fetching my jobs:', error);
  } finally {
    loading.value = false;
  }
};

const resolveStatusVariant = (status) => {
    if (status === 'reserved') return 'warning';
    if (status === 'submitted') return 'info';
    if (status === 'approved') return 'success';
    if (status === 'rejected') return 'error';
    return 'default';
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};

onMounted(() => {
  fetchMyJobs();
});
</script>

<template>
  <VContainer>
    <div class="d-flex justify-space-between align-center mb-6">
       <h2 class="text-h4">My Jobs</h2>
       <VBtn to="/jobs" prepend-icon="bx-plus">Find More Jobs</VBtn>
    </div>

    <VCard>
        <VDataTable
            :headers="headers"
            :items="slots"
            :loading="loading"
            class="text-no-wrap"
        >
            <template #item.job.title="{ item }">
                <div class="d-flex flex-column">
                    <span class="font-weight-medium">{{ item.job?.title }}</span>
                    <span class="text-caption text-disabled">ID: {{ item.job_id }}</span>
                </div>
            </template>

            <template #item.job.reward_per_worker="{ item }">
                <span class="text-success font-weight-medium">${{ item.job?.reward_per_worker }}</span>
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
                    :to="`/jobs/${item.job_id}`"
                >
                    <VIcon icon="bx-show" />
                </VBtn>
            </template>
            
            <template #no-data>
                <div class="text-center pa-4">
                    <p class="text-medium-emphasis">You haven't taken any jobs yet.</p>
                </div>
            </template>
        </VDataTable>
    </VCard>
  </VContainer>
</template>
