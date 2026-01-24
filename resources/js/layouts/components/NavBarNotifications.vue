<script setup>
import axios from '@/plugins/axios';
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';

const notifications = ref([]);
const unreadCount = ref(0);
const router = useRouter();

const fetchNotifications = async () => {
    try {
        const response = await axios.get('/api/notifications');
        notifications.value = response.data;
        unreadCount.value = notifications.value.filter(n => !n.read_at).length;
    } catch (error) {
        console.error('Error fetching notifications:', error);
    }
};

const markAsRead = async (notification) => {
    try {
        await axios.post('/api/notifications/read', { id: notification.id });
        
        // Update local state
        notification.read_at = new Date().toISOString();
        unreadCount.value = Math.max(0, unreadCount.value - 1);

        // Redirect if link exists
        if (notification.data.link) {
            router.push(notification.data.link);
        }
    } catch (error) {
        console.error('Error marking as read:', error);
    }
};

const markAllRead = async () => {
     try {
        await axios.post('/api/notifications/read');
        notifications.value.forEach(n => n.read_at = new Date().toISOString());
        unreadCount.value = 0;
    } catch (error) {
        console.error('Error marking all as read:', error);
    }
};

onMounted(() => {
    fetchNotifications();
    
    // Poll every 30 seconds for new notifications
    setInterval(fetchNotifications, 30000);
});
</script>

<template>
  <VMenu
    location="bottom right"
    :close-on-content-click="true"
  >
    <template #activator="{ props }">
      <VBtn
        v-bind="props"
        icon
        variant="text"
        color="default"
        class="me-2"
      >
        <VBadge
          v-if="unreadCount > 0"
          :content="unreadCount"
          color="error"
          offset-x="3"
          offset-y="3"
        >
          <VIcon icon="bx-bell" size="24" />
        </VBadge>
        <VIcon v-else icon="bx-bell" size="24" />
      </VBtn>
    </template>

    <VCard min-width="300" max-width="350">
        <VCardTitle class="d-flex justify-space-between align-center pa-4">
            <span class="text-h6">Notifications</span>
            <VBtn
                v-if="unreadCount > 0"
                variant="text"
                size="small"
                color="primary"
                @click.stop="markAllRead"
            >
                Mark all read
            </VBtn>
        </VCardTitle>
        
        <VDivider />

        <VList v-if="notifications.length > 0" lines="two" max-height="300" class="overflow-y-auto">
            <VListItem
                v-for="notification in notifications"
                :key="notification.id"
                :value="notification"
                @click="markAsRead(notification)"
                :class="{ 'bg-grey-100': !notification.read_at }"
            >
                <template #prepend>
                     <VAvatar color="primary" variant="tonal">
                         <VIcon icon="bx-money" v-if="notification.data.type === 'topup'" />
                         <VIcon icon="bx-bell" v-else />
                     </VAvatar>
                </template>
                
                <VListItemTitle class="font-weight-medium">
                    {{ notification.data.message }}
                </VListItemTitle>
                <VListItemSubtitle class="text-caption">
                    {{ new Date(notification.created_at).toLocaleString() }}
                </VListItemSubtitle>
            </VListItem>
        </VList>

        <div v-else class="pa-4 text-center text-medium-emphasis">
            No notifications
        </div>
    </VCard>
  </VMenu>
</template>
