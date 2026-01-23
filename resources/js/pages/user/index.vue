<script setup>
import axios from 'axios';
import { onMounted, ref } from 'vue';

const users = ref([]);

const fetchUsers = async () => {
  try {
    const response = await axios.get('/api/users');
    // Assuming the API returns keys like { data: [...] } or just [...]
    // based on the controller: return response()->json($users); where $users is paginated
    users.value = response.data.data; 
  } catch (error) {
    console.error('Error fetching users:', error);
  }
};

const deleteUser = async (id) => {
  if (confirm('Are you sure you want to delete this user?')) {
    try {
      await axios.delete(`/api/users/${id}`);
      fetchUsers();
    } catch (error) {
      console.error('Error deleting user:', error);
    }
  }
};

onMounted(() => {
  fetchUsers();
});
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
          <th class="text-uppercase">ID</th>
          <th class="text-uppercase">Name</th>
          <th class="text-uppercase">Email</th>
          <th class="text-uppercase">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="user in users"
          :key="user.id"
        >
          <td>{{ user.id }}</td>
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
  </VCard>
</template>
