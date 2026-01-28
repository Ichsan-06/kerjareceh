<script setup>
import axios from '@/plugins/axios'
import logo from '@images/logo2.png'
import { default as authThemeMaskDark, default as authThemeMaskLight } from '@images/pages/1.png'
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useTheme } from 'vuetify'

const form = ref({
  username: '',
  email: '',
  password: '',
  privacyPolicies: false,
})

const isPasswordVisible = ref(false)
const router = useRouter()
const vuetifyTheme = useTheme()

const authThemeMask = computed(() => {
  return vuetifyTheme.global.name.value === 'light' ? authThemeMaskLight : authThemeMaskDark
})

const register = async () => {
  try {
    const response = await axios.post('/api/auth/register', form.value)

    localStorage.setItem('token', response.data.access_token)
    localStorage.setItem('user', JSON.stringify(response.data.user))
    localStorage.setItem('permissions', JSON.stringify(response.data.permissions))
    
    // Redirect to dashboard or home
    router.push('/dashboard')
  } catch (error) {
    console.error('Registration failed:', error)


    // Handle validation errors if present
    const message = error.response?.data?.message || 'Registration failed. Please try again.'

    alert(message)
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
            Petualangan dimulai di sini 🚀
          </h5>
          <p class="mb-0">
            Jadikan manajemen aplikasi Anda mudah dan menyenangkan!
          </p>
        </VCardText>

        <VCardText>
          <VForm @submit.prevent="register">
            <VRow>
              <!-- Username -->
              <VCol cols="12">
                <VTextField
                  v-model="form.username"
                  autofocus
                  label="Username"
                  placeholder="Johndoe"
                />
              </VCol>
              <!-- email -->
              <VCol cols="12">
                <VTextField
                  v-model="form.email"
                  label="Email"
                  type="email"
                  placeholder="johndoe@email.com"
                />
              </VCol>

              <!-- password -->
              <VCol cols="12">
                <VTextField
                  v-model="form.password"
                  label="Kata Sandi"
                  autocomplete="password"
                  placeholder="············"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isPasswordVisible ? 'bx-hide' : 'bx-show'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />

                <div class="d-flex align-center my-6">
                  <VCheckbox
                    id="privacy-policy"
                    v-model="form.privacyPolicies"
                    inline
                  />
                  <VLabel
                    for="privacy-policy"
                    style="opacity: 1;"
                  >
                    <span class="me-1 text-high-emphasis">Saya setuju dengan</span>
                    <a
                      href="javascript:void(0)"
                      class="text-primary"
                    >kebijakan privasi & ketentuan</a>
                  </VLabel>
                </div>

                <VBtn
                  block
                  type="submit"
                  size="large"
                >
                  Daftar
                </VBtn>
              </VCol>

              <!-- login instead -->
              <VCol
                cols="12"
                class="text-center text-base"
              >
                <span>Sudah punya akun?</span>
                <RouterLink
                  class="text-primary ms-1"
                  to="/login"
                >
                  Masuk di sini
                </RouterLink>
              </VCol>

              <!--
                <VCol
                cols="12"
                class="d-flex align-center"
                >
                <VDivider />
                <span class="mx-4">or</span>
                <VDivider />
                </VCol> 
              -->

              <!-- auth providers -->
              <!--
                <VCol
                cols="12"
                class="text-center"
                >
                <AuthProvider />
                </VCol> 
              -->
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
