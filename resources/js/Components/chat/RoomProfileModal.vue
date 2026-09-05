<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4"
    @click.self="$emit('close')"
  >
    <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[85vh] transform transition-all animate-fade-in">

      <!-- هدر مودال -->
      <div class="bg-gradient-to-b from-blue-600 to-blue-500 pt-8 pb-6 px-6 flex flex-col items-center relative text-white flex-shrink-0">
        <button
          @click="$emit('close')"
          class="absolute top-4 left-4 text-white/80 hover:text-white p-1 rounded-full bg-white/10 hover:bg-white/20 transition-all"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>

        <!-- تصویر پروفایل -->
        <div class="relative group w-24 h-24 rounded-full border-4 border-white/20 shadow-lg overflow-hidden mb-3">
          <img
            v-if="avatarPreviewUrl || currentAvatar"
            :src="avatarPreviewUrl || currentAvatar"
            :alt="chatName"
            class="w-full h-full object-cover"
          />
          <div v-else class="w-full h-full bg-white/20 flex items-center justify-center font-bold text-2xl text-white">
            {{ getInitials(chatName) }}
          </div>

          <!-- مدیریت عکس (ارسال/حذف) -->
          <div
            v-if="canManageGroup"
            class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2"
          >
            <label class="p-2 bg-white/20 hover:bg-white/40 rounded-full cursor-pointer text-white transition-all" title="تغییر تصویر">
              <input type="file" ref="fileInput" accept="image/*" class="hidden" @change="onFileSelected" />
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
            </label>

            <button
              v-if="currentAvatar"
              @click="$emit('remove-avatar')"
              class="p-2 bg-red-500/80 hover:bg-red-600 rounded-full text-white transition-all"
              title="حذف تصویر"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
          </div>
        </div>

        <!-- پیش‌نمایش تصویر جدید انتخاب شده -->
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

        <h3 class="text-lg font-bold text-white text-center">{{ chatName }}</h3>

        <!-- لینک به پروفایل کاربر مستقیم در پی وی -->
        <a
          v-if="room.type === 'private' && otherUser?.username"
          :href="`/@${otherUser.username}`"
          class="text-xs text-blue-100 hover:underline mt-0.5"
        >
          @{{ otherUser.username }}
        </a>
      </div>

      <!-- بدنه اصلی -->
      <div class="p-5 space-y-5 text-right overflow-y-auto flex-1" dir="rtl">

        <!-- 🔗 لینک عمومی کانال -->
        <div v-if="room.type === 'channel'" class="space-y-1.5">
          <span class="text-xs font-semibold text-gray-400">لینک عمومی کانال</span>
          <div class="bg-blue-50/60 p-3 rounded-2xl border border-blue-100 flex items-center justify-between gap-2">
            <span class="text-xs text-blue-600 font-mono truncate dir-ltr select-all">
              {{ channelLink }}
            </span>
            <button
              @click="copyChannelLink"
              class="px-2.5 py-1 text-[11px] bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 active:scale-95 transition-all flex items-center gap-1 flex-shrink-0"
            >
              <span>{{ isCopied ? 'کپی شد!' : 'کپی لینک' }}</span>
            </button>
          </div>
        </div>

        <!-- 📝 بیوگرافی / توضیحات -->
        <div class="space-y-1.5">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-gray-400">
              {{ room.type === 'private' ? 'بیوگرافی کاربر' : (room.type === 'channel' ? 'توضیحات کانال' : 'توضیحات گروه') }}
            </span>
            <button
              v-if="room.type !== 'private' && canManageGroup && !isEditingBio"
              @click="openBioEdit"
              class="text-xs text-blue-600 font-medium hover:underline"
            >
              ویرایش
            </button>
          </div>

          <div v-if="isEditingBio" class="space-y-2">
            <textarea
              v-model="bioInput"
              rows="3"
              class="w-full text-xs text-gray-700 bg-gray-50 border border-blue-300 rounded-xl p-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
              :placeholder="room.type === 'channel' ? 'توضیحات کانال را وارد کنید...' : 'توضیحات گروه را وارد کنید...'"
            ></textarea>
            <div class="flex justify-end gap-2">
              <button @click="isEditingBio = false" class="px-3 py-1 text-xs text-gray-500 hover:bg-gray-100 rounded-lg">انصراف</button>
              <button @click="saveBio" :disabled="isSavingBio" class="px-3 py-1 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                {{ isSavingBio ? 'در حال ثبت...' : 'ذخیره' }}
              </button>
            </div>
          </div>

          <div v-else class="bg-gray-50 p-3 rounded-2xl border border-gray-100">
            <p v-if="bio" class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ bio }}</p>
            <p v-else class="text-xs text-gray-400 italic">
              {{ room.type === 'private' ? 'بیوگرافی ثبت نشده است.' : 'توضیحاتی ثبت نشده است.' }}
            </p>
          </div>
        </div>

        <!-- ➕ افزودن ممبر -->
        <div v-if="canAddMember" class="pt-1">
          <button
            @click="openAddMemberModal"
            class="w-full py-2.5 px-4 bg-blue-50 text-blue-600 hover:bg-blue-100 font-semibold text-xs rounded-2xl flex items-center justify-center gap-2 transition-all active:scale-95 border border-blue-200"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            <span>{{ room.type === 'channel' ? 'افزودن عضو به کانال' : 'افزودن عضو به گروه' }}</span>
          </button>
        </div>

        <!-- 👥 لیست اعضای گروه یا کانال -->
        <div v-if="room.type !== 'private'" class="space-y-2">
          <div class="flex items-center justify-between border-b border-gray-100 pb-2">
            <span class="text-xs font-semibold text-gray-400">اعضا</span>
            <span class="text-xs bg-blue-50 text-blue-600 font-bold px-2 py-0.5 rounded-full">{{ members.length }} نفر</span>
          </div>

          <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
            <div
              v-for="member in members"
              :key="member.id"
              class="flex items-center justify-between py-2 px-2 hover:bg-gray-50 rounded-xl transition-colors border border-transparent hover:border-gray-100"
            >
              <!-- اطلاعات کاربر همراه با لینک پروفایل -->
              <a
                :href="member.username ? `/@${member.username}` : '#'"
                class="flex items-center gap-2.5 min-w-0 flex-1 group"
              >
                <div class="w-8 h-8 rounded-full overflow-hidden bg-slate-200 flex-shrink-0">
                  <img v-if="member.avatar" :src="member.avatar" class="w-full h-full object-cover" />
                  <div v-else class="w-full h-full bg-blue-500 text-white text-xs font-bold flex items-center justify-center">
                    {{ getInitials(member.name) }}
                  </div>
                </div>
                <div class="flex flex-col min-w-0">
                  <span class="text-xs font-medium text-gray-800 truncate group-hover:text-blue-600 transition-colors">
                    {{ member.name }}
                  </span>
                  <span class="text-[10px]" :class="isOnline(member.id) ? 'text-green-500 font-medium' : 'text-gray-400'">
                    {{ getUserStatus(member) }}
                  </span>
                </div>
              </a>

              <!-- بخش مدیریت نقش و اخراج -->
              <div class="flex items-center gap-1.5 flex-shrink-0">
                <!-- نمایش برچسب نقش -->
                <span v-if="member.role === 'owner'" class="text-[10px] font-bold bg-amber-50 text-amber-600 border border-amber-200 px-2 py-0.5 rounded-md">مالک</span>
                <span v-else-if="member.role === 'admin'" class="text-[10px] font-bold bg-blue-50 text-blue-600 border border-blue-200 px-2 py-0.5 rounded-md">مدیر</span>

                <!-- دکمه‌های عملیاتی برای مدیران/مالک -->
                <template v-if="canManageMember(member)">
                  <!-- دکمه ارتقا یا عزل ادمین -->
                  <button
                    @click="toggleAdminRole(member)"
                    class="px-2 py-0.5 text-[10px] font-bold rounded-md border transition-all active:scale-95"
                    :class="member.role === 'admin' ? 'border-amber-200 text-amber-700 bg-amber-50 hover:bg-amber-100' : 'border-blue-200 text-blue-700 bg-blue-50 hover:bg-blue-100'"
                  >
                    {{ member.role === 'admin' ? 'عزل' : 'ارتقا' }}
                  </button>

                  <!-- دکمه اخراج -->
                  <button
                    @click="kickMember(member)"
                    class="p-1 text-red-500 hover:bg-red-50 rounded-md transition-colors"
                    title="اخراج از گروه"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </template>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- ➕ زیرمودال افزودن کاربر با لیست مخاطبین -->
    <div v-if="showAddMemberModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-[70] flex items-center justify-center p-4" @click.self="showAddMemberModal = false" dir="rtl">
      <div class="bg-white rounded-3xl p-5 w-full max-w-sm space-y-4 shadow-2xl animate-fade-in flex flex-col max-h-[80vh]">
        <div class="flex items-center justify-between border-b pb-3 flex-shrink-0">
          <h4 class="text-sm font-bold text-gray-800">{{ room.type === 'channel' ? 'افزودن عضو به کانال' : 'افزودن عضو به گروه' }}</h4>
          <button @click="showAddMemberModal = false" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>

        <!-- باکس جستجو -->
        <div class="flex-shrink-0">
          <input
            type="text"
            v-model="searchQuery"
            placeholder="جستجوی مخاطب..."
            class="w-full text-xs bg-gray-50 border border-gray-200 rounded-xl p-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <!-- پیام‌های خطا یا موفقیت -->
        <p v-if="addMemberError" class="text-xs text-red-500 font-medium text-center flex-shrink-0">{{ addMemberError }}</p>
        <p v-if="addMemberSuccess" class="text-xs text-green-600 font-medium text-center flex-shrink-0">{{ addMemberSuccess }}</p>

        <!-- لیست مخاطبین / چت‌های اخیر -->
        <div class="flex-1 overflow-y-auto space-y-1.5 pr-1">
          <div v-if="isLoadingContacts" class="text-center py-6 text-xs text-gray-400">
            در حال دریافت مخاطبین...
          </div>

          <div v-else-if="filteredContacts.length === 0" class="text-center py-6 text-xs text-gray-400">
            مخاطبی یافت نشد.
          </div>

          <div
            v-else
            v-for="user in filteredContacts"
            :key="user.id"
            @click="!isUserInGroup(user.id) && !addingUserId && selectAndAddUser(user)"
            class="flex items-center justify-between p-2 rounded-xl transition-all"
            :class="[
              isUserInGroup(user.id) ? 'bg-gray-50 opacity-60 cursor-not-allowed' : 'hover:bg-blue-50/60 cursor-pointer active:scale-98'
            ]"
          >
            <div class="flex items-center gap-2.5 min-w-0">
              <div class="w-9 h-9 rounded-full overflow-hidden bg-slate-200 flex-shrink-0">
                <img v-if="user.avatar" :src="user.avatar" class="w-full h-full object-cover" />
                <div v-else class="w-full h-full bg-blue-500 text-white text-xs font-bold flex items-center justify-center">
                  {{ getInitials(user.name) }}
                </div>
              </div>
              <div class="flex flex-col min-w-0">
                <span class="text-xs font-semibold text-gray-800 truncate">{{ user.name }}</span>
                <span v-if="user.username" class="text-[10px] text-gray-400 truncate">@{{ user.username }}</span>
              </div>
            </div>

            <div>
              <span v-if="isUserInGroup(user.id)" class="text-[10px] bg-gray-200 text-gray-600 px-2 py-0.5 rounded-md font-medium">
                عضو گروه
              </span>
              <span v-else-if="addingUserId === user.id" class="text-[10px] bg-blue-100 text-blue-600 px-2 py-0.5 rounded-md font-medium">
                در حال افزودن...
              </span>
              <span v-else class="text-[10px] bg-blue-600 text-white px-2 py-0.5 rounded-md font-medium">
                افزودن
              </span>
            </div>
          </div>
        </div>

        <div class="pt-2 border-t flex-shrink-0">
          <button @click="showAddMemberModal = false" class="w-full py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 font-medium text-xs rounded-xl">انصراف</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'

