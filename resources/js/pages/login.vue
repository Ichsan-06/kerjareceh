<script setup>
import axios from '@/plugins/axios'
import logo from '@images/logo2.png'
import { default as authThemeMaskDark, default as authThemeMaskLight } from '@images/pages/1.png'
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useTheme } from 'vuetify'

const form = ref({
  email: '',
  password: '',
  remember: false,
})

const isPasswordVisible = ref(false)
const router = useRouter()
const vuetifyTheme = useTheme()

const authThemeMask = computed(() => {
  return vuetifyTheme.global.name.value === 'light' ? authThemeMaskLight : authThemeMaskDark
})

const login = async () => {
  try {
    const response = await axios.post('/api/auth/login', form.value)

    localStorage.setItem('token', response.data.access_token)
    localStorage.setItem('user', JSON.stringify(response.data.user))
    localStorage.setItem('permissions', JSON.stringify(response.data.permissions))
    router.push('/dashboard')
  } catch (error) {
    console.error('Login failed:', error)
    alert('Login gagal. Silakan periksa kredensial Anda.')
  }
}

const isForgotPasswordDialogVisible = ref(false)
const forgotPasswordEmail = ref('')
const isSendingResetLink = ref(false)

const sendResetLink = async () => {
  if (!forgotPasswordEmail.value) return

  isSendingResetLink.value = true
  try {
    const response = await axios.post('/api/auth/password/email', { email: forgotPasswordEmail.value })
    alert(response.data.message || 'Tautan reset kata sandi telah dikirim ke email Anda.')
    isForgotPasswordDialogVisible.value = false
    forgotPasswordEmail.value = ''
  } catch (error) {
    console.error('Failed to send reset link:', error)
    alert(error.response?.data?.message || 'Gagal mengirim tautan reset kata sandi. Silakan coba lagi.')
  } finally {
    isSendingResetLink.value = false
  }
}
</script>

<template>
  <VRow
    no-gutters
    class="auth-wrapper bg-surface"
  >
    <VCol
      lg="8"
      class="d-none d-lg-flex"
    >
      <div class="position-relative auth-bg rounded-lg w-100 ma-8 me-0">
        <div class="d-flex align-center justify-center w-100 h-100">
          <VImg
            max-width="505"
            :src="authThemeMask"
            class="auth-illustration mt-16 mb-2"
          />
        </div>

        <VImg
          :src="logo"
          alt="logo"
          width="150"
          class="auth-logo d-none d-lg-block"
          style="position: absolute; top: 2rem; left: 2rem; z-index: 1;"
        />
      </div>
    </VCol>

    <VCol
      cols="12"
      lg="4"
      class="auth-card-v2 d-flex align-center justify-center"
    >
      <VCard
        flat
        :max-width="500"
        class="mt-12 mt-sm-0 pa-4"
      >
        <VCardText>
          <div class="d-flex d-lg-none align-center justify-center mb-6">
            <VImg
              :src="logo"
              alt="logo"
              width="120"
            />
          </div>

          <h5 class="text-h5 mb-1">
            Selamat Datang di <span class="text-primary">KerjaReceh</span>! 👋🏻
          </h5>
          <p class="mb-0">
            Silakan masuk ke akun Anda dan mulai petualangan
          </p>
        </VCardText>

        <VCardText>
          <VForm @submit.prevent="login">
            <VRow>
              <!-- email -->
              <VCol cols="12">
                <VTextField
                  v-model="form.email"
                  autofocus
                  label="Email atau Username"
                  type="email"
                  placeholder="johndoe@email.com"
                />
              </VCol>

              <!-- password -->
              <VCol cols="12">
                <VTextField
                  v-model="form.password"
                  label="Kata Sandi"
                  placeholder="············"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  autocomplete="password"
                  :append-inner-icon="isPasswordVisible ? 'bx-hide' : 'bx-show'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />

                <!-- remember me checkbox -->
                <div class="d-flex align-center justify-space-between flex-wrap my-6">
                  <VCheckbox
                    v-model="form.remember"
                    label="Ingat saya"
                  />

                  <a
                    class="text-primary"
                    href="javascript:void(0)"
                    @click="isForgotPasswordDialogVisible = true"
                  >
                    Lupa Kata Sandi?
                  </a>
                </div>

                <!-- login button -->
                <VBtn
                  block
                  type="submit"
                  size="large"
                >
                  Masuk
                </VBtn>
                
                <p class="text-center mt-5">
                  <span>New on our platform? </span>
                  <RouterLink
                    class="text-primary"
                    to="/register"
                  >
                    Create an account
                  </RouterLink>
                </p>
                <div class="text-center mt-2">
                  <RouterLink
                    class="text-secondary text-caption"
                    to="/"
                  >
                    <VIcon
                      icon="bx-arrow-back"
                      start
                      size="small"
                    />
                    Kembali ke Beranda
                  </RouterLink>
                </div>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>

  <!-- Forgot Password Dialog -->
  <VDialog
    v-model="isForgotPasswordDialogVisible"
    max-width="500"
  >
    <VCard title="Lupa Kata Sandi">
      <VCardText>
        Silakan masukkan alamat email Anda dan kami akan mengirimkan tautan untuk mereset kata sandi Anda.
      </VCardText>

      <VCardText>
        <VTextField
          v-model="forgotPasswordEmail"
          label="Email"
          placeholder="johndoe@email.com"
          type="email"
          autofocus
          @keyup.enter="sendResetLink"
        />
      </VCardText>

      <VCardActions>
        <VSpacer />
        <VBtn
          color="secondary"
          variant="tonal"
          @click="isForgotPasswordDialogVisible = false"
        >
          Batal
        </VBtn>
        <VBtn
          color="primary"
          variant="elevated"
          :loading="isSendingResetLink"
          :disabled="!forgotPasswordEmail"
          @click="sendResetLink"
        >
          Kirim Tautan
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth.scss";

.auth-wrapper {
  min-height: 100vh;
}

.auth-bg {
  background-color: rgb(var(--v-theme-background));
}
</style>
