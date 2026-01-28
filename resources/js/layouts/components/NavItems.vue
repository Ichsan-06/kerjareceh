<script setup>
import { can } from '@/utils/permissions'
import VerticalNavLink from '@layouts/components/VerticalNavLink.vue'
import VerticalNavSectionTitle from '@layouts/components/VerticalNavSectionTitle.vue'
</script>

<template>
  <!-- 👉 Dashboard -->
  <VerticalNavLink
    :item="{
      title: 'Dashboard',
      icon: 'bx-home-smile',
      to: '/dashboard',
    }"
  />

  <!-- 👉 Front Pages -->
  <!--
    <VerticalNavGroup
    :item="{
    title: 'Front Pages',
    icon: 'bx-file',
    badgeContent: 'Pro',
    badgeClass: 'bg-light-primary text-primary',
    }"
    >
    <VerticalNavLink
    :item="{
    title: 'Landing',
    href: 'https://demos.themeselection.com/sneat-vuetify-vuejs-laravel-admin-template/demo-1/front-pages/landing-page',
    target: '_blank',
    }"
    />
    <VerticalNavLink
    :item="{
    title: 'Pricing',
    href: 'https://demos.themeselection.com/sneat-vuetify-vuejs-laravel-admin-template/demo-1/front-pages/pricing',
    target: '_blank',
    }"
    />
    <VerticalNavLink
    :item="{
    title: 'Payment',
    href: 'https://demos.themeselection.com/sneat-vuetify-vuejs-laravel-admin-template/demo-1/front-pages/payment',
    target: '_blank',
    }"
    />
    <VerticalNavLink
    :item="{
    title: 'Checkout',
    href: 'https://demos.themeselection.com/sneat-vuetify-vuejs-laravel-admin-template/demo-1/front-pages/checkout',
    target: '_blank',
    }"
    />
    <VerticalNavLink
    :item="{
    title: 'Help Center',
    href: 'https://demos.themeselection.com/sneat-vuetify-vuejs-laravel-admin-template/demo-1/front-pages/help-center',
    target: '_blank',
    }"
    />
    </VerticalNavGroup> 
  -->

  <!-- 👉 Apps & Pages -->
  <!--
    <VerticalNavSectionTitle
    :item="{
    heading: 'Apps & Pages',
    }"
    />
    <VerticalNavLink
    :item="{
    title: 'Email',
    icon: 'bx-envelope',
    href: 'https://demos.themeselection.com/sneat-vuetify-vuejs-laravel-admin-template/demo-1/apps/email',
    target: '_blank',
    badgeContent: 'Pro',
    badgeClass: 'bg-light-primary text-primary',
    }"
    />
    <VerticalNavLink
    :item="{
    title: 'Chat',
    icon: 'bx-chat',
    href: 'https://demos.themeselection.com/sneat-vuetify-vuejs-laravel-admin-template/demo-1/apps/chat',
    target: '_blank',
    badgeContent: 'Pro',
    badgeClass: 'bg-light-primary text-primary',
    }"
    />
    <VerticalNavLink
    :item="{
    title: 'Calendar',
    icon: 'bx-calendar',
    href: 'https://demos.themeselection.com/sneat-vuetify-vuejs-laravel-admin-template/demo-1/apps/calendar',
    target: '_blank',
    badgeContent: 'Pro',
    badgeClass: 'bg-light-primary text-primary',
    }"
    />
    <VerticalNavLink
    :item="{
    title: 'Kanban',
    icon: 'bx-grid',
    href: 'https://demos.themeselection.com/sneat-vuetify-vuejs-laravel-admin-template/demo-1/apps/kanban',
    target: '_blank',
    badgeContent: 'Pro',
    badgeClass: 'bg-light-primary text-primary',
    }"
    />

    <VerticalNavLink
    :item="{
    title: 'Account Settings',
    icon: 'bx-user',
    to: '/account-settings',
    }"
    />

    <VerticalNavLink
    :item="{
    title: 'Login',
    icon: 'bx-log-in',
    to: '/login',
    }"
    />
    <VerticalNavLink
    :item="{
    title: 'Register',
    icon: 'bx-user-plus',
    to: '/register',
    }"
    />
    <VerticalNavLink
    :item="{
    title: 'Error',
    icon: 'bx-info-circle',
    to: '/no-existence',
    }"
    /> 
  -->

  <!-- 👉 User Interface -->
  <!--
    <VerticalNavSectionTitle
    :item="{
    heading: 'User Interface',
    }"
    />
    <VerticalNavLink
    :item="{
    title: 'Typography',
    icon: 'bx-text',
    to: '/typography',
    }"
    />
    <VerticalNavLink
    :item="{
    title: 'Icons',
    icon: 'bx-package',
    to: '/icons',
    }"
    />
    <VerticalNavLink
    :item="{
    title: 'Cards',
    icon: 'bx-credit-card',
    to: '/cards',
    }"
    /> 
  -->

  <!-- 👉 Users & Roles -->
  <VerticalNavSectionTitle
    v-if="can('read user') || can('read role')"
    :item="{
      heading: 'Pengguna & Peran',
    }"
  />

  <VerticalNavLink
    v-if="can('read user')"
    :item="{
      title: 'Pengguna',
      icon: 'bx-group',
      to: '/users',
    }"
  />

  <VerticalNavLink
    v-if="can('read role')"
    :item="{
      title: 'Peran & Izin',
      icon: 'bx-shield',
      to: '/roles',
    }"
  />

  <!-- 👉 Main Apps -->
  <VerticalNavSectionTitle
    :item="{
      heading: 'Menu Utama',
    }"
  />

  <VerticalNavLink
    v-if="can('read job')"
    :item="{
      title: 'Bursa Kerja',
      icon: 'bx-store',
      to: '/jobs',
    }"
  />

  <VerticalNavLink
    v-if="can('read leaderboard')"
    :item="{
      title: 'Peringkat',
      icon: 'bx-chart',
      to: '/leaderboard',
    }"
  />

  <!-- 👉 Personal Activity -->
  <VerticalNavSectionTitle
    v-if="can('read posted_jobs') || can('read my_jobs') || can('read wallet')" 
    :item="{
      heading: 'Aktivitas Saya',
    }"
  />

  <VerticalNavLink
    v-if="can('read posted_jobs')"
    :item="{
      title: 'Pekerjaan Saya',
      icon: 'bx-list-plus',
      to: '/posted-jobs',
    }"
  />

  <VerticalNavLink
    v-if="can('read my_jobs')"
    :item="{
      title: 'Pekerjaan Diambil',
      icon: 'bx-task',
      to: '/my-jobs',
    }"
  />

  <VerticalNavLink
    v-if="can('read wallet')"
    :item="{
      title: 'Dompet Saya',
      icon: 'bx-wallet',
      to: '/wallet',
    }"
  />

  <VerticalNavLink
    v-if="can('create topup')"
    :item="{
      title: 'Top Up Saldo',
      icon: 'bx-money',
      to: '/topup',
    }"
  />

  <VerticalNavLink
    v-if="can('read admin_topup')"
    :item="{
      title: 'Verifikasi Top Up',
      icon: 'bx-check-shield',
      to: '/admin/topup',
    }"
  />

  <VerticalNavLink
    v-if="can('create withdraw')"
    :item="{
      title: 'Pencairan Saldo',
      icon: 'bx-money-withdraw',
      to: '/withdraw',
    }"
  />

  <VerticalNavLink
    v-if="can('read admin_withdraw')"
    :item="{
      title: 'Verifikasi Penarikan',
      icon: 'bx-shield-quarter',
      to: '/admin/withdraw',
    }"
  />

  <VerticalNavLink
    v-if="can('read feedback')"
    :item="{
      title: 'Feedback',
      icon: 'bx-message-square-dots',
      to: '/feedback',
    }"
  />

  <VerticalNavLink
    :item="{
      title: 'Traktir Admin',
      icon: 'bx-coffee-togo',
      to: '/traktir',
    }"
  />

  <!-- 👉 Forms & Tables -->
  <!--
    <VerticalNavSectionTitle
    :item="{
    heading: 'Forms & Tables',
    }"
    />
    <VerticalNavLink
    :item="{
    title: 'Form Layouts',
    icon: 'bx-layout',
    to: '/form-layouts',
    }"
    />
    <VerticalNavLink
    :item="{
    title: 'Form Validation',
    icon: 'bx-check-circle',
    href: 'https://demos.themeselection.com/sneat-vuetify-vuejs-laravel-admin-template/demo-1/forms/form-validation',
    target: '_blank',
    badgeContent: 'Pro',
    badgeClass: 'bg-light-primary text-primary',
    }"
    />
    <VerticalNavLink
    :item="{
    title: 'Form Wizard',
    icon: 'bx-align-middle',
    href: 'https://demos.themeselection.com/sneat-vuetify-vuejs-laravel-admin-template/demo-1/forms/form-wizard-numbered',
    target: '_blank',
    badgeContent: 'Pro',
    badgeClass: 'bg-light-primary text-primary',
    }"
    />
    <VerticalNavLink
    :item="{
    title: 'Tables',
    icon: 'bx-table',
    to: '/tables',
    }"
    /> 
  -->

  <!-- 👉 Others -->
  <!--
    <VerticalNavSectionTitle
    :item="{
    heading: 'Others',
    }"
    />
    <VerticalNavLink
    :item="{
    title: 'Access Control',
    icon: 'bx-command',
    href: 'https://demos.themeselection.com/sneat-vuetify-vuejs-laravel-admin-template/demo-1/access-control',
    target: '_blank',
    badgeContent: 'Pro',
    badgeClass: 'bg-light-primary text-primary',
    }"
    />
    <VerticalNavLink
    :item="{
    title: 'Documentation',
    icon: 'bx-file',
    href: 'https://demos.themeselection.com/sneat-vuetify-vuejs-admin-template/documentation/guide/laravel-integration/folder-structure.html',
    target: '_blank',
    }"
    />
    <VerticalNavLink
    :item="{
    title: 'Raise Support',
    href: 'https://github.com/themeselection/sneat-vuetify-vuejs-laravel-admin-template-free/issues',
    icon: 'bx-phone',
    target: '_blank',
    }"
    /> 
  -->
</template>
