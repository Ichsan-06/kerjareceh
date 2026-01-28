<script setup>
import axios from '@/plugins/axios'
import { onMounted, ref } from 'vue'

const roles = ref([])
const permissions = ref({})
const isDialogVisible = ref(false)
const isEditing = ref(false)
const currentRole = ref({ name: '', permissions: [] })
const valid = ref(false)

const headers = [
  { title: 'Role Name', key: 'name' },
  { title: 'Permissions', key: 'permissions', sortable: false },
  { title: 'Actions', key: 'actions', sortable: false },
]

const fetchRoles = async () => {
  try {
    const response = await axios.get('/api/roles')

    roles.value = response.data
  } catch (error) {
    console.error('Error fetching roles:', error)
  }
}

const fetchPermissions = async () => {
  try {
    const response = await axios.get('/api/roles/permissions')

    permissions.value = response.data
  } catch (error) {
    console.error('Error fetching permissions:', error)
  }
}

const openDialog = (role = null) => {
  if (role) {
    isEditing.value = true
    currentRole.value = {
      id: role.id,
      name: role.name,
      permissions: role.permissions.map(p => p.name),
    }
  } else {
    isEditing.value = false
    currentRole.value = { name: '', permissions: [] }
  }
  isDialogVisible.value = true
}

const saveRole = async () => {
  try {
    if (isEditing.value) {
      await axios.put(`/api/roles/${currentRole.value.id}`, currentRole.value)
    } else {
      await axios.post('/api/roles', currentRole.value)
    }
    isDialogVisible.value = false
    fetchRoles()
  } catch (error) {
    console.error('Error saving role:', error)
    alert('Failed to save role')
  }
}

const deleteRole = async id => {
  if (!confirm('Are you sure you want to delete this role?')) return
  try {
    await axios.delete(`/api/roles/${id}`)
    fetchRoles()
  } catch (error) {
    console.error('Error deleting role:', error)
  }
}

onMounted(() => {
  fetchRoles()
  fetchPermissions()
})
</script>

<template>
  <div>
    <VCard title="Roles & Permissions">
      <VCardText>
        <VBtn
          color="primary"
          @click="openDialog"
        >
          Add Role
        </VBtn>
      </VCardText>

      <VDataTable
        :headers="headers"
        :items="roles"
        class="text-no-wrap"
      >
        <template #item.permissions="{ item }">
          <VChip
            v-for="permission in item.permissions"
            :key="permission.id"
            size="small"
            class="ma-1"
          >
            {{ permission.name }}
          </VChip>
        </template>
        <template #item.actions="{ item }">
          <VBtn
            icon
            size="small"
            variant="text"
            color="primary"
            @click="openDialog(item)"
          >
            <VIcon icon="bx-edit" />
          </VBtn>
          <VBtn
            icon
            size="small"
            variant="text"
            color="error"
            @click="deleteRole(item.id)"
          >
            <VIcon icon="bx-trash" />
          </VBtn>
        </template>
      </VDataTable>
    </VCard>

    <VDialog
      v-model="isDialogVisible"
      max-width="800"
    >
      <VCard>
        <VCardTitle>{{ isEditing ? 'Edit Role' : 'Add Role' }}</VCardTitle>
        <VCardText>
          <VForm
            v-model="valid"
            @submit.prevent="saveRole"
          >
            <VTextField
              v-model="currentRole.name"
              label="Role Name"
              required
              class="mb-4"
            />

            <VLabel class="mb-2">
              Permissions
            </VLabel>

            <div
              v-for="(perms, group) in permissions"
              :key="group"
              class="mb-4"
            >
              <h4 class="text-subtitle-1 text-uppercase text-medium-emphasis mb-2">
                {{ group }}
              </h4>
              <VRow>
                <VCol
                  v-for="perm in perms"
                  :key="perm.id"
                  cols="12"
                  sm="6"
                  md="4"
                >
                  <VCheckbox
                    v-model="currentRole.permissions"
                    :label="perm.name"
                    :value="perm.name"
                    density="compact"
                    hide-details
                  />
                </VCol>
              </VRow>
              <VDivider class="my-2" />
            </div>
          </VForm>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn
            variant="text"
            @click="isDialogVisible = false"
          >
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            @click="saveRole"
          >
            Save
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
