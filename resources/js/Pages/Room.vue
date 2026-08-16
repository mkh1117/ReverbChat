<template>
  <div class="h-screen flex flex-col bg-slate-100 relative" @click="closeAllMenus">

    <!-- هدر چت -->
    <header class="bg-white border-b border-gray-200 px-4 py-3 flex items-center gap-3 shadow-sm z-10 cursor-pointer" @click.stop="openProfileModal">
      <!-- دکمه بازگشت -->
      <a :href="route('dashboard')"
         @click.stop
         class="w-9 h-9 rounded-full flex items-center justify-center text-gray-500 hover:bg-gray-100 transition-all">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
        </svg>
      </a>

      <!-- عکس پروفایل هدر -->
      <div class="relative w-10 h-10 flex-shrink-0">
        <img
          v-if="currentRoomAvatar"
          :src="currentRoomAvatar"
          :alt="chat_name"
          class="w-full h-full rounded-full object-cover border border-gray-200"
        />
        <div
          v-else
          class="w-full h-full rounded-full bg-blue-500 text-white flex items-center justify-center font-bold text-sm"
        >
          {{ getInitials(chat_name) }}
        </div>

        <span
          v-if="room.type === 'direct' && isPartnerOnline"
          class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"
        ></span>
      </div>

      <!-- عنوان و وضعیت چت -->
      <div class="flex flex-col flex-1 min-w-0">
        <h2 class="font-bold text-gray-800 text-base leading-tight truncate">{{ chat_name }}</h2>

        <div class="text-xs font-medium mt-0.5">
          <span v-if="room.type === 'channel'" class="text-gray-400">کانال عمومی</span>
          <span v-else-if="room.type === 'group'" class="text-gray-400">
            {{ roomMembers.length || onlineUsers.length }} عضو ، {{ onlineUsers.length }} آنلاین
          </span>
          <template v-else>
            <span v-if="isPartnerOnline" class="text-green-500 flex items-center gap-1">
              آنلاین
            </span>
            <span v-else class="text-gray-400">
              آخرین بازدید {{ formatLastSeen(other_user?.last_seen_at) }}
            </span>
          </template>
        </div>
      </div>
    </header>

    <!-- باکس پیام‌ها -->
    <div
      ref="chatBox"
      @scroll="handleScroll"
      class="flex-1 overflow-y-auto p-4 space-y-4 relative"
      :class="{ 'scroll-smooth': isSmooth }"
    >
      <template v-for="(m, index) in messages" :key="m.id || index">

        <!-- 📅 جداساز تاریخ روز -->
        <div v-if="shouldShowDateHeader(index)" class="flex justify-center my-3 select-none">
          <span class="bg-gray-200/80 backdrop-blur text-gray-600 text-[11px] font-medium px-3 py-1 rounded-full shadow-sm">
            {{ formatDateLabel(m.created_at) }}
          </span>
        </div>

        <!-- حباب پیام -->
        <div
          :id="'msg-' + m.id"
          class="message-bubble flex gap-2 w-full relative"
          :data-msg-id="m.id"
          :data-user="m.user"
          :data-is-read="m.is_read"
          :class="m.user === 'sender' ? 'flex-row-reverse' : 'flex-row'"
        >
          <!-- آواتار فرستنده -->
          <div v-if="m.user === 'receiver'" class="w-8 h-8 rounded-full flex-shrink-0 self-end mb-1">
            <img
              v-if="m.sender_avatar || (room.type === 'direct' && other_user?.avatar)"
              :src="m.sender_avatar || other_user?.avatar"
              class="w-full h-full rounded-full object-cover border border-gray-200"
            />
            <div
              v-else
              class="w-full h-full rounded-full bg-slate-400 text-white flex items-center justify-center text-xs font-bold"
            >
              {{ getInitials(m.sender_name) }}
            </div>
          </div>

          <div class="relative group max-w-xs md:max-w-md" :class="m.user === 'sender' ? 'self-end' : 'self-start'">
            <div
              @click.stop="toggleMessageMenu(index)"
              class="flex flex-col rounded-2xl shadow-sm text-sm leading-relaxed cursor-pointer select-none active:opacity-90 transition-all overflow-hidden pb-1"
              :class="[
                m.user === 'sender'
                  ? 'bg-blue-600 text-white rounded-bl-none self-end'
                  : 'bg-white text-gray-800 rounded-br-none border border-gray-100 self-start'
              ]"
            >
              <div
                v-if="m.reply_to"
                class="text-xs px-3 py-1.5 border-r-4 mt-2 mx-2 rounded flex flex-col text-right overflow-hidden"
                :class="m.user === 'sender'
                  ? 'bg-blue-700/40 border-white text-blue-100'
                  : 'bg-gray-100 border-blue-500 text-gray-500'"
              >
                <span class="font-bold text-[10px]" :class="m.user === 'sender' ? 'text-white' : 'text-blue-600'">پاسخ به:</span>
                <span class="truncate mt-0.5">{{ truncateText(m.reply_to.message) }}</span>
              </div>

              <div class="px-3 pt-2.5 pb-1 text-right relative min-w-[120px] break-words">
                <div
                  v-if="room.type === 'group' && m.user === 'receiver' && m.sender_name"
                  class="text-[11px] font-bold text-blue-600 mb-1 leading-tight truncate max-w-full select-none"
                >
                  {{ m.sender_name }}
                </div>

                <div class="whitespace-pre-wrap break-words">
                  {{ m.message }}
                </div>

                <div
                  class="flex items-center justify-end gap-1 mt-1 text-[10px] select-none"
                  :class="m.user === 'sender' ? 'text-blue-100' : 'text-gray-400'"
                  dir="ltr"
                >
                  <span>{{ formatTime(m.created_at) }}</span>

                  <template v-if="m.user === 'sender'">
                    <svg v-if="m.is_read" class="w-3.5 h-3.5 text-sky-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7M10 17l4 4L23 9" />
                    </svg>
                    <svg v-else class="w-3.5 h-3.5 text-blue-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                  </template>
                </div>
              </div>
            </div>

            <!-- منوی منوی عملیات پیام -->
            <div v-if="activeMenuIndex === index"
              class="absolute top-full mt-1 bg-white border border-gray-100 rounded-xl shadow-xl py-1 z-30 min-w-[110px]"
              :class="m.user === 'sender' ? 'left-0' : 'right-0'"
            >
              <button @click="startReply(m)" class="w-full text-right px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                پاسخ دادن
              </button>
              <button v-if="m.user === 'sender'" @click="deleteMessage(m, index)" class="w-full text-right px-3 py-2 text-xs text-red-600 hover:bg-red-50 flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                حذف پیام
              </button>
            </div>
          </div>
        </div>

      </template>
    </div>

    <!-- دکمه اسکرول به پایین -->
    <button
      v-if="showScrollDownBtn"
      @click="scrollToBottom"
      class="absolute bottom-20 left-4 bg-white text-gray-600 p-3 rounded-full shadow-lg border border-gray-100 hover:bg-gray-50 active:scale-95 transition-all z-20 flex items-center justify-center"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7-7-7" />
      </svg>
    </button>

    <!-- نوار پایین -->
    <div class="p-3 bg-white border-t border-gray-200 flex flex-col gap-2 z-10">
      <template v-if="canSendMessage">
        <div v-if="replyingTo" class="flex items-center justify-between bg-blue-50 border-r-4 border-blue-500 px-3 py-2 rounded-lg w-full text-xs">
          <div class="flex flex-col text-right overflow-hidden">
            <span class="font-bold text-blue-600">پاسخ به پیام</span>
            <span class="text-gray-500 truncate mt-0.5">{{ replyingTo.message }}</span>
          </div>
          <button @click="cancelReply" class="text-gray-400 hover:text-gray-600 p-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>

        <div class="flex items-center gap-2">
          <button
            @click="send"
            :disabled="!newMessage.trim()"
            class="bg-blue-600 text-white rotate-90 p-3 rounded-2xl hover:bg-blue-700 active:scale-95 disabled:opacity-40 disabled:scale-100 transition-all flex items-center justify-center shadow-md shadow-blue-500/20"
          >
            <svg class="w-5 h-5 transform rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" stroke-width="2" />
            </svg>
          </button>
          <div class="flex-1 relative flex items-center" dir="rtl">
            <input
              type="text"
              v-model="newMessage"
              @keyup.enter="send"
              placeholder="پیام خود را بنویسید..."
              class="w-full bg-gray-50 text-sm text-gray-800 border-0 rounded-2xl px-4 py-3 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all placeholder:text-gray-400"
            >
          </div>
        </div>
      </template>

      <template v-else>
        <div class="w-full py-2.5 text-center text-xs font-medium text-gray-500 bg-gray-100 rounded-2xl select-none">
          فقط مدیران این کانال می‌توانند پیام ارسال کنند
        </div>
      </template>
    </div>

    <!-- 👤 مودال پروفایل و مدیریت گروه -->
    <div
      v-if="showProfileModal"
      class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4"
      @click.self="closeProfileModal"
    >
      <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[85vh] transform transition-all animate-fade-in">

        <!-- هدر مودال -->
        <div class="bg-gradient-to-b from-blue-600 to-blue-500 pt-8 pb-6 px-6 flex flex-col items-center relative text-white flex-shrink-0">
          <button
            @click="closeProfileModal"
            class="absolute top-4 left-4 text-white/80 hover:text-white p-1 rounded-full bg-white/10 hover:bg-white/20 transition-all"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
