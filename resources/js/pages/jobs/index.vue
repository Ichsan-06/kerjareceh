<script setup>
import axios from '@/plugins/axios';
import avatar1 from '@images/avatars/avatar-1.png'; // Placeholder avatar
import { onMounted, ref } from 'vue';

const jobs = ref([]);
const loading = ref(false);

const fetchJobs = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/jobs');
    jobs.value = response.data.data;
  } catch (error) {
    console.error('Error fetching jobs:', error);
  } finally {
    loading.value = false;
  }
};

const deleteJob = async (id) => {
  if (!confirm('Are you sure you want to delete this job?')) return;
  try {
    await axios.delete(`/api/jobs/${id}`);
    fetchJobs();
  } catch (error) {
    console.error('Error deleting job:', error);
  }
};

const resolveStatusVariant = (status) => {
  if (status === 'active') return 'success';
  if (status === 'draft') return 'secondary';
  if (status === 'completed') return 'info';
  if (status === 'cancelled') return 'error';
  return 'primary';
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};

onMounted(() => {
  fetchJobs();
});
</script>

<template>
  <VContainer>
    <VRow justify="center">
      <VCol cols="12" md="8">
        <!-- Header / Actions -->
        <div class="d-flex justify-space-between align-center mb-6">
          <h2 class="text-h4">Job Feed</h2>
          <VBtn color="primary" to="/jobs/add" prepend-icon="bx-plus">Post Job</VBtn>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center my-6">
          <VProgressCircular indeterminate color="primary" />
        </div>

        <!-- Empty State -->
        <div v-else-if="jobs.length === 0" class="text-center my-6 text-medium-emphasis">
          <VIcon icon="bx-briefcase" size="large" class="mb-2" />
          <p>No jobs available yet.</p>
        </div>

        <!-- Job Cards Feed -->
        <div v-else class="job-feed">
          <VCard v-for="job in jobs" :key="job.id" class="mb-6 job-card" elevation="2">
            
            <!-- Card Header: User Info -->
            <VCardItem>
              <template #prepend>
                <VAvatar size="40">
                  <VImg :src="avatar1" />
                </VAvatar>
              </template>
              <VCardTitle>{{ job.provider ? job.provider.name : 'Unknown Provider' }}</VCardTitle>
              <VCardSubtitle>
                Posted on {{ formatDate(job.created_at) }}
                <VIcon icon="bx-globe" size="x-small" class="ms-1" />
              </VCardSubtitle>
              <template #append>
                <VChip :color="resolveStatusVariant(job.status)" size="small" label class="text-capitalize">
                    {{ job.status }}
                </VChip>
                 <VMenu>
                  <template #activator="{ props }">
                    <VBtn icon="bx-dots-vertical-rounded" variant="text" v-bind="props" />
                  </template>
                  <VList>
                    <VListItem :to="`/jobs/${job.id}/edit`" prepend-icon="bx-edit" title="Edit" />
                    <VListItem @click="deleteJob(job.id)" prepend-icon="bx-trash" title="Delete" class="text-error" />
                  </VList>
                </VMenu>
              </template>
            </VCardItem>

            <VDivider />

            <!-- Card Content: Job Details -->
            <VCardText class="text-body-1 py-4">
              <h3 class="text-h6 font-weight-bold mb-2">{{ job.title }}</h3>
              <p class="mb-4 text-medium-emphasis" style="white-space: pre-wrap;">{{ job.description }}</p>

              <div class="d-flex flex-wrap gap-2 mb-4">
                 <VChip color="primary" variant="tonal" size="small" prepend-icon="bx-tag">
                    {{ job.job_type ? job.job_type.name : 'General' }}
                 </VChip>
                 <VChip color="success" variant="tonal" size="small" prepend-icon="bx-dollar">
                    ${{ job.reward_per_worker }} / worker
                 </VChip>
              </div>

              <!-- Progress / Budget Info -->
               <VCard variant="flat" color="grey-100" class="pa-3 rounded">
                  <div class="d-flex justify-space-between text-caption mb-1">
                      <span>Slots Taken</span>
                      <span>{{ job.slot_taken }} / {{ job.total_slot }}</span>
                  </div>
                  <VProgressLinear
                    :model-value="(job.slot_taken / job.total_slot) * 100"
                    color="primary"
                    height="8"
                    rounded
                  />
                  <div class="d-flex justify-space-between text-caption mt-2 text-medium-emphasis">
                      <span>Total Budget: ${{ job.total_budget }}</span>
                      <span>Deadline: {{ formatDate(job.end_at) }}</span>
                  </div>
               </VCard>

            </VCardText>

            <VDivider />

            <!-- Card Actions (Social style) -->
            <VCardActions>
              <VBtn variant="text" color="medium-emphasis" prepend-icon="bx-heart">
                  Like
              </VBtn>
               <VBtn variant="text" color="medium-emphasis" prepend-icon="bx-comment">
                  Comment
              </VBtn>
              <VSpacer />
              <VBtn variant="tonal" color="primary" :to="`/jobs/${job.id}`">
                  View Details
              </VBtn>
            </VCardActions>
          </VCard>
        </div>

      </VCol>
    </VRow>
  </VContainer>
</template>

<style scoped>
.job-card {
    border-radius: 12px;
}
</style>
