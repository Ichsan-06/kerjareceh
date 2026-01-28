<script setup>
import axios from '@/plugins/axios'
import { onMounted, ref } from 'vue'

const users = ref([])
const pagination = ref({
  currentPage: 1,
  lastPage: 1,
  total: 0,
  perPage: 10,
})

const fetchUsers = async (page = 1) => {
  try {
    const response = await axios.get(`/api/users?page=${page}`)

    users.value = response.data.data
    pagination.value = {
      currentPage: response.data.current_page,
      lastPage: response.data.last_page,
      total: response.data.total,
      perPage: response.data.per_page,
    }
  } catch (error) {
    console.error('Error fetching users:', error)
  }
}

const deleteUser = async id => {
  if (confirm('Are you sure you want to delete this user?')) {
    try {
      await axios.delete(`/api/users/${id}`)
      fetchUsers(pagination.value.currentPage)
    } catch (error) {
      console.error('Error deleting user:', error)
    }
  }
}

onMounted(() => {
  fetchUsers()
})
</script>

<template>
  <VCard title="User Management">
    <VCardText>
      <VBtn
        color="primary"
        to="/users/add"
        class="mb-4"
      >
        Add New User
      </VBtn>
    </VCardText>

    <VTable>
      <thead>
        <tr>
          <th class="text-uppercase">
            No
          </th>
          <th class="text-uppercase">
            Name
          </th>
          <th class="text-uppercase">
            Email
          </th>
          <th class="text-uppercase">
            Actions
          </th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="(user, index) in users"
          :key="user.id"
        >
          <td>{{ (pagination.currentPage - 1) * pagination.perPage + index + 1 }}</td>
          <td>{{ user.name }}</td>
          <td>{{ user.email }}</td>
          <td>
            <VBtn
              icon
              variant="text"
              color="primary"
              :to="`/users/${user.id}/edit`"
            >
              <VIcon icon="bx-edit" />
            </VBtn>
            <VBtn
              icon
              variant="text"
              color="error"
              @click="deleteUser(user.id)"
            >
              <VIcon icon="bx-trash" />
            </VBtn>
          </td>
        </tr>
      </tbody>
    </VTable>
    <VCardText>
      <VPagination
        v-model="pagination.currentPage"
        :length="pagination.lastPage"
        @update:model-value="fetchUsers"
      />
    </VCardText>
  </VCard>
</template>
