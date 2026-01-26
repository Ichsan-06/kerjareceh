<script setup>
import logo from '@images/logo2.png'
import { useTheme } from 'vuetify'

const theme = useTheme()
const primaryColor = theme.current.value.colors.primary

const features = [
  {
    icon: 'bx-no-entry',
    title: 'Tanpa Modal',
    desc: 'Daftar & mulai kerja gratis. Gak ada biaya pendaftaran atau deposit.',
  },
  {
    icon: 'bx-check-shield',
    title: 'Saldo Aman',
    desc: 'Sistem Escrow menjamin kamu dibayar setiap tugas yang valid selesai.',
  },
  {
    icon: 'bx-time-five',
    title: 'Kerja Fleksibel',
    desc: 'Kerjakan kapan aja, di mana aja. Cuma modal HP & kuota.',
  },
  {
    icon: 'bx-wallet',
    title: 'Withdraw Cepat',
    desc: 'Tarik saldo ke e-wallet (DANA, OVO, Gopay) atau bank lokal.',
  },
]

const steps = [
  {
    id: 1,
    title: 'Daftar Akun',
    desc: 'Isi data diri singkat, verifikasi email, & langsung aktif.',
  },
  {
    id: 2,
    title: 'Pilih Tugas',
    desc: 'Cari tugas receh yang kamu suka (IG, YT, Telegram, dll).',
  },
  {
    id: 3,
    title: 'Kerjakan',
    desc: 'Ikuti instruksi, kerjakan tugas, & submit bukti (screenshot).',
  },
  {
    id: 4,
    title: 'Cuan Cair',
    desc: 'Tugas divalidasi, saldo langsung masuk dompet kamu!',
  },
]

const jobTypes = [
  { icon: 'bxl-instagram', title: 'Instagram', desc: 'Follow, Like, Comment' },
  { icon: 'bxl-youtube', title: 'YouTube', desc: 'Subscribe, Watch, Like' },
  { icon: 'bxl-telegram', title: 'Telegram', desc: 'Join Channel/Group' },
  { icon: 'bxl-twitter', title: 'Twitter/X', desc: 'Follow, Retweet, Like' },
  { icon: 'bxl-tiktok', title: 'TikTok', desc: 'Follow, Like, Share' },
  { icon: 'bx-star', title: 'Review', desc: 'Google Maps Review & App Store' },
]

const testimonials = [
  {
    name: 'Budi Santoso',
    role: 'Mahasiswa',
    text: 'Lumayan banget buat tambah uang jajan. Sehari bisa dapet 20-50rb cuma modal klik-klik doang pas lagi gabut.',
    avatar: 'https://i. Pravatar.cc/150?u=a042581f4e29026024d',
  },
  {
    name: 'Siti Aminah',
    role: 'Ibu Rumah Tangga',
    text: 'Sambil jaga anak bisa tetep produktif. Withdraw-nya juga gampang masuk ke DANA.',
    avatar: 'https://i.pravatar.cc/150?u=a042581f4e29026704d',
  },
  {
    name: 'Siti Aminah',
    role: 'Ibu Rumah Tangga',
    text: 'Sambil jaga anak bisa tetep produktif. Withdraw-nya juga gampang masuk ke DANA.',
    avatar: 'https://i.pravatar.cc/150?u=a042581f4e29026704d',
  },
]

const stats = ref({
  total_taskers: 0,
  total_tasks_completed: 0,
  total_payout: 0,
})

const formatCurrency = (value) => {
  if (value >= 1000000000) {
    return (value / 1000000000).toFixed(1) + 'M+'
  } else if (value >= 1000000) {
    return (value / 1000000).toFixed(0) + 'jt+'
  } else if (value >= 1000) {
    return (value / 1000).toFixed(0) + 'rb+'
  }
  return value
}

const formatNumber = (value) => {
    if (value >= 1000) {
        return (value / 1000).toFixed(1) + 'K+'
    }
    return value
}

const fetchStats = async () => {
  try {
    const { data } = await axios.get('/api/landing-stats')
    stats.value = data
  } catch (error) {
    console.error('Failed to fetch stats:', error)
  }
}

import axios from '@/plugins/axios'
import { onMounted } from 'vue'

