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
});

const errors = ref({});

const fetchUser = async () => {
  try {
    const response = await axios.get(`/api/users/${userId}`);
    const user = response.data;
    form.value.name = user.name;
    form.value.email = user.email;
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
  fetchUser();
});
</script>

<template>
  <VCard title="Edit User">
    <VCardText>
      <VForm @submit.prevent="submit">
        <VRow>
          <VCol cols="12">
            <VTextField
              v-model="form.name"
              label="Name"
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
            <VTextField
              v-model="form.password"
              label="Password (Leave blank to keep current)"
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
              Cancel
            </VBtn>
          </VCol>
        </VRow>
      </VForm>
    </VCardText>
  </VCard>
</template>
