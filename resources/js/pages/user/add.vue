<script setup>
import axios from 'axios';
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const form = ref({
  name: '',
  email: '',
  password: '',
});

const errors = ref({});

const submit = async () => {
  try {
    await axios.post('/api/users', form.value);
    router.push('/users');
  } catch (error) {
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors;
    } else {
      console.error('Error creating user:', error);
    }
  }
};
</script>

<template>
  <VCard title="Add New User">
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
              label="Password"
              type="password"
              :error-messages="errors.password"
              required
            />
          </VCol>
          <VCol cols="12" class="d-flex gap-4">
            <VBtn type="submit">Submit</VBtn>
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