<!-- بخش آواتار در مودال پروفایل -->
<div class="relative group w-24 h-24 rounded-full border-4 border-white/20 shadow-lg overflow-hidden mb-3">
  <img
    v-if="avatarPreviewUrl || currentRoomAvatar"
    :src="avatarPreviewUrl || currentRoomAvatar"
    :alt="chat_name"
    class="w-full h-full object-cover"
  />
  <div v-else class="w-full h-full bg-white/20 flex items-center justify-center font-bold text-2xl text-white">
    {{ getInitials(chat_name) }}
  </div>

  <!-- دکمه‌های مدیریت تصویر (فقط ادمین/مالک) -->
  <div
    v-if="canManageGroup"
    class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2"
  >
    <!-- انتخاب عکس -->
    <label class="p-2 bg-white/20 hover:bg-white/40 rounded-full cursor-pointer text-white transition-all" title="تغییر تصویر">
      <input type="file" ref="fileInput" accept="image/*" class="hidden" @change="onFileSelected" />
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
    </label>

    <!-- حذف عکس -->
    <button
      v-if="currentRoomAvatar"
      @click="removeAvatar"
      class="p-2 bg-red-500/80 hover:bg-red-600 rounded-full text-white transition-all"
      title="حذف تصویر"
    >
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
    </button>
  </div>
