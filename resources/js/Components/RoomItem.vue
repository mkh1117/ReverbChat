<template>
  <Link
    :href="`/chat/${room.id}`"
    class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-100 transition-colors cursor-pointer block"
  >
    <div class="flex items-center gap-3">
      <!-- آواتار یا آیکون چت -->
      <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold">
        {{ room.name.charAt(0) }}
      </div>

      <!-- نام چت -->
      <div class="flex flex-col">
        <span class="text-sm font-semibold text-gray-900">{{ room.name }}</span>
      </div>
    </div>

    <!-- نشانگر پیام‌های خوانده‌نشده -->
    <div v-if="room.unread_count > 0" class="flex items-center justify-center">
      <span class="min-w-[20px] h-5 px-1.5 text-xs font-bold text-white bg-blue-600 rounded-full flex items-center justify-center shadow-sm">
        {{ room.unread_count > 99 ? '+99' : room.unread_count }}
      </span>
    </div>
  </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  room: {
    type: Object,
    required: true
  }
})
</script>
