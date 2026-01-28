<script setup>
import axios from '@/plugins/axios'
import logo from '@images/logo2.png'
import { default as authThemeMaskDark, default as authThemeMaskLight } from '@images/pages/1.png'
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useTheme } from 'vuetify'

const route = useRoute()
const router = useRouter()
const vuetifyTheme = useTheme()

const form = ref({
  email: route.query.email || '',
  token: route.query.token || '',
  password: '',
  password_confirmation: '',
})

const isPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)
const isSubmitting = ref(false)

const authThemeMask = computed(() => {
  return vuetifyTheme.global.name.value === 'light' ? authThemeMaskLight : authThemeMaskDark
})

const resetPassword = async () => {
  if (form.value.password !== form.value.password_confirmation) {
    alert('Konfirmasi kata sandi tidak cocok.')
    return
  }

  isSubmitting.value = true
  try {
    const response = await axios.post('/api/auth/password/reset', form.value)
    alert(response.data.message || 'Kata sandi Anda telah berhasil direset.')
    router.push('/login')
  } catch (error) {
    console.error('Reset password failed:', error)
    alert(error.response?.data?.message || 'Gagal mereset kata sandi. Silakan periksa kembali tautan Anda.')
  } finally {
    isSubmitting.value = false
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
            Reset Kata Sandi 🔒
          </h5>
          <p class="mb-0">
            Silakan masukkan kata sandi baru Anda untuk akun <span class="font-weight-bold">{{ form.email }}</span>
          </p>
        </VCardText>

        <VCardText>
          <VForm @submit.prevent="resetPassword">
            <VRow>
              <!-- email (hidden but visible for context) -->
              <VCol cols="12">
                <VTextField
                  v-model="form.email"
                  label="Email"
                  type="email"
                  readonly
                  disabled
                />
              </VCol>

              <!-- password -->
              <VCol cols="12">
                <VTextField
                  v-model="form.password"
                  name="password"
                  id="password"
                  label="Kata Sandi Baru"
                  placeholder="············"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  autocomplete="new-password"
                  :append-inner-icon="isPasswordVisible ? 'bx-hide' : 'bx-show'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />
              </VCol>

              <!-- confirm password -->
              <VCol cols="12">
                <VTextField
                  v-model="form.password_confirmation"
                  name="password_confirmation"
                  id="password_confirmation"
                  label="Konfirmasi Kata Sandi Baru"
                  placeholder="············"
                  :type="isConfirmPasswordVisible ? 'text' : 'password'"
                  autocomplete="new-password"
                  :append-inner-icon="isConfirmPasswordVisible ? 'bx-hide' : 'bx-show'"
                  @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
                />
              </VCol>

              <!-- reset button -->
              <VCol cols="12">
                <VBtn
                  block
                  type="submit"
                  size="large"
                  :loading="isSubmitting"
                  :disabled="!form.password || !form.password_confirmation"
                >
                  Reset Kata Sandi
                </VBtn>
              </VCol>

              <VCol
                cols="12"
                class="text-center"
              >
                <RouterLink
                  class="text-primary"
                  to="/login"
                >
                  <VIcon
                    icon="bx-chevron-left"
                    class="flip-in-rtl"
                  />
                  <span>Kembali ke Masuk</span>
                </RouterLink>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
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