</div>

<!-- 🖼️ مودال پیش‌نمایش و تأیید آپلود عکس -->
<div
  v-if="selectedAvatarFile"
  class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[60] flex items-center justify-center p-4"
  dir="rtl"
>
  <div class="bg-white rounded-3xl p-5 w-full max-w-xs text-center space-y-4 shadow-2xl animate-fade-in">
    <h4 class="text-sm font-bold text-gray-800">پیش‌نمایش تصویر جدید</h4>

    <div class="w-32 h-32 mx-auto rounded-full overflow-hidden border-2 border-blue-500 shadow-inner">
      <img :src="avatarPreviewUrl" class="w-full h-full object-cover" />
    </div>

    <div class="flex items-center justify-center gap-2 pt-2">
      <button
        @click="confirmUploadAvatar"
        :disabled="isUploadingAvatar"
        class="flex-1 py-2 px-3 text-xs bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl shadow-md disabled:opacity-50 transition-all"
      >
        {{ isUploadingAvatar ? 'در حال آپلود...' : 'ذخیره تصویر' }}
      </button>
      <button
        @click="cancelAvatarSelection"
        :disabled="isUploadingAvatar"
        class="flex-1 py-2 px-3 text-xs bg-gray-100 hover:bg-gray-200 text-gray-600 font-medium rounded-xl transition-all"
      >
        انصراف
      </button>
    </div>
  </div>
