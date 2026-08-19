<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'

const props = defineProps({
  show: Boolean,
  avatars: {
    type: Array,
    default: () => []
  },
  currentAvatar: String,
  title: String,
  canDelete: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'delete'])

const currentIndex = ref(0)

// ریست کردن ایندکس زمان باز شدن یا تغییر لیست عکس‌ها
watch(() => props.show, (newVal) => {
  if (newVal) currentIndex.value = 0
})

const galleryImages = computed(() => {
  if (props.avatars && props.avatars.length > 0) {
    return props.avatars
  }
  return props.currentAvatar ? [props.currentAvatar] : []
})

const activeImage = computed(() => {
  return galleryImages.value[currentIndex.value] || props.currentAvatar
})

const nextImage = () => {
  if (galleryImages.value.length <= 1) return
  currentIndex.value = (currentIndex.value + 1) % galleryImages.value.length
}

const prevImage = () => {
  if (galleryImages.value.length <= 1) return
  currentIndex.value = (currentIndex.value - 1 + galleryImages.value.length) % galleryImages.value.length
}

const handleDelete = () => {
  if (!activeImage.value) return

  // ارسال اطلاعات عکس فعال برای حذف به کامپوننت والد
  emit('delete', {
    image: activeImage.value,
    index: currentIndex.value
  })

  // تنظیم ایندکس مجاز پس از حذف
  if (currentIndex.value > 0 && currentIndex.value >= galleryImages.value.length - 1) {
    currentIndex.value--
  }
}

const handleKeydown = (e) => {
  if (!props.show) return
  if (e.key === 'Escape') emit('close')
  if (e.key === 'ArrowRight') prevImage()
  if (e.key === 'ArrowLeft') nextImage()
}

onMounted(() => window.addEventListener('keydown', handleKeydown))
onUnmounted(() => window.removeEventListener('keydown', handleKeydown))
</script>

<template>
  <Transition name="fade">
    <div
      v-if="show"
      class="fixed inset-0 bg-black/90 z-[100] flex flex-col justify-between p-4 backdrop-blur-md select-none"
      @click="emit('close')"
    >
      <!-- هدر گالری -->
      <div class="flex items-center justify-between text-white z-10 p-2" @click.stop>
        <div class="flex flex-col">
          <span class="font-bold text-sm">{{ title }}</span>
          <span v-if="galleryImages.length > 1" class="text-xs text-gray-400">
            عکس {{ currentIndex + 1 }} از {{ galleryImages.length }}
          </span>
        </div>

        <div class="flex items-center gap-2">
          <!-- دکمه حذف عکس -->
          <button
            v-if="canDelete && galleryImages.length > 0"
            @click="handleDelete"
            title="حذف تصویر"
            class="p-2 rounded-full bg-red-500/20 text-red-400 hover:bg-red-500 hover:text-white transition-all"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>

          <!-- دکمه بستن -->
          <button @click="emit('close')" class="p-2 rounded-full bg-white/10 hover:bg-white/20 transition-all">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- عکس اصلی -->
      <div class="flex-1 flex items-center justify-center relative my-auto" @click.stop>
        <img
          v-if="activeImage"
          :src="activeImage"
          class="max-h-[75vh] max-w-full object-contain rounded-2xl shadow-2xl transition-all duration-300"
        />
        <div v-else class="text-gray-400 text-sm">تصویری موجود نیست</div>

        <!-- دکمه‌های بعدی / قبلی -->
        <template v-if="galleryImages.length > 1">
          <button
            @click="prevImage"
            class="absolute right-2 top-1/2 -translate-y-1/2 p-3 rounded-full bg-black/40 text-white hover:bg-black/70 backdrop-blur transition-all"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
            </svg>
          </button>

          <button
            @click="nextImage"
            class="absolute left-2 top-1/2 -translate-y-1/2 p-3 rounded-full bg-black/40 text-white hover:bg-black/70 backdrop-blur transition-all"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
        </template>
      </div>

      <!-- نشانگرهای زیر عکس (Dots) -->
      <div v-if="galleryImages.length > 1" class="flex justify-center gap-2 py-3 z-10" @click.stop>
        <button
          v-for="(_, idx) in galleryImages"
          :key="idx"
          @click="currentIndex = idx"
          class="h-2 rounded-full transition-all"
          :class="idx === currentIndex ? 'w-6 bg-white' : 'w-2 bg-white/40 hover:bg-white/70'"
        ></button>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