onMounted(() => {
  fetchStats()
})

const faqs = [
  {
    question: 'Apakah daftarnya gratis?',
    answer: '100% Gratis! Tidak ada biaya pendaftaran sepeserpun. Cukup daftar pakai email dan langsung bisa kerja.',
  },
  {
    question: 'Berapa minimal penarikan saldo?',
    answer: 'Minimal withdraw cuma Rp 10.000 ke e-wallet (DANA, OVO, Gopay, ShopeePay) atau Rp 50.000 ke rekening bank.',
  },
  {
    question: 'Kapan tugas divalidasi?',
    answer: 'Validasi tugas maksimal 2x24 jam (hari kerja). Tapi biasanya lebih cepat, bahkan ada yang instan.',
  },
  {
    question: 'Apakah aman dan terpercaya?',
    answer: 'Tentu! Kami menggunakan sistem Escrow (Rekber) dimana saldo pemberi kerja dikunci dulu sebelum tugas dikerjakan, jadi pembayaran kamu terjamin.',
  }
]
</script>

<template>
  <div class="landing-page bg-surface">
    <!-- Navbar -->
    <VAppBar flat color="surface" class="px-md-10" height="80">
      <div class="d-flex align-center cursor-pointer" @click="$router.push('/')">
        <VImg :src="logo" width="40" class="me-3" />
        <h2 class="text-h4 font-weight-bold text-primary mb-0">CuanTask</h2>
      </div>
      <VSpacer />
      <div class="d-none d-md-flex gap-4 me-6">
        <VBtn variant="text" href="#cara-kerja">Cara Kerja</VBtn>
        <VBtn variant="text" href="#fitur">Keunggulan</VBtn>
        <VBtn variant="text" href="#testimoni">Kata Mereka</VBtn>
      </div>
      <VBtn variant="outlined" color="primary" class="me-3" to="/login">Masuk</VBtn>
      <VBtn color="primary" to="/register">Daftar</VBtn>
    </VAppBar>

    <!-- Hero Section -->
    <VContainer fluid class="hero-section py-16">
      <VContainer>
        <VRow align="center">
          <VCol cols="12" md="6">
            <VChip color="warning" class="mb-4 font-weight-bold" label>🚀 Platform Microtask No. #1 Indonesia</VChip>
            <h1 class="text-h2 font-weight-black mb-4 line-height-1-2">
              Kerja <span class="text-primary">Ringan</span>,<br>
              Cuan <span class="text-success">Harian</span>
            </h1>
            <p class="text-h6 text-medium-emphasis mb-8" style="line-height: 1.6;">
              Ambil tugas simpel, kerjain sebentar pake HP, saldo langsung masuk.
              Tanpa modal, tanpa ribet, tarik kapan aja!
            </p>
            <div class="d-flex gap-4 flex-wrap">
              <VBtn color="primary" size="x-large" to="/register" append-icon="bx-right-arrow-alt">
                Mulai Sekarang
              </VBtn>
              <VBtn variant="tonal" color="secondary" size="x-large" to="/about">
                Pelajari Dulu
              </VBtn>
            </div>
          </VCol>
          <VCol cols="12" md="6" class="text-center position-relative">
            <!-- Simple Illustration Placeholder -->
             <div class="d-flex justify-center align-center">
                <div class="hero-blob"></div>
                <!-- You can replace this VIcon with a proper Illustration VImg later -->
                <VIcon icon="bx-money-withdraw" size="200" color="primary" class="hero-icon" />
             </div>
          </VCol>
        </VRow>
      </VContainer>
    </VContainer>

    <!-- Stats Section -->
    <div class="bg-primary text-white py-8">
      <VContainer>
        <VRow justify="center" class="text-center">
            <VCol cols="4" md="3">
                <h3 class="text-h3 font-weight-bold">{{ formatNumber(stats.total_taskers) }}</h3>
                <p class="mb-0 text-white-50">Tasker Aktif</p>
            </VCol>
             <VCol cols="4" md="3">
                <h3 class="text-h3 font-weight-bold">{{ formatNumber(stats.total_tasks_completed) }}</h3>
                <p class="mb-0 text-white-50">Tugas Selesai</p>
            </VCol>
             <VCol cols="4" md="3">
                <h3 class="text-h3 font-weight-bold">IDR {{ formatCurrency(stats.total_payout) }}</h3>
                <p class="mb-0 text-white-50">Terbayarkan</p>
            </VCol>
        </VRow>
      </VContainer>
    </div>

    <!-- How It Works -->
    <VContainer id="cara-kerja" class="py-16">
      <div class="text-center mb-12">
        <h2 class="text-h3 font-weight-bold mb-2">Gampang Banget!</h2>
        <p class="text-body-1 text-medium-emphasis">Cuma butuh 4 langkah buat mulai ngehasilin duit.</p>
      </div>

      <VRow>
        <VCol v-for="step in steps" :key="step.id" cols="12" sm="6" md="3" class="text-center">
          <div class="step-card pa-6 rounded-xl hover-elevate">
            <div class="step-number bg-primary text-h4 font-weight-bold rounded-circle d-flex align-center justify-center mx-auto mb-4" style="width: 60px; height: 60px;">
              {{ step.id }}
            </div>
            <h3 class="text-h5 font-weight-bold mb-2">{{ step.title }}</h3>
            <p class="text-body-2 text-medium-emphasis mb-0">{{ step.desc }}</p>
          </div>
        </VCol>
      </VRow>
    </VContainer>

    <!-- Job Types -->
    <div class="bg-grey-100 py-16">
        <VContainer>
            <div class="text-center mb-12">
                <h2 class="text-h3 font-weight-bold mb-2">Jenis Pekerjaan</h2>
                <p class="text-body-1 text-medium-emphasis">Banyak pilihan tugas yang bisa kamu kerjakan.</p>
            </div>
             <VRow>
                <VCol v-for="job in jobTypes" :key="job.title" cols="6" md="2" class="text-center">
                    <VCard class="pa-4 rounded-lg h-100" flat hover>
                        <VIcon :icon="job.icon" size="40" color="primary" class="mb-3" />
                         <h4 class="text-subtitle-1 font-weight-bold">{{ job.title }}</h4>
                    </VCard>
                </VCol>
             </VRow>
        </VContainer>
    </div>

    <!-- FAQ Section -->
    <VContainer id="faq" class="py-16">
        <VRow justify="center">
            <VCol cols="12" md="8" class="text-center mb-8">
                <h2 class="text-h3 font-weight-bold mb-2">Sering Ditanyakan</h2>
                <p class="text-body-1 text-medium-emphasis">Jawaban untuk pertanyaan yang sering muncul.</p>
            </VCol>
        </VRow>
        <VRow justify="center">
            <VCol cols="12" md="8">
                <VExpansionPanels variant="accordion">
                    <VExpansionPanel
                        v-for="(faq, index) in faqs"
                        :key="index"
                        :title="faq.question"
                        :text="faq.answer"
                    />
                </VExpansionPanels>
            </VCol>
        </VRow>
    </VContainer>


    <!-- Features (Keunggulan) -->
    <VContainer id="fitur" class="py-16">
       <VRow align="center">
           <VCol cols="12" md="5">
               <h2 class="text-h3 font-weight-bold mb-6">Kenapa Pilih <span class="text-primary">CuanTask</span>?</h2>
               <p class="text-body-1 text-medium-emphasis mb-6">
                   Platform kami didesain khusus buat kamu yang pengen cari tambahan tanpa ribet.
                   Sistem kami transparan, adil, dan pastinya menguntungkan.
               </p>
               <VList lines="two" class="bg-transparent">
                   <VListItem v-for="feature in features" :key="feature.title" class="px-0">
                       <template #prepend>
                            <VAvatar color="primary" variant="tonal" rounded>
                                <VIcon :icon="feature.icon" />
                            </VAvatar>
                       </template>
                       <VListItemTitle class="font-weight-bold text-h6">{{ feature.title }}</VListItemTitle>
                       <VListItemSubtitle class="text-body-2">{{ feature.desc }}</VListItemSubtitle>
                   </VListItem>
               </VList>
           </VCol>
           <VCol cols="12" md="7" class="text-center">
                 <!-- Feature Illustration/Image placeholder -->
                <VCard border flat color="transparent" class="d-flex align-center justify-center" style="min-height: 400px;">
                    <VIcon icon="bx-trophy" size="200" color="warning" />
                </VCard>
           </VCol>
       </VRow>
    </VContainer>

    <!-- For Employers -->
    <!-- For Employers -->
    <div class="bg-primary py-16 position-relative overflow-hidden text-inverse-surface">
         <!-- Background decoration -->
         <div class="position-absolute top-0 right-0 mt-n16 mr-n16 rounded-circle bg-white opacity-10 d-none d-md-block" style="width: 600px; height: 600px; filter: blur(80px);"></div>
         <div class="position-absolute bottom-0 left-0 mb-n16 ml-n16 rounded-circle bg-warning opacity-10 d-none d-md-block" style="width: 400px; height: 400px; filter: blur(60px);"></div>

        <VContainer class="position-relative" style="z-index: 1;">
             <VRow align="center" justify="space-between">
                <VCol cols="12" md="5" order="2" order-md="1">
                     <VCard class="pa-6 rounded-xl border-dashed bg-white" theme="light" elevation="10" style="border-color: rgba(var(--v-theme-primary), 0.2) !important;">
                        <VRow dense>
                            <VCol cols="6">
                                <VCard class="d-flex flex-column align-center justify-center pa-4 bg-grey-50 rounded-xl text-center h-100 hover-elevate" flat border>
                                    <VAvatar color="primary" variant="tonal" size="56" class="mb-3">
                                        <VIcon icon="bx-target-lock" size="32" />
                                    </VAvatar>
                                    <h4 class="font-weight-bold text-subtitle-2 text-wrap">Target Tepat</h4>
                                </VCard>
                            </VCol>
                            <VCol cols="6">
                                <VCard class="d-flex flex-column align-center justify-center pa-4 bg-grey-50 rounded-xl text-center h-100 hover-elevate" flat border>
                                    <VAvatar color="success" variant="tonal" size="56" class="mb-3">
                                        <VIcon icon="bx-money" size="32" />
                                    </VAvatar>
                                    <h4 class="font-weight-bold text-subtitle-2 text-wrap">Hemat Budget</h4>
                                </VCard>
                            </VCol>
                            <VCol cols="6">
                                <VCard class="d-flex flex-column align-center justify-center pa-4 bg-grey-50 rounded-xl text-center h-100 hover-elevate" flat border>
                                    <VAvatar color="error" variant="tonal" size="56" class="mb-3">
                                        <VIcon icon="bx-rocket" size="32" />
                                    </VAvatar>
                                    <h4 class="font-weight-bold text-subtitle-2 text-wrap">Hasil Cepat</h4>
                                </VCard>
                            </VCol>
                             <VCol cols="6">
                                <VCard class="d-flex flex-column align-center justify-center pa-4 bg-grey-50 rounded-xl text-center h-100 hover-elevate" flat border>
                                    <VAvatar color="info" variant="tonal" size="56" class="mb-3">
                                        <VIcon icon="bx-support" size="32" />
                                    </VAvatar>
                                    <h4 class="font-weight-bold text-subtitle-2 text-wrap">Support 24/7</h4>
                                </VCard>
                            </VCol>
                        </VRow>
                     </VCard>
                </VCol>
                <VCol cols="12" md="6" order="1" order-md="2" class="text-white ps-md-10">
                    <div class="d-inline-flex align-center px-4 py-1 rounded-pill bg-white text-primary font-weight-bold mb-6 text-caption text-uppercase box-shadow-sm">
                        <VIcon icon="bx-briefcase" size="16" class="me-2" />
                        Untuk Bisnis & UMKM
                    </div>
                    <h2 class="text-h3 font-weight-black mb-6 line-height-1-2">
                        Butuh Bantuan <br>
                        <span class="text-warning">Promosi Digital?</span>
                    </h2>
                    <p class="text-h6 opacity-90 mb-8 font-weight-regular" style="line-height: 1.6;">
                        Tingkatkan <i>brand awareness</i>, followers, atau dapatkan review bintang 5 dari user riil di seluruh Indonesia.
                        Mulai kampanye pertamamu hanya dengan <b>Rp 500 per task!</b>
                    </p>
                    
                    <div class="d-flex flex-wrap gap-4">
                        <VBtn size="x-large" color="white" class="text-primary font-weight-bold px-8 shadow-lg" to="/register?role=employer" prepend-icon="bx-plus-circle">
                            Pasang Iklan
                        </VBtn>
                        <VBtn size="x-large" variant="outlined" color="white" class="px-6" append-icon="bx-right-arrow-alt">
                            Lihat Paket
                        </VBtn>
                    </div>
                </VCol>
             </VRow>
        </VContainer>
    </div>


    <!-- Testimonials -->
    <VContainer id="testimoni" class="py-16">
        <div class="text-center mb-12">
            <h2 class="text-h3 font-weight-bold mb-2">Kata Mereka</h2>
            <p class="text-body-1 text-medium-emphasis">Apa kata user yang udah nyobain CuanTask.</p>
        </div>
        <VRow justify="center">
            <VCol v-for="item in testimonials" :key="item.name" cols="12" md="5">
                <VCard class="pa-6 rounded-xl h-100" elevation="2">
                    <div class="d-flex align-center mb-4">
                        <!-- <VAvatar size="50" :image="item.avatar" class="me-4" /> -->
                         <VAvatar size="50" color="primary" variant="tonal" class="me-4">
                            {{ item.name.charAt(0) }}
                         </VAvatar>
                        <div>
                            <h4 class="text-h6 font-weight-bold">{{ item.name }}</h4>
                            <span class="text-caption text-medium-emphasis">{{ item.role }}</span>
                        </div>
                    </div>
                    <p class="text-body-1 fst-italic">"{{ item.text }}"</p>
                     <div class="d-flex text-warning">
                        <VIcon icon="bx-bxs-star" v-for="n in 5" :key="n" size="small" />
                    </div>
                </VCard>
            </VCol>
        </VRow>
    </VContainer>

    <!-- CTA Footer -->
    <div class="py-16 text-center">
         <VContainer>
             <VCard class="bg-primary text-white py-12 px-6 rounded-xl overflow-visible position-relative">
                 <div class="position-relative" style="z-index: 2;">
                     <h2 class="text-h3 font-weight-bold mb-4">Yuk, Mulai Dapetin Cuan Hari Ini!</h2>
                     <p class="text-h6 mb-8 opacity-90">Gabung bareng ribuan user lainnya yang udah cairin rupiah.</p>
                     <VBtn color="white" size="x-large" class="text-primary font-weight-bold px-8" to="/register">
                         Daftar Gratis Sekarang
                     </VBtn>
                 </div>

                 <!-- Decorative Elements -->
                 <div class="position-absolute top-0 right-0 mt-n8 mr-n8 d-none d-md-block">
                     <VIcon icon="bx-coin-stack" size="150" color="white" style="opacity: 0.2;" />
                 </div>
             </VCard>
         </VContainer>
    </div>

    <!-- Footer -->
    <VFooter class="bg-surface text-center d-flex flex-column py-6">
        <div class="d-flex align-center justify-center mb-4">
             <VImg :src="logo" width="30" class="me-2" />
             <span class="text-h6 font-weight-bold">CuanTask</span>
        </div>
         <div class="d-flex gap-4 mb-4 flex-wrap justify-center">
             <a href="#" class="text-decoration-none text-medium-emphasis">Tentang Kami</a>
             <a href="#" class="text-decoration-none text-medium-emphasis">Syarat & Ketentuan</a>
             <a href="#" class="text-decoration-none text-medium-emphasis">Kebijakan Privasi</a>
             <a href="#" class="text-decoration-none text-medium-emphasis">Kontak</a>
         </div>
         <p class="text-caption text-disabled mb-0">
             &copy; {{ new Date().getFullYear() }} CuanTask. All rights reserved. Made with ❤️ for Pejuang Receh.
         </p>
    </VFooter>

  </div>
</template>

<style scoped lang="scss">
.line-height-1-2 {
    line-height: 1.2;
}
.hero-blob {
    position: absolute;
    width: 300px;
    height: 300px;
    background: rgba(var(--v-theme-primary), 0.1);
    border-radius: 50%;
    filter: blur(50px);
    z-index: 0;
}
.hero-icon {
    position: relative;
    z-index: 1;
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
    100% { transform: translateY(0px); }
}

.hover-elevate {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    &:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
}
</style>