const props = defineProps({
  isOpen: Boolean,
  room: Object,
  chatName: String,
  userRole: String,
  otherUser: Object,
  currentAvatar: String,
  bio: String,
  members: { type: Array, default: () => [] },
  onlineUsers: { type: Array, default: () => [] }
})

const emit = defineEmits(['close', 'update-bio', 'add-member-success', 'upload-avatar', 'remove-avatar'])

const isEditingBio = ref(false)
const isSavingBio = ref(false)
const bioInput = ref('')
const isCopied = ref(false)

const fileInput = ref(null)
const selectedAvatarFile = ref(null)
const avatarPreviewUrl = ref(null)
const isUploadingAvatar = ref(false)

const showAddMemberModal = ref(false)
const searchQuery = ref('')
const contactsList = ref([])
const isLoadingContacts = ref(false)
const addingUserId = ref(null)
const addMemberError = ref('')
const addMemberSuccess = ref('')

const canManageGroup = computed(() => props.room.type !== 'private' && ['owner', 'admin'].includes(props.userRole))
const canAddMember = computed(() => props.room.type === 'group' || (props.room.type === 'channel' && ['owner', 'admin'].includes(props.userRole)))
const channelLink = computed(() => props.room.slug ? `${window.location.origin}/join/${props.room.slug}` : `${window.location.origin}/chat/${props.room.id}`)

