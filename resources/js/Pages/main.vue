<template>
  <div class="min-h-screen bg-gray-50 flex flex-col" dir="rtl">

    <!-- هدر ثابت و یکپارچه -->
    <header class="sticky top-0 z-40 bg-white border-b border-gray-100 px-4 py-3 shadow-sm transition-all duration-200">
      <div class="flex items-center justify-between h-10 gap-3">

        <!-- حالت عادی هدر -->
        <template v-if="!searchOpen">
          <h1 class="text-xl font-bold text-gray-900 selection:bg-blue-100">پیام‌ها</h1>
          <button @click="toggleSearch"
            class="w-10 h-10 rounded-full flex items-center justify-center text-gray-500 hover:bg-gray-100 active:scale-95 transition-all"
            aria-label="جستجو">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z" />
            </svg>
          </button>
        </template>

        <!-- حالت فعال بودن جستجو -->
        <template v-else>
          <button @click="toggleSearch"
            class="w-10 h-10 rounded-full flex items-center justify-center text-gray-500 hover:bg-gray-100 active:scale-95 transition-all"
            aria-label="بازگشت">
            <!-- فلش اصلاح شده برای RTL (اشاره به راست) -->
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
          </button>

          <div class="flex-1 relative">
            <input
              v-model="search"
              ref="searchInput"
              type="text"
              placeholder="جستجو در گفتگوها..."
              class="w-full h-10 pr-4 pl-10 text-sm bg-gray-100 text-gray-900 rounded-full border-0 focus:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
            />
            <!-- دکمه پاک کردن متن سرچ -->
            <button v-if="search" @click="search = ''" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </template>

      </div>
    </header>

    <!-- محتوای اصلی (لیست چت‌ها) -->
    <main class="flex-1 p-4 max-w-2xl w-full mx-auto">

      <!-- لیست اصلی چت‌ها (حالت عادی) -->
      <div v-if="!searchOpen" class="space-y-1">
        <div class="flex items-center justify-between px-1 mb-2">
          <span class="text-xs font-semibold text-gray-400 tracking-wider">گفتگوهای اخیر</span>
          <span class="text-xs text-gray-400 bg-gray-200/60 px-2 py-0.5 rounded-full">{{ rooms.length }} مکالمه</span>
        </div>
        <RoomItem v-for="room in rooms" :key="room.id" :room="room" />
      </div>

      <!-- نتایج جستجو -->
      <div v-else class="space-y-1">
        <template v-if="search.length > 0">
          <RoomItem v-for="room in filteredRooms" :key="room.id" :room="room" />

          <!-- وضعیت: نتیجه‌ای یافت نشد -->
          <div v-if="filteredRooms.length === 0" class="flex flex-col items-center justify-center py-20 text-gray-400">
            <div class="p-4 bg-gray-100 rounded-full mb-3">
              <svg class="w-8 h-8 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z" />
              </svg>
            </div>
            <p class="text-sm font-medium text-gray-500">نتیجه‌ای برای «{{ search }}» پیدا نشد</p>
            <p class="text-xs text-gray-400 mt-1">املای کلمات را بررسی کنید یا عبارت دیگری بنویسید.</p>
          </div>
        </template>

        <!-- وضعیت: هنوز چیزی تایپ نشده -->
        <div v-else class="flex flex-col items-center justify-center py-20 text-gray-400 text-sm">
          <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
          </svg>
          <span class="font-medium text-gray-500">نام مخاطب یا گروه را جستجو کنید</span>
        </div>
      </div>

    </main>
  </div>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue'
import RoomItem from '../Components/RoomItem.vue'

const props = defineProps({
  rooms: { type: Array, default: () => [] }
})

const searchOpen = ref(false)
const search = ref('')
const searchInput = ref(null)

const toggleSearch = async () => {
  searchOpen.value = !searchOpen.value
  if (!searchOpen.value) {
    search.value = ''
  } else {
    // فوکوس خودکار روی اینپوت بعد از رندر شدن
    await nextTick()
    searchInput.value?.focus()
  }
}

const filteredRooms = computed(() =>
  props.rooms.filter(room =>
    room.name.toLowerCase().includes(search.value.toLowerCase())
  )
)
</script>
