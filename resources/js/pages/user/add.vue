<script setup>
import axios from '@/plugins/axios'
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const form = ref({
  name: '',
  email: '',
  password: '',
  role: null,
})

const roles = ref([])
const errors = ref({})

const fetchRoles = async () => {
  try {
    const response = await axios.get('/api/roles')

    roles.value = response.data
  } catch (error) {
    console.error('Error fetching roles:', error)
  }
}

const submit = async () => {
  try {
    await axios.post('/api/users', form.value)
    router.push('/users')
  } catch (error) {
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors
    } else {
      console.error('Error creating user:', error)
    }
  }
}

onMounted(() => {
  fetchRoles()
})
</script>

<template>
  <VCard title="Tambah Pengguna Baru">
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
              label="Password"
              type="password"
              :error-messages="errors.password"
              required
            />
          </VCol>
          <VCol
            cols="12"
            class="d-flex gap-4"
          >
            <VBtn type="submit">
              Simpan
            </VBtn>
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
