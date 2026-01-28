<script setup>
import axios from '@/plugins/axios'
import { can } from '@/utils/permissions'
import { onMounted, ref } from 'vue'

const feedbacks = ref([])
const loading = ref(false)
const dialog = ref(false)
const valid = ref(false)

const form = ref({
  message: '',
  type: 'bug',
})

const headers = [
  { title: 'User', key: 'user.name' },
  { title: 'Content', key: 'message' },
  { title: 'Type', key: 'type' },
  { title: 'Status', key: 'status' },
  { title: 'Created At', key: 'created_at' },
  { title: 'Actions', key: 'actions', sortable: false },
]

const fetchFeedbacks = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/feedback')

    feedbacks.value = response.data
  } catch (error) {
    console.error('Error fetching feedbacks:', error)
  } finally {
    loading.value = false
  }
}

const submitFeedback = async () => {
  if (!form.value.message) return
  
  try {
    await axios.post('/api/feedback', form.value)
    dialog.value = false
    form.value = { message: '', type: 'bug' }
    fetchFeedbacks()
  } catch (error) {
    console.error('Error submitting feedback:', error)
  }
}

const deleteFeedback = async id => {
  if (!confirm('Are you sure you want to delete this feedback?')) return
  
  try {
    await axios.delete(`/api/feedback/${id}`)
    fetchFeedbacks()
  } catch (error) {
    console.error('Error deleting feedback:', error)
  }
}

const resolveTypeColor = type => {
  if (type === 'bug') return 'error'
  if (type === 'feature') return 'info'
  
  return 'primary'
}

onMounted(() => {
  if (can('read feedback')) {
    fetchFeedbacks()
  }
})
</script>

<template>
  <VContainer>
    <div class="d-flex justify-space-between align-center mb-6">
      <h2 class="text-h4">
        Feedback
      </h2>
      <VBtn
        v-if="can('create feedback')"
        color="primary"
        @click="dialog = true"
      >
        Send Feedback
      </VBtn>
    </div>

    <!-- Feedback List (Admin/Provider mainly, or users see their own if modified) -->
    <VCard v-if="can('read feedback')">
      <VDataTable
        :headers="headers"
        :items="feedbacks"
        :loading="loading"
      >
        <template #item.type="{ item }">
          <VChip
            :color="resolveTypeColor(item.type)"
            size="small"
            label
            class="text-capitalize"
          >
            {{ item.type }}
          </VChip>
        </template>

        <template #item.status="{ item }">
          <VChip
            color="default"
            size="small"
            label
            class="text-capitalize"
          >
            {{ item.status }}
          </VChip>
        </template>

        <template #item.created_at="{ item }">
          {{ new Date(item.created_at).toLocaleDateString() }}
        </template>

        <template #item.actions="{ item }">
          <VBtn
            v-if="can('delete feedback')"
            icon
            variant="text"
            color="error"
            @click="deleteFeedback(item.id)"
          >
            <VIcon icon="bx-trash" />
          </VBtn>
        </template>
      </VDataTable>
    </VCard>

    <!-- Feedback Dialog -->
    <VDialog
      v-model="dialog"
      max-width="500"
    >
      <VCard title="Send Feedback">
        <VCardText>
          <VForm
            v-model="valid"
            @submit.prevent="submitFeedback"
          >
            <VSelect
              v-model="form.type"
              :items="['bug', 'feature', 'other']"
              label="Type"
              required
              class="mb-4"
            />
            <VTextarea
              v-model="form.message"
              label="Message"
              rows="3"
              required
            />
          </VForm>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn
            variant="text"
            @click="dialog = false"
          >
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            @click="submitFeedback"
          >
            Send
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </VContainer>
</template>