</div>

          <h3 class="text-lg font-bold text-white text-center">{{ chat_name }}</h3>
          <p v-if="room.type === 'direct' && other_user?.username" class="text-xs text-blue-100 mt-0.5">@{{ other_user.username }}</p>
        </div>

        <!-- بدنه مودال (بایو و اعضا) -->
        <div class="p-5 space-y-5 text-right overflow-y-auto flex-1" dir="rtl">

          <!-- ویرایش/نمایش بایو -->
          <div class="space-y-1.5">
            <div class="flex items-center justify-between">
              <span class="text-xs font-semibold text-gray-400">بیوگرافی (توضیحات)</span>
              <button
                v-if="canManageGroup && !isEditingBio"
                @click="isEditingBio = true"
                class="text-xs text-blue-600 font-medium hover:underline"
              >
                ویرایش
              </button>
            </div>

            <!-- حالت ویرایش بایو -->
            <div v-if="isEditingBio" class="space-y-2">
              <textarea
                v-model="bioInput"
                rows="3"
                class="w-full text-xs text-gray-700 bg-gray-50 border border-blue-300 rounded-xl p-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="توضیحات گروه را وارد کنید..."
              ></textarea>
              <div class="flex justify-end gap-2">
                <button @click="isEditingBio = false" class="px-3 py-1 text-xs text-gray-500 hover:bg-gray-100 rounded-lg">انصراف</button>
                <button @click="saveBio" :disabled="isSavingBio" class="px-3 py-1 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                  {{ isSavingBio ? 'در حال ثبت...' : 'ذخیره' }}
                </button>
              </div>
            </div>

            <!-- حالت نمایش بایو -->
            <p v-else class="text-sm text-gray-700 leading-relaxed bg-gray-50 p-3 rounded-2xl border border-gray-100 whitespace-pre-line">
              {{ currentBio }}
            </p>
          </div>

          <!-- لیست کاربران (فقط برای گروه) -->
          <div v-if="room.type === 'group'" class="space-y-2">
            <div class="flex items-center justify-between border-b border-gray-100 pb-2">
              <span class="text-xs font-semibold text-gray-400">اعضای گروه</span>
              <span class="text-xs bg-blue-50 text-blue-600 font-bold px-2 py-0.5 rounded-full">
                {{ roomMembers.length }} نفر
              </span>
            </div>

            <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
              <div
                v-for="member in roomMembers"
                :key="member.id"
                class="flex items-center justify-between py-1.5 px-2 hover:bg-gray-50 rounded-xl transition-all"
              >
                <div class="flex items-center gap-2.5 min-w-0">
                  <div class="w-8 h-8 rounded-full overflow-hidden bg-slate-200 flex-shrink-0">
                    <img v-if="member.avatar" :src="member.avatar" class="w-full h-full object-cover" />
                    <div v-else class="w-full h-full bg-blue-500 text-white text-xs font-bold flex items-center justify-center">
                      {{ getInitials(member.name) }}
                    </div>
                  </div>
                  <div class="flex flex-col min-w-0">
                    <span class="text-xs font-medium text-gray-800 truncate">{{ member.name }}</span>
                    <span class="text-[10px]" :class="isUserOnline(member.id) ? 'text-green-500' : 'text-gray-400'">
                      {{ isUserOnline(member.id) ? 'آنلاین' : 'آفلاین' }}
                    </span>
                  </div>
                </div>

                <!-- نشان نقش کاربر -->
                <span
                  v-if="member.role === 'owner'"
                  class="text-[10px] font-bold bg-amber-50 text-amber-600 border border-amber-200 px-2 py-0.5 rounded-md"
                >
                  مالک
                </span>
                <span
                  v-else-if="member.role === 'admin'"
                  class="text-[10px] font-bold bg-blue-50 text-blue-600 border border-blue-200 px-2 py-0.5 rounded-md"
                >
                  مدیر
                </span>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick, computed } from 'vue'
import axios from 'axios'
import '../echo'

const props = defineProps({
    user:       Object,
    room:       Object,
    chat_name:  String,
    user_role:  String,
    chats:      Array,
    other_user: Object,
})

defineEmits(['back'])

const newMessage = ref('')
const messages = ref([])
const chatBox = ref(null)
const showScrollDownBtn = ref(false)
const isSmooth = ref(false)

const activeMenuIndex = ref(null)
const replyingTo = ref(null)
const onlineUsers = ref([])


const showProfileModal = ref(false)
const isEditingBio = ref(false)
const isSavingBio = ref(false)
const bioInput = ref('')
const fileInput = ref(null)


const currentRoomAvatar = ref('')
const currentBio = ref('')
const roomMembers = ref([])


const canManageGroup = computed(() => {
    if (props.room.type === 'private') return false

    return ['owner', 'admin'].includes(props.user_role)
})

const isPartnerOnline = computed(() => {
    if (!props.other_user) return false
    return onlineUsers.value.some(u => u.id === props.other_user.id)
})

