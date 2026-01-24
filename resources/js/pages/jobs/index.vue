<script setup>
import axios from '@/plugins/axios';
import { formatCurrency, formatDate } from '@/utils/formatters';
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
  if (!confirm('Apakah anda yakin ingin menghapus pekerjaan ini?')) return;
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

// Comments Logic
const toggleComments = async (jobId) => {
    const job = jobs.value.find(j => j.id === jobId);
    if (!job) return;

    job.commentOpen = !job.commentOpen;

    if (job.commentOpen && !job.commentsLoaded) {
        job.commentsLoading = true;
        try {
            const response = await axios.get(`/api/jobs/${jobId}/comments`);
            job.comments = response.data;
            job.commentsLoaded = true;
        } catch (error) {
            console.error('Error fetching comments:', error);
        } finally {
            job.commentsLoading = false;
        }
    }
};

const postComment = async (job) => {
    if (!job.newComment || job.newComment.trim() === '') return;

    job.postingComment = true;
    try {
        const response = await axios.post('/api/comments', {
            job_id: job.id,
            content: job.newComment
        });
        
        if (!job.comments) job.comments = [];
        job.comments.unshift(response.data); // Add new comment to top
        job.newComment = ''; // Clear input
    } catch (error) {
        console.error('Error posting comment:', error);
    } finally {
        job.postingComment = false;
    }
};

onMounted(() => {
  fetchJobs();
});
</script>

<template>
  <VContainer fluid>
    <VRow justify="center">
      <VCol cols="12" md="10">
        <!-- Header / Actions -->
        <div class="d-flex justify-space-between align-center mb-6">
          <h2 class="text-h4">Bursa Pekerjaan</h2>
          <VBtn color="primary" to="/jobs/add" prepend-icon="bx-plus">Pasang Pekerjaan</VBtn>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center my-6">
          <VProgressCircular indeterminate color="primary" />
        </div>

        <!-- Empty State -->
        <div v-else-if="jobs.length === 0" class="text-center my-6 text-medium-emphasis">
          <VIcon icon="bx-briefcase" size="large" class="mb-2" />
          <p>Belum ada pekerjaan tersedia.</p>
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
                Diposting pada {{ formatDate(job.created_at) }}
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
                    <VListItem @click="deleteJob(job.id)" prepend-icon="bx-trash" title="Hapus" class="text-error" />
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
                    {{ job.job_type ? job.job_type.name : 'Umum' }}
                 </VChip>
                 <VChip color="success" variant="tonal" size="small" prepend-icon="bx-money">
                    {{ formatCurrency(job.reward_per_worker) }} / pekerja
                 </VChip>
              </div>

              <!-- Progress / Budget Info -->
               <VCard variant="flat" color="grey-100" class="pa-3 rounded">
                  <div class="d-flex justify-space-between text-caption mb-1">
                      <span>Slot Terambil</span>
                      <span>{{ job.slot_taken }} / {{ job.total_slot }}</span>
                  </div>
                  <VProgressLinear
                    :model-value="(job.slot_taken / job.total_slot) * 100"
                    color="primary"
                    height="8"
                    rounded
                  />
                  <div class="d-flex justify-space-between text-caption mt-2 text-medium-emphasis">
                      <span>Total Anggaran: {{ formatCurrency(job.total_budget) }}</span>
                      <span>Batas Waktu: {{ formatDate(job.end_at) }}</span>
                  </div>
               </VCard>

            </VCardText>

            <VDivider />


            <!-- Card Actions -->
            <VCardActions>
              <VBtn variant="text" color="medium-emphasis" prepend-icon="bx-comment" @click="toggleComments(job.id)">
                  {{ job.commentOpen ? 'Sembunyikan Komentar' : 'Komentar' }}
              </VBtn>
              <VSpacer />
              <VBtn variant="tonal" color="primary" :to="`/jobs/${job.id}`">
                  Lihat Detail
              </VBtn>
            </VCardActions>

            <!-- Comments Section (Expansion Panel) -->
            <VExpandTransition>
                <div v-if="job.commentOpen">
                    <VDivider />
                    <VCardText>
                        <!-- List Comments -->
                        <div v-if="job.commentsLoading" class="text-center">
                            <VProgressCircular indeterminate size="24" color="primary" />
                        </div>
                        <div v-else-if="job.comments && job.comments.length > 0" class="mb-4">
                            <div v-for="comment in job.comments" :key="comment.id" class="d-flex gap-3 mb-3">
                                <VAvatar size="32" color="secondary" variant="tonal">
                                    {{ comment.user.name.charAt(0) }}
                                </VAvatar>
                                <div class="bg-grey-100 pa-2 rounded flex-grow-1">
                                    <div class="d-flex justify-space-between align-center">
                                        <span class="text-subtitle-2 font-weight-bold">{{ comment.user.name }}</span>
                                        <span class="text-caption text-medium-emphasis">{{ formatDate(comment.created_at) }}</span>
                                    </div>
                                    <p class="text-body-2 mb-0">{{ comment.content }}</p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center text-caption text-medium-emphasis mb-4">
                            Belum ada komentar. Jadilah yang pertama!
                        </div>

                        <!-- Post Comment -->
                        <div class="d-flex gap-2">
                            <VTextField
                                v-model="job.newComment"
                                placeholder="Tulis komentar..."
                                variant="outlined"
                                density="compact"
                                hide-details
                                @keyup.enter="postComment(job)"
                            />
                            <VBtn icon="bx-send" variant="text" color="primary" :loading="job.postingComment" @click="postComment(job)" />
                        </div>
                    </VCardText>
                </div>
            </VExpandTransition>
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
