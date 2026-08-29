<template>
  <Teleport to="body">
    <div
      v-if="isOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
      dir="rtl"
      @click.self="close"
    >
      <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl flex flex-col max-h-[80vh] overflow-hidden animate-in fade-in zoom-in-95 duration-150">

        <!-- هدر مودال -->
        <div class="p-4 border-b border-gray-100 flex items-center justify-between">
          <h3 class="font-bold text-gray-800 text-base">هدایت پیام به...</h3>
          <button @click="close" class="text-gray-400 hover:text-gray-600 p-1 rounded-full hover:bg-gray-100 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- باکس جستجو -->
        <div class="p-3 bg-gray-50 border-b border-gray-100">
          <div class="relative">
            <input
              v-model="search"
              type="text"
              placeholder="جستجوی گفتگو..."
              class="w-full bg-white text-sm text-gray-800 rounded-xl pr-9 pl-4 py-2 border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all placeholder:text-gray-400"
            />
            <svg class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z" />
            </svg>
          </div>
        </div>

        <!-- پیش‌نمایش پیام انتخابی -->
        <div v-if="message" class="px-4 py-2.5 bg-blue-50 border-b border-blue-100 flex items-center gap-2 text-xs text-blue-800">
          <svg class="w-4 h-4 flex-shrink-0 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
          </svg>
          <span class="truncate font-medium">{{ message.message }}</span>
        </div>

        <!-- لیست گفتگوها -->
        <div class="flex-1 overflow-y-auto p-2 space-y-1">
          <button
            v-for="chat in filteredChats"
            :key="chat.id"
            @click="selectTargetChat(chat)"
            class="w-full flex items-center justify-between p-2.5 rounded-xl hover:bg-gray-50 transition-colors text-right group"
          >
            <div class="flex items-center gap-3 min-w-0 flex-1">
              <div class="w-10 h-10 rounded-full bg-blue-500 text-white flex items-center justify-center font-bold text-xs flex-shrink-0 overflow-hidden">
                <img v-if="getChatAvatar(chat)" :src="getChatAvatar(chat)" class="w-full h-full object-cover" />
                <span v-else>{{ getInitials(getDisplayName(chat)) }}</span>
              </div>
              <div class="flex flex-col min-w-0">
                <span class="text-sm font-semibold text-gray-800 truncate">{{ getDisplayName(chat) }}</span>
                <span class="text-[11px] text-gray-400 truncate">
                  {{ chat.type === 'private' ? 'گفتگوی شخصی' : (chat.type === 'group' ? 'گروه' : 'کانال') }}
                </span>
              </div>
            </div>

            <span class="text-xs text-blue-600 font-medium opacity-0 group-hover:opacity-100 transition-opacity">
              ارسال
            </span>
          </button>

          <div v-if="filteredChats.length === 0" class="text-center py-8 text-xs text-gray-400">
            گفتگویی یافت نشد
          </div>
        </div>

      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
  isOpen: Boolean,
  message: Object,
  chats: { type: Array, default: () => [] }
})

const emit = defineEmits(['close', 'forward'])

const search = ref('')
const currentUserId = usePage().props.auth.user.id

const close = () => {
  search.value = ''
  emit('close')
}


const getDisplayName = (chat) => {
  if (chat.type === 'private' && chat.users) {
    const partner = chat.users.find(u => Number(u.id) !== Number(currentUserId))
    return partner ? partner.name : (chat.name || 'کاربر')
  }
  return chat.name
}


const getChatAvatar = (chat) => {
  if (chat.avatar_url) return chat.avatar_url
  if (chat.type === 'private' && chat.users) {
    const partner = chat.users.find(u => Number(u.id) !== Number(currentUserId))
    return partner?.avatar || null
  }
  return chat.avatar || null
}

const selectTargetChat = (chat) => {
  emit('forward', { targetRoom: chat, message: props.message })
  close()
}

const filteredChats = computed(() => {
  return props.chats.filter(chat => {

    if (chat.type === 'channel') {
      const role = chat.user_role || chat.pivot?.role
      if (!['owner', 'admin'].includes(role)) {
        return false
      }
    }


    const name = getDisplayName(chat).toLowerCase()
    return name.includes(search.value.toLowerCase())
  })
})

const getInitials = (name) => {
  if (!name) return '?'
  const parts = name.trim().split(' ')
  return parts.length >= 2 ? (parts[0][0] + parts[1][0]).toUpperCase() : name.substring(0, 2).toUpperCase()
}
</script>
