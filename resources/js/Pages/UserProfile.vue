<template>
  <div class="min-h-screen bg-slate-100 flex flex-col items-center p-4">
    <div class="w-full max-w-md bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">

      <!-- هدر و دکمه بازگشت -->
      <div class="p-4 flex items-center justify-between border-b border-gray-100">
        <button @click="goBack" class="w-9 h-9 rounded-full flex items-center justify-center text-gray-500 hover:bg-gray-100">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
          </svg>
        </button>
        <span class="font-bold text-gray-700 text-sm">پروفایل کاربر</span>
        <div class="w-9"></div>
      </div>

      <!-- بخش آواتار، نشانگر آنلاین و نام -->
      <div class="flex flex-col items-center pt-6 pb-4 px-4 text-center">
        <div class="w-24 h-24 rounded-full overflow-hidden shadow-md border-2 border-white mb-3 relative bg-slate-200">
          <img
            v-if="profileUser.avatar"
            :src="profileUser.avatar"
            :alt="profileUser.name"
            class="w-full h-full object-cover"
          />
          <div v-else class="w-full h-full bg-blue-600 text-white flex items-center justify-center text-2xl font-bold">
            {{ getInitials(profileUser.name) }}
          </div>

          <!-- نشانگر سبزرنگ آنلاین بودن روی آواتار -->
          <span
            v-if="isOnline"
            class="absolute bottom-1 right-1 w-5 h-5 bg-emerald-500 border-2 border-white rounded-full shadow-sm"
          ></span>
        </div>

        <h1 class="text-xl font-bold text-gray-800">{{ profileUser.name }}</h1>
        <span class="text-sm text-blue-600 font-medium dir-ltr">@{{ profileUser.username }}</span>
      </div>

      <!-- دکمه ارسال پیام -->
      <div class="px-6 py-2 flex justify-center">
        <button
          @click="startChat"
          :disabled="isProcessing"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-2xl flex items-center justify-center gap-2 shadow-lg shadow-blue-500/25 transition-all active:scale-95 disabled:opacity-50"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
          </svg>
          <span>ارسال پیام خصوصی</span>
        </button>
      </div>

      <!-- بیو و وضعیت آخرین بازدید/آنلاین -->
      <div class="p-6 space-y-4">
        <div v-if="profileUser.bio" class="bg-gray-50 p-4 rounded-2xl border border-gray-100 text-right">
          <span class="text-xs font-bold text-gray-400 block mb-1">درباره من (بیو)</span>
          <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ profileUser.bio }}</p>
        </div>

        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 flex items-center justify-between text-xs text-gray-500">
          <span>وضعیت</span>
          <!-- نمایش آنلاین یا تاریخ آخرین بازدید -->
          <span v-if="isOnline" class="text-emerald-600 font-bold flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            آنلاین
          </span>
          <span v-else>{{ formatLastSeen(profileUser.last_seen_at) }}</span>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { globalOnlineUsers } from '@/app.js' // 👈 ایمپورت آرایه کاربران آنلاین

const props = defineProps({
  profileUser: Object,
  avatars:     Array
})

const isProcessing = ref(false)

// بررسی آنلاین بودن کاربر بر اساس لیست حضور در کانال Presence
const isOnline = computed(() => {
  return globalOnlineUsers.value.some(id => Number(id) === Number(props.profileUser.id))
})

const goBack = () => {
  window.history.back()
}

const startChat = () => {
  isProcessing.value = true
  router.post(`/chat/private/start/${props.profileUser.id}`, {}, {
    onFinish: () => {
      isProcessing.value = false
    }
  })
}

const getInitials = (name) => {
  if (!name) return '?'
  const parts = name.trim().split(' ')
  return parts.length >= 2 ? (parts[0][0] + parts[1][0]).toUpperCase() : name.substring(0, 2).toUpperCase()
}

const formatLastSeen = (dateString) => {
  if (!dateString) return 'نامشخص'
  const formattedString = dateString.includes('T') ? dateString : dateString.replace(' ', 'T') + 'Z'
  let date = new Date(formattedString)

  if (isNaN(date.getTime())) {
    date = new Date(dateString)
  }

  const now = new Date()
  const startOfToday = new Date(now.getFullYear(), now.getMonth(), now.getDate())
  const startOfYesterday = new Date(startOfToday)
  startOfYesterday.setDate(startOfYesterday.getDate() - 1)

  const startOfTargetDate = new Date(date.getFullYear(), date.getMonth(), date.getDate())

  const timeFormatter = new Intl.DateTimeFormat('fa-IR', {
    hour: '2-digit',
    minute: '2-digit',
    hour12: false
  })
  const timeString = timeFormatter.format(date)

  if (startOfTargetDate.getTime() === startOfToday.getTime()) {
    return `امروز ساعت ${timeString}`
  }

  if (startOfTargetDate.getTime() === startOfYesterday.getTime()) {
    return `دیروز ساعت ${timeString}`
  }

  const fullDateFormatter = new Intl.DateTimeFormat('fa-IR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    hour12: false
  })

  return fullDateFormatter.format(date)
}
</script>