const isUserOnline = (userId) => {
    return onlineUsers.value.some(u => u.id === userId)
}

const openProfileModal = () => {
    showProfileModal.value = true
}

const closeProfileModal = () => {
    showProfileModal.value = false
    isEditingBio.value = false
}


const selectedAvatarFile = ref(null)
const avatarPreviewUrl = ref(null)
const isUploadingAvatar = ref(false)


const onFileSelected = (e) => {
    const file = e.target.files[0]
    if (!file) return

    selectedAvatarFile.value = file

    avatarPreviewUrl.value = URL.createObjectURL(file)
}


const cancelAvatarSelection = () => {
    if (avatarPreviewUrl.value) {
        URL.revokeObjectURL(avatarPreviewUrl.value)
    }
    selectedAvatarFile.value = null
    avatarPreviewUrl.value = null
    if (fileInput.value) {
        fileInput.value.value = ''
    }
}

const confirmUploadAvatar = async () => {
    if (!selectedAvatarFile.value || isUploadingAvatar.value) return
    isUploadingAvatar.value = true

    const formData = new FormData()
    formData.append('avatar', selectedAvatarFile.value)

    try {
        const res = await axios.post(`/chat/rooms/${props.room.id}/update-avatar`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
        if (res.data && res.data.avatar) {
            currentRoomAvatar.value = res.data.avatar
        }

        cancelAvatarSelection()
    } catch (err) {
        console.error('خطا در آپلود عکس:', err)
    } finally {
        isUploadingAvatar.value = false
    }
}

// حذف عکس گروه
const removeAvatar = async () => {
    try {
        await axios.delete(`/chat/rooms/${props.room.id}/remove-avatar`)
        currentRoomAvatar.value = null
    } catch (err) {
        console.error('خطا در حذف عکس:', err)
    }
}

// ذخیره بایو جدید
const saveBio = async () => {
    if (isSavingBio.value) return
    isSavingBio.value = true

    try {
        await axios.post(`/chat/rooms/${props.room.id}/update-saveBio`, {
            description: bioInput.value
        })
        currentBio.value = bioInput.value
        isEditingBio.value = false
    } catch (err) {
        console.error('خطا در ثبت توضیحات:', err)
    } finally {
        isSavingBio.value = false
    }
}

const getInitials = (name) => {
    if (!name) return '?'
    const parts = name.trim().split(' ')
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase()
    }
    return name.substring(0, 2).toUpperCase()
}

const formatLastSeen = (timestamp) => {
    if (!timestamp) return 'اخیر'
    const date = new Date(timestamp)
    return date.toLocaleTimeString('fa-IR', { hour: '2-digit', minute: '2-digit' })
}

const canSendMessage = computed(() => {
    if (props.room.type !== 'channel') {
        return true
    }
    return ['owner', 'admin'].includes(props.user_role)
})

const scrollToBottom = async () => {
    await nextTick()
    if (chatBox.value) {
        chatBox.value.scrollTop = chatBox.value.scrollHeight
    }
}

const scrollToFirstUnread = async (unreadMessageId) => {
    isSmooth.value = false
    await nextTick()

    requestAnimationFrame(() => {
        const element = document.getElementById(`msg-${unreadMessageId}`)
        if (element) {
            element.scrollIntoView({ behavior: 'auto', block: 'center' })
        } else {
            scrollToBottom()
        }

        setTimeout(() => {
            isSmooth.value = true
        }, 100)
    })
}

const formatTime = (timestamp) => {
    if (!timestamp) return ''
    const date = new Date(timestamp)
    return date.toLocaleTimeString('fa-IR', { hour: '2-digit', minute: '2-digit' })
}

const formatDateLabel = (timestamp) => {
    if (!timestamp) return ''
    const date = new Date(timestamp)
    const today = new Date()
    const yesterday = new Date(today)
    yesterday.setDate(yesterday.getDate() - 1)

    const isToday = date.toDateString() === today.toDateString()
    const isYesterday = date.toDateString() === yesterday.toDateString()

    if (isToday) return 'امروز'
    if (isYesterday) return 'دیروز'

    return date.toLocaleDateString('fa-IR', { year: 'numeric', month: 'long', day: 'numeric' })
}

