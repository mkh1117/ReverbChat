<template>
  <Link
    :href="`/chat/${room.id}`"
    class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-100 transition-colors cursor-pointer block group"
  >
    <div class="flex items-center gap-3 min-w-0 flex-1">
      <div class="relative flex-shrink-0">

        <!-- آواتار تصویری -->
        <img
          v-if="displayAvatar"
          :src="displayAvatar"
          :alt="displayName"
          class="w-11 h-11 rounded-full object-cover border border-gray-100 shadow-sm"
        />

        <!-- آواتار پیش‌فرض طرح تلگرام -->
        <div
          v-else
          class="w-11 h-11 rounded-full text-white flex items-center justify-center font-bold text-sm shadow-sm selection:bg-none"
          :class="avatarBgColor"
        >
          {{ avatarInitials }}
        </div>

        <!-- نشانگر آنلاین -->
        <span
          v-if="room.type === 'private' && isOnline"
          class="absolute bottom-0 left-0 w-3.5 h-3.5 bg-green-500 border-2 border-white rounded-full"
          title="آنلاین"
        ></span>
      </div>

      <div class="flex flex-col min-w-0 flex-1 pl-2">
        <div class="flex items-center justify-between mb-0.5">
          <span class="text-sm font-semibold text-gray-900 truncate">{{ displayName }}</span>

          <span v-if="latestMessageTime" class="text-[11px] text-gray-400 font-medium mr-2 flex-shrink-0">
            {{ latestMessageTime }}
          </span>
        </div>

        <!-- پیش‌نمایش آخرین پیام (همراه با لینک پروفایل فرستنده اصلی) -->
        <p class="text-xs text-gray-500 truncate flex items-center gap-1">
          <template v-if="forwardedUser">
            <span>فوروارد از</span>
            <Link
              v-if="forwardedUser.username"
              :href="route('user.profile', { username: forwardedUser.username })"
              @click.stop
              class="font-semibold text-blue-600 hover:underline flex-shrink-0"
            >
              {{ forwardedUser.name }}
            </Link>
            <span v-else class="font-semibold text-gray-700 flex-shrink-0">
              {{ forwardedUser.name }}
            </span>
            <span>:</span>
          </template>

          <span class="truncate">{{ latestMessageText }}</span>
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
import { Link, usePage } from '@inertiajs/vue3'

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

const currentUserId = computed(() => usePage().props.auth.user.id)

const bgColors = [
  'bg-red-500',
  'bg-orange-500',
  'bg-amber-500',
  'bg-emerald-500',
  'bg-teal-500',
  'bg-cyan-500',
  'bg-blue-500',
  'bg-indigo-500',
  'bg-purple-500',
  'bg-pink-500'
]

const partnerUser = computed(() => {
  if (!props.room.users || props.room.users.length === 0) return null
  return props.room.users.find(u => Number(u.id) !== Number(currentUserId.value)) || props.room.users[0]
})

const displayName = computed(() => {
  if (props.room.type === 'private') {
    return partnerUser.value ? partnerUser.value.name : 'کاربر'
  }
  return props.room.name || 'گروه'
})

const displayAvatar = computed(() => {
  if (props.room.type === 'private') {
    return partnerUser.value?.avatar || props.room.avatar_url || null
  }
  return props.room.avatar_url || props.room.avatar || null
})

const avatarInitials = computed(() => {
  const name = displayName.value.trim()
  if (!name) return '?'

  const parts = name.split(/\s+/)
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase()
  }
  return name.substring(0, 2).toUpperCase()
})

const avatarBgColor = computed(() => {
  const targetId = props.room.type === 'private'
    ? (partnerUser.value?.id || props.room.id)
    : props.room.id

  return bgColors[targetId % bgColors.length]
})

// استخراج اطلاعات کاربرِ پیام اصلی برای لینک پروفایل
const forwardedUser = computed(() => {
  if (!props.room.messages || props.room.messages.length === 0) return null
  const lastMsg = props.room.messages[0]

  if (lastMsg.forwarded_from) {
    return {
      name: lastMsg.forwarded_from.sender_name || 'کاربر',
      username: lastMsg.forwarded_from.sender_username || null
    }
  }

  if (lastMsg.forwarded_from_message?.sender) {
    return {
      name: lastMsg.forwarded_from_message.sender.name,
      username: lastMsg.forwarded_from_message.sender.username
    }
  }

  return null
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
