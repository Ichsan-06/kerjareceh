<script setup>
import axios from '@/plugins/axios';
import { formatCurrency, formatDate } from '@/utils/formatters';
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();
const jobId = route.params.id;
const job = ref(null);
const loading = ref(false);
const actionLoading = ref(false);

const currentUser = ref(JSON.parse(localStorage.getItem('userData') || '{}'));
const providerSlots = ref([]);
const providerLoading = ref(false);

const publicParticipants = ref([]);
const participantsLoading = ref(false);

// Submission Form
const screenshot = ref(null);
const submissionNote = ref('');
const submissionDialog = ref(false);

// Dispute Form
const disputeReason = ref('');
const disputeDialog = ref(false);

// Approval/Rejection Form
const approvalDialog = ref(false);
const selectedSlot = ref(null);
const decision = ref('');
const decisionReason = ref('');

const fetchJob = async () => {
  loading.value = true;
  try {
    const response = await axios.get(`/api/jobs/${jobId}`);
    job.value = response.data;
    
    if (isProvider.value) {
        fetchProviderSlots();
    } else {
        fetchPublicParticipants();
    }
  } catch (error) {
    console.error('Error fetching job:', error);
  } finally {
    loading.value = false;
  }
};

const fetchProviderSlots = async () => {
    providerLoading.value = true;
    try {
        const response = await axios.get(`/api/jobs/${jobId}/slots`);
        providerSlots.value = response.data;
    } catch (error) {
        console.error('Error fetching slots:', error);
    } finally {
        providerLoading.value = false;
    }
};

const fetchPublicParticipants = async () => {
    participantsLoading.value = true;
    try {
        const response = await axios.get(`/api/jobs/${jobId}/participants`);
        publicParticipants.value = response.data;
    } catch (error) {
        console.error('Error fetching participants:', error);
    } finally {
        participantsLoading.value = false;
    }
};

const deleteJob = async () => {
  if (!confirm('Are you sure you want to delete this job?')) return;
  try {
    await axios.delete(`/api/jobs/${jobId}`);
    router.push('/jobs');
  } catch (error) {
    console.error('Error deleting job:', error);
  }
};

const takeJob = async () => {
    actionLoading.value = true;
    try {
        await axios.post('/api/jobs/take', { job_id: job.value.id });
        await fetchJob(); // Refresh
    } catch (error) {
        console.error('Error taking job:', error);
        alert(error.response?.data?.message || 'Failed to take job');
    } finally {
        actionLoading.value = false;
    }
};

const submitProof = async () => {
    if (!screenshot.value) {
        alert('Please upload a screenshot');
        return;
    }
    
    actionLoading.value = true;
    const formData = new FormData();
    formData.append('job_slot_id', job.value.my_slot.id);
    
    let fileToUpload = null;
    if (Array.isArray(screenshot.value) && screenshot.value.length > 0) {
        fileToUpload = screenshot.value[0];
    } else if (screenshot.value instanceof File) {
        fileToUpload = screenshot.value;
    }

    if (fileToUpload) {
        formData.append('screenshot', fileToUpload);
    } else {
         // Fallback if structure is unexpected, though validation check passed above
         console.warn('Screenshot value format unexpected:', screenshot.value);
    }
    
    formData.append('submission_data[note]', submissionNote.value);

    try {
        await axios.post('/api/submissions', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            }
        });
        submissionDialog.value = false;
        await fetchJob(); // Refresh
    } catch (error) {
        console.error('Error submitting proof:', error);
        alert(error.response?.data?.message || 'Failed to submit proof');
    } finally {
        actionLoading.value = false;
    }
};

const openApprovalDialog = (slot, type) => {
    selectedSlot.value = slot;
    decision.value = type;
    decisionReason.value = '';
    approvalDialog.value = true;
};

const submitDecision = async () => {
    if (!selectedSlot.value) return;

    actionLoading.value = true;
    try {
        await axios.post('/api/approvals', {
            job_slot_id: selectedSlot.value.id,
            decision: decision.value,
            reason: decisionReason.value
        });
        approvalDialog.value = false;
        await fetchProviderSlots(); // Refresh list
    } catch (error) {
         console.error('Error submitting decision:', error);
         alert(error.response?.data?.message || 'Failed to submit decision');
    } finally {
        actionLoading.value = false;
    }
};