const shouldShowDateHeader = (index) => {
    if (index === 0) return true
    const currentDate = new Date(messages.value[index].created_at).toDateString()
    const previousDate = new Date(messages.value[index - 1].created_at).toDateString()
    return currentDate !== previousDate
}

const handleScroll = () => {
    if (!chatBox.value) return
    const { scrollTop, scrollHeight, clientHeight } = chatBox.value
    showScrollDownBtn.value = (scrollHeight - scrollTop - clientHeight) > 200
}

const initMessages = () => {
    let firstUnreadId = null

    // مقداردهی حالت اولیه روم
    if (props.room.type === 'direct') {
        currentRoomAvatar.value = props.other_user?.avatar || null
        currentBio.value = props.other_user?.bio || 'بیوگرافی تنظیم نشده است.'
    } else {
        currentRoomAvatar.value = props.room.avatar || null
        currentBio.value = props.room.description || 'توضیحاتی برای این روم درج نشده است.'
        roomMembers.value = props.room.users || []
    }
    bioInput.value = currentBio.value

    messages.value = props.chats.map(msg => {
        const isSender = msg.sender_id === props.user.id

        if (!isSender && (msg.is_read == 0 || msg.is_read === false) && !firstUnreadId) {
            firstUnreadId = msg.id
        }

        return {
            id: msg.id,
            message: msg.message,
            user: isSender ? 'sender' : 'receiver',
            sender_name: msg.sender ? msg.sender.name : (msg.sender_name || 'کاربر'),
            sender_avatar: msg.sender ? msg.sender.avatar : msg.sender_avatar,
            reply_to: msg.reply_to,
            is_read: msg.is_read,
            created_at: msg.created_at || new Date().toISOString()
        }
    })

    if (firstUnreadId) {
        scrollToFirstUnread(firstUnreadId)
    } else {
        scrollToBottom()
        setTimeout(() => {
            isSmooth.value = true
        }, 100)
    }
}

const markAsRead = async (messageIds) => {
    if (!messageIds || messageIds.length === 0) return

    try {
        axios.defaults.headers.common["X-Socket-Id"] = Echo.socketId()
        await axios.post(`/chat/${props.room.id}/read`, {
            message_ids: messageIds
        })
    } catch (e) {
        console.error('خطا در به‌روزرسانی وضعیت سین پیام‌ها:', e)
    }
}

const toggleMessageMenu = (index) => {
  activeMenuIndex.value = activeMenuIndex.value === index ? null : index
}

const closeAllMenus = () => {
  activeMenuIndex.value = null
}

const startReply = (message) => {
  replyingTo.value = message
  activeMenuIndex.value = null
}

const cancelReply = () => {
  replyingTo.value = null
}

const truncateText = (text) => {
  if (!text) return ''
  return text.length > 30 ? text.substring(0, 30) + '...' : text
}

const deleteMessage = async (message, index) => {
  activeMenuIndex.value = null
  messages.value.splice(index, 1)
  try {
    if (message.id) {
      axios.defaults.headers.common["X-Socket-Id"] = Echo.socketId()
      await axios.delete(`/chat/messages/${message.id}`)
    }
  } catch (e) {
    console.error('خطا در حذف پیام:', e)
  }
}

let observer = null
const pendingReadIds = new Set()
let readTimeout = null

const setupIntersectionObserver = () => {
    const options = {
        root: chatBox.value,
        threshold: 0.1
    }

    observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const msgId = entry.target.getAttribute('data-msg-id')
                const isReceiver = entry.target.getAttribute('data-user') === 'receiver'
                const isUnread = entry.target.getAttribute('data-is-read') === '0' || entry.target.getAttribute('data-is-read') === 'false' || entry.target.getAttribute('data-is-read') === false

                if (msgId && isReceiver && isUnread) {
                    pendingReadIds.add(Number(msgId))

                    clearTimeout(readTimeout)
                    readTimeout = setTimeout(() => {
                        if (pendingReadIds.size > 0) {
                            const idsToSend = Array.from(pendingReadIds)
                            markAsRead(idsToSend)

                            messages.value.forEach(m => {
                                if (idsToSend.includes(m.id)) {
                                    m.is_read = 1
                                }
                            })

                            pendingReadIds.clear()
                        }
                    }, 300)
                }
            }
        })
    }, options)

    attachObserverToMessages()
}

