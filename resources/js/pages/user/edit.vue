<script setup>
import axios from '@/plugins/axios';
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();
const userId = route.params.id;

const form = ref({
  name: '',
  email: '',
  password: '',
  role: null,
});

const roles = ref([]);
const errors = ref({});

const fetchRoles = async () => {
    try {
        const response = await axios.get('/api/roles');
        roles.value = response.data;
    } catch (error) {
        console.error('Error fetching roles:', error);
    }
};

const fetchUser = async () => {
  try {
    const response = await axios.get(`/api/users/${userId}`);
    const user = response.data;
    form.value.name = user.name;
    form.value.email = user.email;
    if (user.roles && user.roles.length > 0) {
        form.value.role = user.roles[0].name;
    }
    // Password is left empty intentionally
  } catch (error) {
    console.error('Error fetching user:', error);
    router.push('/users'); // Redirect if not found
  }
};

const submit = async () => {
  try {
    await axios.put(`/api/users/${userId}`, form.value);
    router.push('/users');
  } catch (error) {
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors;
    } else {
      console.error('Error updating user:', error);
    }
  }
};

onMounted(() => {
  fetchRoles();
  fetchUser();
});
</script>

<template>
  <VCard title="Edit Pengguna">
    <VCardText>
      <VForm @submit.prevent="submit">
        <VRow>
          <VCol cols="12">
            <VTextField
              v-model="form.name"
              label="Nama"
              :error-messages="errors.name"
              required
            />
          </VCol>
          <VCol cols="12">
            <VTextField
              v-model="form.email"
              label="Email"
              type="email"
              :error-messages="errors.email"
              required
            />
          </VCol>
           <VCol cols="12">
            <VSelect
              v-model="form.role"
              :items="roles"
              item-title="name"
              item-value="name"
              label="Peran / Role"
              :error-messages="errors.role"
              required
            />
          </VCol>
          <VCol cols="12">
            <VTextField
              v-model="form.password"
              label="Password (Biarkan kosong jika tidak ingin mengubah)"
              type="password"
              :error-messages="errors.password"
            />
          </VCol>
          <VCol cols="12" class="d-flex gap-4">
            <VBtn type="submit">Update</VBtn>
            <VBtn
              type="reset"
              color="secondary"
              variant="tonal"
              to="/users"
            >
              Batal
            </VBtn>
          </VCol>
        </VRow>
      </VForm>
    </VCardText>
  </VCard>
</template>