const submitDispute = async () => {
    if (!disputeReason.value) return;
    actionLoading.value = true;
    try {
        await axios.post('/api/disputes', {
            job_slot_id: job.value.my_slot.id,
            reason: disputeReason.value
        });
        disputeDialog.value = false;
        await fetchJob();
    } catch (error) {
        console.error('Error submitting dispute:', error);
        alert(error.response?.data?.message || 'Failed to submit dispute');
    } finally {
        actionLoading.value = false;
    }
};

const openImage = (url) => {
    window.open(url, '_blank');
};

const resolveStatusVariant = (status) => {
  if (status === 'active') return 'success';
  if (status === 'draft') return 'secondary';
  if (status === 'completed') return 'info';
  if (status === 'cancelled') return 'error';
  return 'primary';
};

const resolveSlotStatusVariant = (status) => {
    if (status === 'reserved') return 'warning';
    if (status === 'submitted') return 'info';
    if (status === 'approved') return 'success';
    if (status === 'rejected') return 'error';
    return 'default';
};


const isProvider = computed(() => {
    // Check various sources where user might be stored
    // Note: Adjust 'userData' key based on actual login implementation
    // Earlier login.vue snippet showed 'user' key, let's try that too
    const user = currentUser.value;
    return job.value && user && job.value.provider_id === user.id;
});

const canTakeJob = computed(() => {
    if (!job.value) return false;
    if (isProvider.value) return false; // Provider can't take own job
    if (job.value.my_slot) return false; // Already taken
    if (job.value.status !== 'active') return false; // Not active
    if (job.value.slot_taken >= job.value.total_slot) return false; // Full
    return true;
});

const mySlotStatus = computed(() => {
    return job.value?.my_slot?.status;
});

onMounted(() => {
    // Try to get user from multiple potential keys
    const userData = localStorage.getItem('userData');
    const user = localStorage.getItem('user');
    
    if (userData) {
        currentUser.value = JSON.parse(userData);
    } else if (user) {
         currentUser.value = JSON.parse(user);
    }
    
    fetchJob();
    fetchComments();
});

// Comments Logic
const comments = ref([]);
const commentsLoading = ref(false);
const newComment = ref('');
const postingComment = ref(false);

const fetchComments = async () => {
    commentsLoading.value = true;
    try {
        const response = await axios.get(`/api/jobs/${jobId}/comments`);
        comments.value = response.data;
    } catch (error) {
        console.error('Error fetching comments:', error);
    } finally {
        commentsLoading.value = false;
    }
};

const postComment = async () => {
    if (!newComment.value || newComment.value.trim() === '') return;

    postingComment.value = true;
    try {
        const response = await axios.post('/api/comments', {
            job_id: jobId,
            content: newComment.value
        });
        
        comments.value.unshift(response.data); // Add new comment to top
        newComment.value = ''; // Clear input
    } catch (error) {
        console.error('Error posting comment:', error);
    } finally {
        postingComment.value = false;
    }
};
</script>

