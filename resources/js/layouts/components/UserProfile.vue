<script setup>
import UserAvatar from '@/components/UserAvatar.vue'
import { useThemeSwitcher } from '@/hooks/layout/useThemeSwitcher'

const props = defineProps({
  // 'navbar': solo el avatar (barra superior, únicamente en el panel
  // principal). 'sidebar': avatar + nombre, fila completa clickeable (pie
  // del menú lateral, visible en el resto de vistas — ver
  // DefaultLayoutWithVerticalNav.vue).
  variant: {
    type: String,
    default: 'navbar',
  },
})

const { themeName, changeTheme } = useThemeSwitcher()

// TODO: reemplazar por el usuario autenticado cuando exista el sistema de auth.
const fullName = 'Usuario'
</script>

<template>
  <div
    class="relative flex items-center cursor-pointer"
    :class="variant === 'sidebar' ? 'gap-3 w-full rounded-lg px-2 py-2 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors' : ''"
  >
    <UserAvatar
      variant="tonal"
      :name="fullName"
    />

    <div
      v-if="variant === 'sidebar'"
      class="flex flex-col min-w-0"
    >
      <span class="text-sm font-semibold truncate">{{ fullName }}</span>
    </div>

    <!-- SECTION Menu -->
    <VMenu
      activator="parent"
      width="230"
      :location="variant === 'sidebar' ? 'top end' : 'bottom end'"
      offset="14px"
    >
      <VList>
        <template v-if="variant === 'navbar'">
          <VListItem>
            <template #prepend>
              <VListItemAction start>
                <UserAvatar
                  variant="tonal"
                  :name="fullName"
                />
              </VListItemAction>
            </template>

            <VListItemTitle class="font-weight-semibold">
              {{ fullName }}
            </VListItemTitle>
          </VListItem>
          <VDivider class="my-2" />
        </template>

        <!-- 👉 Modo oscuro / claro -->
        <VListItem
          link
          @click="changeTheme"
        >
          <template #prepend>
            <VIcon
              class="me-2"
              :icon="themeName === 'dark' ? 'bx-sun' : 'bx-moon'"
              size="22"
            />
          </template>
          <VListItemTitle>Modo {{ themeName === 'dark' ? 'claro' : 'oscuro' }}</VListItemTitle>
        </VListItem>
      </VList>
    </VMenu>
    <!-- !SECTION -->
  </div>
</template>