const attachObserverToMessages = () => {
    if (!observer) return

    nextTick(() => {
        requestAnimationFrame(() => {
            const messageElements = chatBox.value?.querySelectorAll('.message-bubble')
            messageElements?.forEach(el => {
                observer.unobserve(el)
                observer.observe(el)
            })
        })
    })
}

const send = async () => {
    if (!newMessage.value.trim()) return

    const text = newMessage.value
    const replyPayload = replyingTo.value ? { ...replyingTo.value } : null

    newMessage.value = ''
    replyingTo.value = null

    messages.value.push({
      id: Date.now(),
      message: text,
      user: 'sender',
      reply_to: replyPayload,
      is_read: 0,
      created_at: new Date().toISOString()
    })

    scrollToBottom()

    try {
        axios.defaults.headers.common["X-Socket-Id"] = Echo.socketId()
        await axios.post(`/chat/${props.room.id}/messages`, {
            message: text,
            reply_to_id: replyPayload ? replyPayload.id : null
        })
    } catch(e) {
        console.error('خطا در ارسال:', e)
    }
}

const getCookie = (name) => {
    const value = `; ${document.cookie}`
    const parts = value.split(`; ${name}=`)
    if (parts.length === 2) return decodeURIComponent(parts.pop().split(';').shift())
    return null
}

const sendLastSeen = () => {
    const csrfMeta = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    const xsrfToken = getCookie('XSRF-TOKEN')

    fetch('/user/last-seen', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-XSRF-TOKEN': xsrfToken || '',
            'X-CSRF-TOKEN': csrfMeta || '',
            'Accept': 'application/json',
        },
        credentials: 'same-origin',
        keepalive: true
    }).catch(err => console.error('خطای ثبت Last Seen:', err))
}

const handleVisibilityOrUnload = () => {
    if (document.visibilityState === 'hidden') {
        sendLastSeen()
    }
}

onMounted(() => {
    initMessages()
    setupIntersectionObserver()
    sendLastSeen()

    Echo.private('message.' + props.room.id)
        .listen('MessageEvent', (e) => {
            const isSender = e.sender_id === props.user.id;

            messages.value.push({
                id: e.id,
                message: e.message,
                user: isSender ? 'sender' : 'receiver',
                sender_name: e.sender_name || (e.sender ? e.sender.name : 'کاربر'),
                sender_avatar: e.sender_avatar || (e.sender ? e.sender.avatar : null),
                reply_to: e.reply_to,
                is_read: e.is_read,
                created_at: e.created_at || new Date().toISOString()
            })

            attachObserverToMessages()

            if (!showScrollDownBtn.value || isSender) {
                scrollToBottom()
            }

            if (!isSender) {
                nextTick(() => {
                    if (!showScrollDownBtn.value) {
                        setTimeout(() => {
                            markAsRead([e.id]);
                        }, 200);
                    }
                })
            }
        })

    Echo.private('message.' + props.room.id)
        .listen('MessagesReadEvent', (e) => {
            if (e.message_ids && Array.isArray(e.message_ids)) {
                messages.value.forEach(msg => {
                    if (msg.user === 'sender' && e.message_ids.includes(msg.id)) {
                        msg.is_read = 1;
                    }
                })
            } else {
                messages.value.forEach(msg => {
                    if (msg.user === 'sender') {
                        msg.is_read = 1;
                    }
                })
            }
        })

    window.addEventListener('beforeunload', sendLastSeen)
    document.addEventListener('visibilitychange', handleVisibilityOrUnload)

    Echo.join(`chat.presence.${props.room.id}`)
        .here((users) => {
            onlineUsers.value = users
        })
        .joining((user) => {
            if (!onlineUsers.value.some(u => u.id === user.id)) {
                onlineUsers.value.push(user)
            }
        })
        .leaving((leavingUser) => {
            onlineUsers.value = onlineUsers.value.filter(u => u.id !== leavingUser.id)

            if (props.other_user && leavingUser.id === props.other_user.id) {
                props.other_user.last_seen_at = new Date().toISOString()
            }
        })
        .error((error) => {
            console.error('خطای Presence Channel:', error)
        })
})

onUnmounted(() => {
    window.removeEventListener('beforeunload', sendLastSeen)
    document.removeEventListener('visibilitychange', handleVisibilityOrUnload)

    sendLastSeen()
    Echo.leave(`chat.presence.${props.room.id}`)
})
</script>
