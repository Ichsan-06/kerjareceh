<script setup>
import axios from '@/plugins/axios';
import { formatCurrency } from '@/utils/formatters';
import { onMounted, ref } from 'vue';

const leaderboard = ref([]);
const loading = ref(false);

const fetchLeaderboard = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/leaderboard');
        leaderboard.value = response.data;
    } catch (error) {
        console.error('Error fetching leaderboard:', error);
    } finally {
        loading.value = false;
    }
};

const resolveRankColor = (index) => {
    if (index === 0) return 'warning'; // Gold
    if (index === 1) return 'grey-lighten-1'; // Silver
    if (index === 2) return 'brown-lighten-1'; // Bronze
    return 'grey-lighten-3'; // Default
};

const resolveRankIcon = (index) => {
    if (index === 0) return 'bx-trophy';
    if (index === 1) return 'bx-medal';
    if (index === 2) return 'bx-award';
    return 'bx-hash';
};

onMounted(() => {
    fetchLeaderboard();
});
</script>

<template>
    <VContainer>
        <VRow justify="center">
            <VCol cols="12" md="8">
                <VCard class="mb-6">
                    <VCardItem class="text-center py-6">
                        <VAvatar color="primary" variant="tonal" size="56" class="mb-2">
                            <VIcon icon="bx-bar-chart-square" size="32" />
                        </VAvatar>
                        <h2 class="text-h4 font-weight-bold">Peringkat Penghasilan Tertinggi</h2>
                        <p class="text-medium-emphasis">Peringkat berdasarkan total penghasilan dari pekerjaan yang diselesaikan.</p>
                    </VCardItem>
                </VCard>

                <VCard>
                    <div v-if="loading" class="text-center py-6">
                        <VProgressCircular indeterminate color="primary" />
                    </div>
                    <VTable v-else>
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 80px;">Peringkat</th>
                                <th>Pengguna</th>
                                <th class="text-end">Total Cuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(user, index) in leaderboard" :key="user.id" :class="{'bg-yellow-lighten-5': index === 0}">
                                <td class="text-center">
                                    <VAvatar :color="resolveRankColor(index)" size="36" variant="tonal">
                                        <VIcon :icon="resolveRankIcon(index)" size="20" />
                                    </VAvatar>
                                </td>
                                <td>
                                    <div class="d-flex align-center">
                                        <VAvatar color="primary" size="32" variant="tonal" class="me-3">
                                            {{ user.name.charAt(0) }}
                                        </VAvatar>
                                        <div>
                                            <div class="font-weight-bold">{{ user.name }}</div>
                                            <div class="text-caption text-medium-emphasis">{{ user.email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end font-weight-bold text-success">
                                    {{ formatCurrency(user.total_earnings) }}
                                </td>
                            </tr>
                            <tr v-if="leaderboard.length === 0">
                                <td colspan="3" class="text-center text-medium-emphasis py-6">
                                    Belum ada data tersedia.
                                </td>
                            </tr>
                        </tbody>
                    </VTable>
                </VCard>
            </VCol>
        </VRow>
    </VContainer>
</template>