// بررسی سطح دسترسی مدیریت روی یک عضو مشخص
const canManageMember = (member) => {
  
  if (member.role === 'owner') return false

  if (props.userRole === 'owner') return true

  if (props.userRole === 'admin' && (member.role === 'member' || !member.role)) return true

  return false
}

// تغییر یا عزل نقش ادمینی
const toggleAdminRole = async (member) => {
  const newRole = member.role === 'admin' ? 'member' : 'admin'
  try {
    axios.defaults.headers.common["X-Socket-Id"] = window.Echo?.socketId()
    await axios.post(`/chat/rooms/${props.room.id}/change-role`, {
      user_id: member.id,
      role: newRole
    })
    emit('role-changed', { userId: member.id, newRole })
  } catch (err) {
    console.error('خطا در تغییر نقش کاربر:', err)
  }
}

// اخراج عضو از گروه یا کانال
const kickMember = async (member) => {
  if (!confirm(`آیا از اخراج ${member.name} اطمینان دارید؟`)) return

  try {
    axios.defaults.headers.common["X-Socket-Id"] = window.Echo?.socketId()
    await axios.post(`/chat/rooms/${props.room.id}/kick`, {
      user_id: member.id
    })

    emit('member-kicked', member.id)
  } catch (err) {
    console.error('خطا در اخراج کاربر:', err)
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
    return `ساعت ${timeString}`
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

const isOnline = (userId) => props.onlineUsers.includes(userId)

const getUserStatus = (member) => {
  if (isOnline(member.id)) return 'آنلاین'
  return member.last_seen_at ? `آخرین بازدید: ${formatLastSeen(member.last_seen_at)}` : 'آخرین بازدید نامشخص'
}

const copyChannelLink = () => {
  navigator.clipboard.writeText(channelLink.value).then(() => {
    isCopied.value = true
    setTimeout(() => isCopied.value = false, 2000)
  })
}

const openBioEdit = () => {
  bioInput.value = props.bio || ''
  isEditingBio.value = true
}

const saveBio = async () => {
  if (isSavingBio.value) return
  isSavingBio.value = true
  try {
    await axios.post(`/chat/rooms/${props.room.id}/update-description`, { description: bioInput.value })
    emit('update-bio', bioInput.value)
    isEditingBio.value = false
  } catch (err) {
    console.error('خطا در بروزرسانی بیو:', err)
  } finally {
    isSavingBio.value = false
  }
}

const onFileSelected = (e) => {
  const file = e.target.files[0]
  if (!file) return
  selectedAvatarFile.value = file
  avatarPreviewUrl.value = URL.createObjectURL(file)
}

const cancelAvatarSelection = () => {
  if (avatarPreviewUrl.value) URL.revokeObjectURL(avatarPreviewUrl.value)
  selectedAvatarFile.value = null
  avatarPreviewUrl.value = null
}

const confirmUploadAvatar = async () => {
  if (!selectedAvatarFile.value || isUploadingAvatar.value) return
  isUploadingAvatar.value = true
  const formData = new FormData()
  formData.append('avatar', selectedAvatarFile.value)

  try {
    axios.defaults.headers.common["X-Socket-Id"] = window.Echo?.socketId()
    const res = await axios.post(`/chat/rooms/${props.room.id}/update-avatar`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    emit('upload-avatar', res.data.avatar)
    cancelAvatarSelection()
  } catch (err) {
    console.error('خطا در آپلود عکس:', err)
  } finally {
    isUploadingAvatar.value = false
  }
}

const isUserInGroup = (userId) => {
  return props.members.some(m => Number(m.id) === Number(userId))
}

const filteredContacts = computed(() => {
  if (!searchQuery.value.trim()) return contactsList.value
  const q = searchQuery.value.toLowerCase()
  return contactsList.value.filter(c =>
    (c.name && c.name.toLowerCase().includes(q)) ||
    (c.username && c.username.toLowerCase().includes(q))
  )
})

const openAddMemberModal = async () => {
  showAddMemberModal.value = true
  addMemberError.value = ''
  addMemberSuccess.value = ''
  searchQuery.value = ''

  if (contactsList.value.length === 0) {
    isLoadingContacts.value = true
    try {
      window.axios.defaults.withCredentials = true;
      window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
      const res = await axios.get('/chat/contacts-and-recent')
      contactsList.value = res.data.users || res.data || []
    } catch (err) {
      console.error('خطا در دریافت لیست مخاطبین:', err)
    } finally {
      isLoadingContacts.value = false
    }
  }
}

const selectAndAddUser = async (user) => {
  addingUserId.value = user.id
  addMemberError.value = ''
  addMemberSuccess.value = ''

  try {
    const res = await axios.post(`/chat/rooms/${props.room.id}/add-member`, {
      user_id: user.id,
      username: user.username
    })

    addMemberSuccess.value = `${user.name} با موفقیت اضافه شد.`
    if (res.data.member) {
      emit('add-member-success', res.data.member)
    } else {
      emit('add-member-success', user)
    }

    setTimeout(() => {
      addMemberSuccess.value = ''
    }, 2000)

  } catch (err) {
    addMemberError.value = err.response?.data?.message || 'خطا در افزودن کاربر.'
  } finally {
    addingUserId.value = null
  }
}
</script>
