<template>
  <Link
    :href="`/chat/${room.id}`"
    class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-100 transition-colors cursor-pointer block group"
  >
    <div class="flex items-center gap-3 min-w-0 flex-1">
      <div class="relative flex-shrink-0">

        <img
          v-if="room.avatar"
          :src="room.avatar"
          :alt="room.name"
          class="w-11 h-11 rounded-full object-cover"
        />
        <div
          v-else
          class="w-11 h-11 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-base"
        >
          {{ room.name ? room.name.charAt(0) : '?' }}
        </div>


        <span
          v-if="room.type === 'private' && isOnline"
          class="absolute bottom-0 left-0 w-3.5 h-3.5 bg-green-500 border-2 border-white rounded-full"
          title="آنلاین"
        ></span>
      </div>


      <div class="flex flex-col min-w-0 flex-1 pl-2">
        <div class="flex items-center justify-between mb-0.5">
          <span class="text-sm font-semibold text-gray-900 truncate">{{ room.name }}</span>


          <span v-if="latestMessageTime" class="text-[11px] text-gray-400 font-medium mr-2 flex-shrink-0">
            {{ latestMessageTime }}
          </span>
        </div>


        <p class="text-xs text-gray-500 truncate">
          {{ latestMessageText }}
        </p>
      </div>

    </div>


    <div v-if="room.unread_count > 0" class="flex items-center justify-center mr-2 flex-shrink-0">
      <span class="min-w-[20px] h-5 px-1.5 text-xs font-bold text-white bg-blue-600 rounded-full flex items-center justify-center shadow-sm">
        {{ room.unread_count > 99 ? '+99' : room.unread_count }}
      </span>
    </div>
  </Link>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  room: {
    type: Object,
    required: true
  },
  isOnline: {
    type: Boolean,
    default: false
  }
})


const latestMessageText = computed(() => {
  if (props.room.messages && props.room.messages.length > 0) {
    return props.room.messages[0].message
  }
  return 'هنوز پیامی ارسال نشده است'
})


const latestMessageTime = computed(() => {
  if (!props.room.messages || props.room.messages.length === 0) return ''

  const dateStr = props.room.messages[0].created_at
  if (!dateStr) return ''

  const date = new Date(dateStr)
  if (isNaN(date.getTime())) return ''

  const now = new Date()
  const isToday = date.toDateString() === now.toDateString()


  if (isToday) {
    return date.toLocaleTimeString('fa-IR', { hour: '2-digit', minute: '2-digit' })
  }


  return date.toLocaleDateString('fa-IR', { month: 'short', day: 'numeric' })
})
</script>
