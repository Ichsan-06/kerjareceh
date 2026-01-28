<script setup>
import axios from '@/plugins/axios'
import avatar1 from '@images/avatars/avatar-1.png'

const accountDataLocal = ref({
  avatarImg: avatar1,
  firstName: '',
  lastName: '',
  email: '',
  org: '',
  phone: '',
  address: '',
  state: '',
  zip: '',
  country: '',
  language: '',
  timezone: '',
  currency: '',
})

const isAccountDeactivated = ref(false)
const refInputEl = ref()
const avatarFile = ref(null)
const successMessage = ref('')
const errorMessage = ref('')

// Fetch User Data
const fetchUser = async () => {
  try {
    const response = await axios.get('/api/user')
    const user = response.data

    accountDataLocal.value.firstName = user.name // Simulating First/Last name split or just one field
    accountDataLocal.value.email = user.email
    if (user.avatar) {
      // accountDataLocal.value.avatarImg = '/storage/' + user.avatar; // Adjust based on storage
      // Or if using full URL
      accountDataLocal.value.avatarImg = user.avatar_url || avatar1 
    }
  } catch (error) {
    console.error('Error fetching user:', error)
  }
}

onMounted(() => {
  fetchUser()
})

const resetForm = () => {
  fetchUser()
}

const changeAvatar = file => {
  const fileReader = new FileReader()
  const { files } = file.target
  if (files && files.length) {
    avatarFile.value = files[0]
    fileReader.readAsDataURL(files[0])
    fileReader.onload = () => {
      if (typeof fileReader.result === 'string')
        accountDataLocal.value.avatarImg = fileReader.result
    }
  }
}

// reset avatar image
const resetAvatar = () => {
  accountDataLocal.value.avatarImg = avatar1
  avatarFile.value = null
}

const submitProfile = async () => {
  const formData = new FormData()

  formData.append('name', accountDataLocal.value.firstName) // Using firstName as Full Name for now as DB has 'name'
  formData.append('email', accountDataLocal.value.email)
    
  if (avatarFile.value) {
    formData.append('avatar', avatarFile.value)
  }

  try {
    const response = await axios.post('/api/profile', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    successMessage.value = 'Profile updated successfully!'
    errorMessage.value = ''
  } catch (error) {
    console.error(error)
    errorMessage.value = error.response?.data?.message || 'Failed to update profile.'
    successMessage.value = ''
  }
}
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard title="Account Details">
        <VCardText class="d-flex">
          <!-- 👉 Avatar -->
          <VAvatar
            rounded="lg"
            size="100"
            class="me-6"
            :image="accountDataLocal.avatarImg"
          />

          <!-- 👉 Upload Photo -->
          <form class="d-flex flex-column justify-center gap-5">
            <div class="d-flex flex-wrap gap-2">
              <VBtn
                color="primary"
                @click="refInputEl?.click()"
              >
                <VIcon
                  icon="bx-cloud-upload"
                  class="d-sm-none"
                />
                <span class="d-none d-sm-block">Upload new photo</span>
              </VBtn>

              <input
                ref="refInputEl"
                type="file"
                name="file"
                accept=".jpeg,.png,.jpg,GIF"
                hidden
                @input="changeAvatar"
              >

              <VBtn
                type="reset"
                color="error"
                variant="tonal"
                @click="resetAvatar"
              >
                <span class="d-none d-sm-block">Reset</span>
                <VIcon
                  icon="bx-refresh"
                  class="d-sm-none"
                />
              </VBtn>
            </div>

            <p class="text-body-1 mb-0">
              Allowed JPG, GIF or PNG. Max size of 800K
            </p>
          </form>
        </VCardText>

        <VDivider />

        <VCardText>
          <VAlert
            v-if="successMessage"
            type="success"
            class="mb-4"
          >
            {{ successMessage }}
          </VAlert>
          <VAlert
            v-if="errorMessage"
            type="error"
            class="mb-4"
          >
            {{ errorMessage }}
          </VAlert>
          
          <!-- 👉 Form -->
          <VForm
            class="mt-6"
            @submit.prevent="submitProfile"
          >
            <VRow>
              <!-- 👉 First Name -->
              <VCol cols="12">
                <VTextField
                  v-model="accountDataLocal.firstName"
                  label="Full Name"
                />
              </VCol>

              <!-- 👉 Email -->
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="accountDataLocal.email"
                  label="E-mail"
                  type="email"
                />
              </VCol>

              <!-- 👉 Form Actions -->
              <VCol
                cols="12"
                class="d-flex flex-wrap gap-4"
              >
                <VBtn type="submit">
                  Save changes
                </VBtn>

                <VBtn
                  color="secondary"
                  variant="tonal"
                  type="reset"
                  @click.prevent="resetForm"
                >
                  Reset
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