<template>
  <VContainer>
    <div v-if="loading" class="text-center my-6">
       <VProgressCircular indeterminate color="primary" />
    </div>

    <div v-else-if="job">
        <!-- Back Button -->
        <VBtn variant="text" color="secondary" class="mb-4" to="/jobs" prepend-icon="bx-arrow-back">
            Kembali
        </VBtn>

        <!-- Status Alert for Worker -->
        <VAlert v-if="job.my_slot && !isProvider" :color="resolveSlotStatusVariant(mySlotStatus)" variant="tonal" class="mb-6" border="start">
            <div class="d-flex justify-space-between align-center">
                <div>
                    <strong>Status Saya: {{ mySlotStatus.toUpperCase() }}</strong>
                    <div v-if="mySlotStatus === 'reserved'">
                        Anda telah memesan pekerjaan ini. Silakan kirim bukti sebelum habis masa berlakunya.
                    </div>
                    <div v-else-if="mySlotStatus === 'submitted'">
                        Bukti terkirim. Menunggu persetujuan.
                    </div>
                    <div v-else-if="mySlotStatus === 'rejected'">
                        Kiriman anda ditolak.
                    </div>
                     <div v-else-if="mySlotStatus === 'approved'">
                        Selamat! Kiriman anda disetujui.
                    </div>
                </div>
                <div>
                     <VBtn v-if="mySlotStatus === 'reserved'" color="primary" @click="submissionDialog = true">
                        Kirim Bukti
                    </VBtn>
                    <VBtn v-if="mySlotStatus === 'rejected'" color="error" variant="outlined" class="ms-2" @click="disputeDialog = true">
                        Ajukan Banding
                    </VBtn>
                </div>
               
            </div>
        </VAlert>

        <VRow>
            <!-- Main Content -->
            <VCol cols="12" md="8">
                <VCard class="mb-6">
                    <VCardItem>
                        <template #prepend>
                            <VAvatar size="48" class="me-3" :variant="!job.provider?.avatar ? 'tonal' : undefined" color="primary">
                                <VImg v-if="job.provider?.avatar" :src="`/storage/${job.provider.avatar}`" />
                                <span v-else class="text-h6">{{ job.provider?.name?.charAt(0) }}</span>
                            </VAvatar>
                        </template>
                         <VCardTitle class="text-h5">
                            {{ job.title }}
                         </VCardTitle>
                         <VCardSubtitle>
                            Diposting oleh {{ job.provider ? job.provider.name : 'Unknown' }}
                         </VCardSubtitle>
                         <template #append>
                            <VChip :color="resolveStatusVariant(job.status)" label class="text-capitalize">
                                {{ job.status }}
                            </VChip>
                         </template>
                    </VCardItem>

                    <VDivider />

                    <VCardText class="text-body-1 py-4">
                        <div class="d-flex gap-4 mb-6">
                             <VChip color="primary" variant="tonal" prepend-icon="bx-tag">
                                {{ job.job_type ? job.job_type.name : 'Umum' }}
                             </VChip>
                             <VChip color="success" variant="tonal" prepend-icon="bx-money">
                                {{ formatCurrency(job.reward_per_worker) }} / pekerja
                             </VChip>
                        </div>

                        <h3 class="text-h6 font-weight-bold mb-2">Deskripsi</h3>
                        <p style="white-space: pre-wrap;">{{ job.description }}</p>
                    </VCardText>
                </VCard>

                <!-- Provider: Submissions List -->
                <VCard v-if="isProvider" title="Submissions & Slots" class="mb-6">
                     <VCardText v-if="providerLoading" class="text-center">
                        <VProgressCircular indeterminate size="24" />
                     </VCardText>
                    <VTable v-else>
                        <thead>
                            <tr>
                                <th>Pekerja</th>
                                <th>Status</th>
                                <th>Bukti</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="slot in providerSlots" :key="slot.id">
                                <td>
                                    <div class="d-flex align-center">
                                        <VAvatar size="32" class="me-2" variant="tonal" color="primary">
                                            <span>{{ slot.worker?.name?.charAt(0) || '?' }}</span>
                                        </VAvatar>
                                        {{ slot.worker?.name || 'Unknown' }}
                                    </div>
                                </td>
                                <td>
                                    <VChip :color="resolveSlotStatusVariant(slot.status)" size="small" label>
                                        {{ slot.status }}
                                    </VChip>
                                </td>
                                <td>
                                    <div v-if="slot.submission">
                                        <div v-if="slot.submission.screenshot_path">
                                            <VImg
                                                :src="`/storage/${slot.submission.screenshot_path}`"
                                                :width="100"
                                                :height="60"
                                                cover
                                                class="rounded cursor-pointer"
                                                @click="openImage(`/storage/${slot.submission.screenshot_path}`)"
                                            />
                                        </div>
                                        <div v-if="slot.submission.submission_data?.note" class="text-caption text-medium-emphasis mt-1">
                                            "{{ slot.submission.submission_data.note }}"
                                        </div>
                                    </div>
                                    <span v-else class="text-disabled">-</span>
                                </td>
                                <td>
                                    <div v-if="slot.status === 'submitted'" class="d-flex gap-2">
                                        <VBtn size="small" color="success" icon="bx-check" variant="text" @click="openApprovalDialog(slot, 'approved')" />
                                        <VBtn size="small" color="error" icon="bx-x" variant="text" @click="openApprovalDialog(slot, 'rejected')" />
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="providerSlots.length === 0">
                                <td colspan="4" class="text-center text-medium-emphasis">Belum ada slot terambil.</td>
                            </tr>
                        </tbody>
                    </VTable>
                </VCard>

                 <!-- Public: Participants List -->
                <VCard v-else title="Siapa yang mengerjakan ini?" class="mb-6">
                    <VCardText v-if="participantsLoading" class="text-center">
                        <VProgressCircular indeterminate size="24" />
                    </VCardText>
                     <VList v-else density="compact">
                        <VListItem v-for="participant in publicParticipants" :key="participant.id">
                            <template #prepend>
                                <VAvatar size="32" class="me-2" variant="tonal" color="primary">
                                    <span>{{ participant.worker?.name?.charAt(0) || '?' }}</span>
                                </VAvatar>
                            </template>
                            <VListItemTitle>{{ participant.worker?.name || 'Unknown' }}</VListItemTitle>
                            <VListItemSubtitle class="text-caption">
                                Bergabung {{ formatDate(participant.created_at) }}
                            </VListItemSubtitle>
                            <template #append>
                                <VChip :color="resolveSlotStatusVariant(participant.status)" size="x-small" label class="text-capitalize">
                                    {{ participant.status }}
                                </VChip>
                            </template>
                        </VListItem>
                        <VListItem v-if="publicParticipants.length === 0">
                            <VListItemTitle class="text-medium-emphasis text-center">Jadilah yang pertama bergabung!</VListItemTitle>
                        </VListItem>
                    </VList>
                </VCard>

                <!-- Comments Section -->
                <VCard title="Komentar & Pertanyaan" class="mt-6">
                    <VCardText>
                        <div v-if="commentsLoading" class="text-center my-2">
                            <VProgressCircular indeterminate size="24" color="primary" />
                        </div>
                        <div v-else-if="comments.length > 0" class="mb-4" style="max-height: 300px; overflow-y: auto;">
                            <div v-for="comment in comments" :key="comment.id" class="d-flex gap-3 mb-3">
                                <VAvatar size="32" color="secondary" :variant="!comment.user.avatar ? 'tonal' : undefined">
                                    <VImg v-if="comment.user.avatar" :src="`/storage/${comment.user.avatar}`" />
                                    <span v-else>{{ comment.user.name.charAt(0) }}</span>
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
                            Belum ada komentar.
                        </div>

                        <!-- Post Comment -->
                        <div class="d-flex gap-2">
                            <VTextField
                                v-model="newComment"
                                placeholder="Tanya sesuatu..."
                                variant="outlined"
                                density="compact"
                                hide-details
                                @keyup.enter="postComment"
                            />
                            <VBtn icon="bx-send" variant="text" color="primary" :loading="postingComment" @click="postComment" />
                        </div>
                    </VCardText>
                </VCard>
            </VCol>

            <!-- Sidebar Info -->
            <VCol cols="12" md="4">
                <VCard title="Detail Pekerjaan" class="mb-6">
                    <VCardText>
                         <VList density="compact">
                            <VListItem prepend-icon="bx-calendar">
                                <VListItemTitle>Tanggal Mulai</VListItemTitle>
                                <VListItemSubtitle>{{ formatDate(job.start_at) }}</VListItemSubtitle>
                            </VListItem>
                            <VListItem prepend-icon="bx-time">
                                <VListItemTitle>Batas Waktu</VListItemTitle>
                                <VListItemSubtitle>{{ formatDate(job.end_at) }}</VListItemSubtitle>
                            </VListItem>
                            <VListItem prepend-icon="bx-group">
                                <VListItemTitle>Slot</VListItemTitle>
                                <VListItemSubtitle>{{ job.slot_taken }} / {{ job.total_slot }} terisi</VListItemSubtitle>
                            </VListItem>
                             <VListItem prepend-icon="bx-wallet">
                                <VListItemTitle>Total Anggaran</VListItemTitle>
                                <VListItemSubtitle>{{ formatCurrency(job.total_budget) }}</VListItemSubtitle>
                            </VListItem>
                        </VList>
                    </VCardText>
                    <VCardActions class="px-4 pb-4">
                        <VBtn 
                            v-if="canTakeJob"
                            block 
                            color="primary" 
                            variant="elevated" 
                            :loading="actionLoading"
                            @click="takeJob"
                        >
                            Ambil Pekerjaan
                        </VBtn>
                        <VBtn 
                            v-else-if="job.my_slot && !isProvider"
                            block
                            color="success"
                            variant="tonal"
                            disabled
                        >
                            Sudah Diambil
                        </VBtn>
                         <VBtn 
                            v-else-if="!isProvider"
                            block
                            color="secondary"
                            variant="tonal"
                            disabled
                        >
                            Tidak Tersedia
                        </VBtn>
                        <VBtn
                            v-else
                            block
                            color="info"
                            variant="tonal"
                            disabled
                        >
                            Anda adalah Pemilik
                        </VBtn>
                    </VCardActions>
                </VCard>



                <VCard v-if="isProvider" title="Admin Actions" class="mt-4">
                    <VCardText class="d-flex flex-column gap-2">
                        <VBtn :to="`/jobs/${job.id}/edit`" prepend-icon="bx-edit" variant="tonal" color="primary" block>
                            Edit Pekerjaan
                        </VBtn>
                        <VBtn @click="deleteJob" prepend-icon="bx-trash" variant="tonal" color="error" block>
                            Hapus Pekerjaan
                        </VBtn>
                    </VCardText>
                </VCard>
            </VCol>
        </VRow>
    </div>
     <div v-else class="text-center my-6">
        <h3 class="text-h5 text-medium-emphasis">Pekerjaan tidak ditemukan</h3>
        <VBtn to="/jobs" color="primary" class="mt-4">Kembali ke Daftar</VBtn>
     </div>

     <!-- Submission Dialog -->
     <VDialog v-model="submissionDialog" max-width="500">
        <VCard title="Kirim Bukti">
            <VCardText>
                <VFileInput 
                    v-model="screenshot" 
                    label="Upload Screenshot" 
                    prepend-icon="bx-camera" 
                    accept="image/*"
                    show-size
                />
                <VTextarea 
                    v-model="submissionNote"
                    label="Catatan (Opsional)"
                    rows="3"
                    class="mt-3"
                />
            </VCardText>
            <VCardActions>
                <VSpacer />
                <VBtn color="secondary" variant="text" @click="submissionDialog = false">Batal</VBtn>
                <VBtn color="primary" :loading="actionLoading" @click="submitProof">Kirim</VBtn>
            </VCardActions>
        </VCard>
     </VDialog>

      <!-- Dispute Dialog -->
     <VDialog v-model="disputeDialog" max-width="500">
        <VCard title="Ajukan Banding">
            <VCardText>
                <p class="text-body-2 mb-4">Jelaskan mengapa menurut anda penolakan ini tidak adil.</p>
                <VTextarea 
                    v-model="disputeReason"
                    label="Alasan"
                    rows="4"
                />
            </VCardText>
            <VCardActions>
                <VSpacer />
                <VBtn color="secondary" variant="text" @click="disputeDialog = false">Batal</VBtn>
                <VBtn color="error" :loading="actionLoading" @click="submitDispute">Kirim Banding</VBtn>
            </VCardActions>
        </VCard>
     </VDialog>

      <!-- Approval/Reject Dialog -->
     <VDialog v-model="approvalDialog" max-width="500">
        <VCard :title="decision === 'approved' ? 'Setujui Pengajuan' : 'Tolak Pengajuan'">
            <VCardText>
                <p class="text-body-2 mb-4">
                    Apakah anda yakin ingin {{ decision === 'approved' ? 'menyetujui' : 'menolak' }} pengajuan dari {{ selectedSlot?.worker?.name }}?
                </p>
                <VTextarea 
                    v-model="decisionReason"
                    label="Alasan / Umpan Balik (Opsional)"
                    rows="3"
                />
            </VCardText>
            <VCardActions>
                <VSpacer />
                <VBtn color="secondary" variant="text" @click="approvalDialog = false">Batal</VBtn>
                <VBtn :color="decision === 'approved' ? 'success' : 'error'" :loading="actionLoading" @click="submitDecision">
                    Konfirmasi {{ decision === 'approved' ? 'Persetujuan' : 'Penolakan' }}
                </VBtn>
            </VCardActions>
        </VCard>
     </VDialog>
  </VContainer>
</template>
