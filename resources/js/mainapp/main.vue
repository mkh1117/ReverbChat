<template>
  <div class="relative min-h-screen bg-gray-100 p-4">

    <!-- نوار بالای صفحه اصلی -->
    <div v-if="!searchOpen" class="flex justify-between items-center">
      <h1 class="text-2xl font-bold text-gray-800">پیام‌ها</h1>
      <!-- آیکن سرچ -->
      <button @click="toggleSearch" class="p-2">
        <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z" />
        </svg>
      </button>
    </div>

    <!-- لیست چت‌ها -->
    <div v-if="!searchOpen" class="mt-6 grid gap-4">
        <a href="">
      <div v-for="(chat, index) in chats" :key="index"
        class="bg-white p-4 rounded-xl shadow hover:shadow-md transition">
        <div class="flex items-center gap-4">
          <img :src="chat.avatar" class="w-10 h-10 rounded-full border-2"
            :class="chat.online ? 'border-green-500' : 'border-gray-300'" />
          <div>
            <p class="font-semibold text-gray-800">{{ chat.username }}</p>
            <p class="text-sm text-gray-500">{{ chat.lastMessage }}</p>
          </div>
        </div>
      </div>
      </a>
    </div>

    <!-- حالت سرچ تمام‌صفحه -->
    <transition name="fade">
      <div v-if="searchOpen" class="fixed inset-0 bg-white z-50 p-6 flex flex-col">
        <!-- نوار بالا با آیکن برگشت -->
        <div class="flex items-center justify-between mb-4">
          <button @click="toggleSearch" class="p-2">
            <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <h2 class="text-xl font-semibold text-gray-800">جستجو</h2>
          <div class="w-6"></div> <!-- فضای خالی برای تراز کردن -->
        </div>

        <!-- اینپوت سرچ -->
        <input type="text" v-model="search" autofocus placeholder="جستجو کن..."
          class="text-xl px-4 py-2 border border-gray-300 rounded-md shadow mb-4 focus:outline-none focus:ring-2 focus:ring-blue-400" />

        <!-- نتایج سرچ -->
        <div v-if="search.length > 0" class="overflow-auto space-y-4">
          <div v-for="(chat, index) in filteredChats" :key="index"
            class="flex items-center justify-between p-4 rounded-lg shadow border hover:bg-gray-50 cursor-pointer">
            <div class="flex items-center gap-4">
              <img :src="chat.avatar" class="w-10 h-10 rounded-full border-2"
                :class="chat.online ? 'border-green-500' : 'border-gray-300'" />
              <div>
                <p class="font-semibold">{{ chat.username }}</p>
                <p class="text-sm text-gray-500">{{ chat.lastMessage }}</p>
              </div>
            </div>
            <span class="text-xs text-gray-400">{{ chat.time }}</span>
          </div>
          <p v-if="filteredChats.length === 0" class="text-center text-gray-400">هیچی پیدا نشد 😕</p>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const searchOpen = ref(false)
const search = ref('')

const toggleSearch = () => {
  searchOpen.value = !searchOpen.value
  if (!searchOpen.value) search.value = ''
}

const chats = ref([
  {
    username: 'علی رضایی',
    lastMessage: 'سلام، وقت بخیر...',
    time: '14:32',
    online: true,
    avatar: 'https://i.pravatar.cc/150?img=5',
  },
  {
    username: 'مریم قاسمی',
    lastMessage: 'منتظرم خبری بدی...',
    time: 'دیروز',
    online: false,
    avatar: 'https://i.pravatar.cc/150?img=3',
  },
  {
    username: 'فاطمه محمدی',
    lastMessage: 'اوکی! تا بعد میبینمت 👋',
    time: '13:15',
    online: false,
    avatar: 'https://i.pravatar.cc/150?img=12',
  },
])

const filteredChats = computed(() =>
  chats.value.filter(chat =>
    chat.username.includes(search.value) ||
    chat.lastMessage.includes(search.value)
  )
)
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: all 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
  transform: translateY(10px);
}
</style>
