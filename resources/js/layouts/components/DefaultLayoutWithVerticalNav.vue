<script setup>
import Footer from '@/layouts/components/Footer.vue'
import NavItems from '@/layouts/components/NavItems/NavItems.vue'
import { useThemeSwitcher } from '@/hooks/layout/useThemeSwitcher'
import { useDarkModeStore } from '@/store/dark-mode'
import VerticalNavLayout from '@layouts/components/VerticalNavLayout.vue'
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useTheme } from 'vuetify'

const {
  name: themeName,
  global: globalTheme,
} = useTheme()

const darkModeStore = useDarkModeStore()
const router = useRouter()
const { changeTheme } = useThemeSwitcher()

onMounted(() => {
  globalTheme.name.value = darkModeStore.darkMode ? 'dark' : 'light'
  darkModeStore.updateDOMDarkMode(darkModeStore.darkMode)
})

const goToHome = () => {
  router.push({ name: 'summarizer' })
}
</script>

<template>
  <VerticalNavLayout>
    <!-- 👉 navbar: sin contenido, colapsada a 0 (ver VerticalNavLayout.vue) —
         solo el botón de menú para mobile, flotando sobre el contenido. -->
    <template #navbar="{ toggleVerticalOverlayNavActive }">
      <IconBtn
        class="d-lg-none fixed top-4 start-4 z-[60] pointer-events-auto rounded-full bg-white dark:bg-[#2B2C40] shadow-lg"
        @click="toggleVerticalOverlayNavActive(true)"
      >
        <VIcon icon="bx-menu" />
      </IconBtn>
    </template>

    <template #vertical-nav-header="{ toggleIsOverlayNavActive }">
      <div
        class="cursor-pointer app-logo app-title-wrapper"
        @click="goToHome"
      >
        <VSheet class="w-10 h-10 rounded-lg d-flex align-center justify-center bg-primary">
          <span class="text-white font-weight-bold text-lg">dA</span>
        </VSheet>
        <span
          class="text-2xl font-weight-bold tracking-wide"
          :style="{ color: globalTheme.name.value == 'dark' ? '#8B7FF0' : '#6D5AE0' }"
        >drakAI</span>
      </div>

      <IconBtn
        class="d-block d-lg-none"
        @click="toggleIsOverlayNavActive(false)"
      >
        <VIcon icon="bx-x" />
      </IconBtn>
    </template>

    <template #vertical-nav-content>
      <NavItems />
    </template>

    <template #after-vertical-nav-items>
      <VDivider class="mx-3" />
      <ul class="my-2">
        <li class="nav-link">
          <a
            href="javascript:void(0)"
            role="button"
            @click="changeTheme"
          >
            <VIcon
              :icon="themeName === 'dark' ? 'bx-sun' : 'bx-moon'"
              class="nav-item-icon"
            />
            <span class="nav-item-title">
              Modo {{ themeName === 'dark' ? 'claro' : 'oscuro' }}
            </span>
          </a>
        </li>
      </ul>
    </template>

    <!-- 👉 Pages -->
    <slot />

    <!-- 👉 Footer -->
    <template #footer>
      <Footer />
    </template>
  </VerticalNavLayout>
</template>

<style lang="scss" scoped>
.app-logo {
  display: flex;
  align-items: center;
  column-gap: 0.75rem;
}
</style>
