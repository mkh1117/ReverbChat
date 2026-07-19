<template>
  <a :href="`/chat/${room.id}`"
    class="flex items-center gap-3 px-3.5 py-3 bg-white rounded-2xl border border-gray-100 hover:bg-gray-50 transition">

    <!-- آواتار -->
    <div :class="['w-11 h-11 flex items-center justify-center text-sm font-medium flex-shrink-0', avatarStyle]">
      {{ avatarText }}
    </div>

    <!-- اطلاعات -->
    <div class="flex-1 min-w-0">
      <p class="text-sm font-medium text-gray-900">{{ room.name }}</p>
      <p v-if="room.last_message" class="text-xs text-gray-400 truncate mt-0.5">
        {{ room.last_message }}
      </p>
    </div>

    <!-- متا -->
    <div class="flex flex-col items-end gap-1 flex-shrink-0">
      <span class="text-xs text-gray-400">{{ room.last_time ?? '' }}</span>
      <span v-if="room.unread_count" class="bg-blue-600 text-white text-xs rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1">
        {{ room.unread_count }}
      </span>
      <span v-else-if="room.type !== 'private'" class="text-xs text-gray-400 border border-gray-200 rounded-full px-2 py-0.5">
        {{ typeLabel }}
      </span>
    </div>
  </a>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  room: Object
})

const avatarStyle = computed(() => {
  if (props.room.type === 'channel') return 'bg-purple-50 text-purple-700 rounded-xl'
  if (props.room.type === 'group')   return 'bg-emerald-50 text-emerald-700 rounded-full'
  return 'bg-blue-50 text-blue-700 rounded-full'
})

const avatarText = computed(() => {
  if (props.room.type === 'channel') return '#'
  return props.room.name?.slice(0, 2) ?? '?'
})

const typeLabel = computed(() => {
  return props.room.type === 'group'
    ? 'گروه'
    : (props.room.type === 'channel' ? 'کانال' : 'کاربر')
})
</script>
