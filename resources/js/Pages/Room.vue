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
          @click.stop="openGallery"
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
          v-if="room.type === 'private' && isPartnerOnline"
          class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"
        ></span>
      </div>

      <!-- عنوان و وضعیت چت -->
      <div class="flex flex-col flex-1 min-w-0">
        <h2 class="font-bold text-gray-800 text-base leading-tight truncate">{{ chat_name }}</h2>

        <div class="text-xs font-medium mt-0.5">
          <span v-if="room.type === 'channel'" class="text-gray-400">کانال عمومی</span>
          <span v-else-if="room.type === 'group'" class="text-gray-400" dir="rtl">
            {{ roomMembers.length }} عضو ، {{ groupOnlineCount }} آنلاین
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
          <span class="bg-gray-200/80 backdrop-blur text-gray-600 text-[11px] font-medium px-3 py-1 rounded-full shadow-sm" dir="rtl">
            {{ formatDateLabel(m.created_at) }}
          </span>
        </div>

        <!-- حباب پیام -->
        <div
          :id="'msg-' + m.id"
          class="message-bubble flex gap-2 w-full relative"
          :data-msg-id="m.id"
          :data-sequence-id="m.sequence_id"
          :data-user="m.user"
          :class="m.user === 'sender' ? 'flex-row-reverse' : 'flex-row'"
        >
          <!-- آواتار فرستنده -->
          <div v-if="m.user === 'receiver'" class="w-8 h-8 rounded-full flex-shrink-0 self-end mb-1">
            <img
              v-if="m.sender_avatar || (room.type === 'private' && other_user?.avatar)"
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
              @click.stop="m.status !== 'pending' && toggleMessageMenu(index)"
              class="flex flex-col rounded-2xl shadow-sm text-sm leading-relaxed select-none transition-all overflow-hidden pb-1"
              :class="[
                m.user === 'sender'
                  ? 'bg-blue-600 text-white rounded-bl-none self-end'
                  : 'bg-white text-gray-800 rounded-br-none border border-gray-100 self-start',
                m.status === 'pending' ? 'opacity-70 cursor-wait' : 'cursor-pointer active:opacity-90'
              ]"
            >
            <!-- 🟢 بخش نمایش فوروارد شده از... -->
      <div
  v-if="m.forwarded_from && m.forwarded_from.sender_name"
  class="text-[11px] font-medium pt-2 px-3 flex items-center gap-1 select-none"
  :class="m.user === 'sender' ? 'text-blue-100' : 'text-gray-500'"
>
  <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
  </svg>
  <span>فوروارد شده از</span>

  <a
    v-if="m.forwarded_from.sender_username"
    :href="route('user.profile', { username: m.forwarded_from.sender_username })"
    @click.stop
    class="font-bold underline hover:opacity-80 transition-opacity"
    :class="m.user === 'sender' ? 'text-white' : 'text-blue-600'"
  >
    {{ m.forwarded_from.sender_name }}
  </a>
  <span v-else class="font-bold" :class="m.user === 'sender' ? 'text-white' : 'text-gray-800'">
    {{ m.forwarded_from.sender_name }}
  </span>
</div>
              <div
                v-if="m.reply_to"
                @click.stop="scrollToMessage(m.reply_to.id)"
                class="text-xs px-3 py-1.5 border-r-4 mt-2 mx-2 rounded flex flex-col text-right overflow-hidden cursor-pointer hover:opacity-80 transition-opacity"
                :class="m.user === 'sender'
                  ? 'bg-blue-700/40 border-white text-blue-100'
                  : 'bg-gray-100 border-blue-500 text-gray-500'"
              >
                <span class="font-bold text-[10px]" :class="m.user === 'sender' ? 'text-white' : 'text-blue-600'">{{ m.reply_to.sender?.name || m.reply_to.sender_name || 'کاربر' }}:</span>
                <span class="truncate mt-0.5">{{ truncateText(m.reply_to.message) }}</span>
              </div>

              <div class="px-3 pt-2.5 pb-1 text-right relative min-w-[120px] break-words">
                <div
                  v-if="room.type === 'group' && m.user === 'receiver' && m.sender_name"
                  class="text-[11px] font-bold text-blue-600 mb-1 leading-tight truncate max-w-full select-none"
                >
                  {{ m.sender_name }}
                </div>

                <!-- نمایش فایل در صورت وجود -->
                <MediaViewer v-if="m.file_path || m.attachment_id" :message="m" />

                <!-- متن پیام/کپشن -->
                <div v-if="m.message" class="whitespace-pre-wrap break-words">
                  {{ m.message }}
                </div>

                <div
                  class="flex items-center justify-end gap-1 mt-1 text-[10px] select-none"
                  :class="m.user === 'sender' ? 'text-blue-100' : 'text-gray-400'"
                  dir="ltr"
                >
                  <span>{{ formatTime(m.created_at) }}</span>

                  <template v-if="m.user === 'sender'">

                  <svg v-if="m.status === 'pending'" class="w-3.5 h-3.5 text-blue-200 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <svg v-else-if="m.status === 'failed'" class="w-3.5 h-3.5 text-red-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>


                  <template v-else>

                    <svg v-if="m.views_count > 0" class="w-3.5 h-3.5 text-sky-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7M10 17l4 4L23 9" />
                    </svg>

                    <svg v-else class="w-3.5 h-3.5 text-blue-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                  </template>
                </template>
                </div>
              </div>
            </div>

            <!-- منوی عملیات پیام -->
            <div v-if="activeMenuIndex === index"
              class="absolute top-full mt-1 bg-white border border-gray-100 rounded-xl shadow-xl py-1 z-30 min-w-[110px]"
              :class="m.user === 'sender' ? 'left-0' : 'right-0'"
            >
              <button @click="startReply(m)" class="w-full text-right px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                پاسخ دادن
              </button>
              <button @click="openForwardModal(m)" class="w-full text-right px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 flex items-center gap-2">
              <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
              </svg>
              فوروارد
            </button>
              <button v-if="m.user === 'sender' || user_role === 'admin' || user_role === 'owner'" @click="openDeleteModal(m, index)" class="w-full text-right px-3 py-2 text-xs text-red-600 hover:bg-red-50 flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
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
            <span class="font-bold text-blue-600">
                پاسخ به {{ replyingTo.sender_name || replyingTo.sender?.name || 'کاربر' }}
            </span>
            <span class="text-gray-500 truncate mt-0.5">{{ replyingTo.message }}</span>
          </div>
          <button @click="cancelReply" class="text-gray-400 hover:text-gray-600 p-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>

        <input
          type="file"
          ref="fileInput"
          class="hidden"
          @change="handleFileSelect"
        />

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
          <button
            @click="triggerFileInput"
            type="button"
            class="text-gray-400 hover:text-gray-600 p-2.5 rounded-2xl hover:bg-gray-100 active:scale-95 transition-all"
            title="ارسال فایل"
          >
            <svg class="w-5 h-5 rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
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

    <!-- 🗑️ مودال تأیید حذف پیام -->
    <DeleteMessageModal
      :isOpen="showDeleteModal"
      @close="showDeleteModal = false"
      @confirm="handleConfirmDelete"
    />

    <!-- 🖼️ گالری تصاویر آواتار -->
    <AvatarGalleryModal
      :show="showGalleryModal"
      :avatars="avatarList"
      :current-avatar="currentRoomAvatar"
      :title="chat_name"
      :can-delete="user_role === 'admin' || user_role === 'owner'"
      @delete="handleDeleteAvatar"
      @close="showGalleryModal = false"
    />

    <!-- 👤 مودال پروفایل و مدیریت روم -->
    <RoomProfileModal
  :isOpen="showProfileModal"
  :room="room"
  :chatName="chat_name"
  :userRole="user_role"
  :otherUser="other_user"
  :currentAvatar="currentRoomAvatar"
  :bio="currentBio"
  :members="roomMembers"
  :onlineUsers="globalOnlineUsers"
  @close="showProfileModal = false"
  @update-bio="newBio => currentBio = newBio"
  @add-member-success="newMember => roomMembers.push(newMember)"
  @upload-avatar="newPath => avatarList.unshift(newPath)"
  @remove-avatar="handleDeleteAvatar"
  @role-changed="({ userId, newRole }) => {
    const member = roomMembers.find(m => m.id === userId)
    if (member) member.role = newRole
  }"
  @member-kicked="kickedUserId => {
    roomMembers = roomMembers.filter(m => m.id !== kickedUserId)
  }"
/>
    <ForwardMessageModal
      :isOpen="showForwardModal"
      :message="selectedMessageToForward"
      :chats="all_chats"
      @close="showForwardModal = false"
      @forward="handleConfirmForward"
    />
    <FileUploadModal
  :isOpen="showFileUploadModal"
  :file="selectedFile"
  :roomId="room.id"
  @close="showFileUploadModal = false"
  @uploaded="handleFileUploaded"
/>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick, computed } from 'vue'
import axios from 'axios'
import '../echo'
import DeleteMessageModal from './../Components/chat/DeleteMessageModal.vue'
import AvatarGalleryModal from './../Components/chat/AvatarGalleryModal.vue'
import RoomProfileModal from './../Components/chat/RoomProfileModal.vue'
import ForwardMessageModal from './../Components/chat/ForwardMessageModal.vue'
import { globalOnlineUsers } from '../app.js'
import FileUploadModal from './../Components/chat/FileUploadModal.vue'
import MediaViewer from './../Components/chat/MediaViewer.vue'

const props = defineProps({
  user:       Object,
  room:       Object,
  chat_name:  String,
  user_role:  String,
  chats:      Array,
  user_last_read: Number,
  other_user: Object,
  all_chats:  Array,
  avatars:    Array,
  members:    Array,
})

const emit = defineEmits(['back', 'update:room', 'update:otherUser'])

const newMessage = ref('')
const messages = ref([])
const chatBox = ref(null)
const showScrollDownBtn = ref(false)
const isSmooth = ref(false)

const fileInput = ref(null)
const selectedFile = ref(null)
const showFileUploadModal = ref(false)

const triggerFileInput = () => {
  fileInput.value?.click()
}

const handleFileSelect = (e) => {
  const file = e.target.files[0]
  if (file) {
    selectedFile.value = file
    showFileUploadModal.value = true
  }
  e.target.value = ''
}

const handleFileUploaded = (serverMessage) => {

  const isSender = serverMessage.sender_id === props.user.id
const attachment = serverMessage.attachment
  messages.value.push({
    id: serverMessage.id,
    sequence_id: serverMessage.sequence_id,
    message: serverMessage.message,
    attachment_id: attachment?.id || null,
    file_path: attachment.file_path,
    file_type: attachment.file_type,
    file_name: attachment.file_name,
    user: isSender ? 'sender' : 'receiver',
    sender_name: serverMessage.sender ? serverMessage.sender.name : 'کاربر',
    views_count: 0,
    created_at: serverMessage.created_at || new Date().toISOString(),

  })
  scrollToBottom()
}

const activeMenuIndex = ref(null)
const replyingTo = ref(null)

const showProfileModal = ref(false)
const avatarList = ref([...(props.avatars || [])])

const currentRoomAvatar = computed(() => {
  if (props.room.type === 'private') {
    return avatarList.value.length > 0 ? avatarList.value[0] : (props.other_user?.avatar || null)
  }
  return avatarList.value.length > 0 ? avatarList.value[0] : (props.room.avatar || null)
})
const currentBio = ref('')
const roomMembers = ref([])

const initProfileData = () => {
  if (props.room.type === 'private') {
    currentBio.value = props.other_user?.bio || ''
  } else {
    currentBio.value = props.room.description || props.room.bio || ''
  }
  roomMembers.value = props.members || []
}

const groupOnlineCount = computed(() => {
  if (!roomMembers.value || !globalOnlineUsers.value) return 0

  return roomMembers.value.filter(member =>
    globalOnlineUsers.value.some(onlineId => String(onlineId) === String(member.id))
  ).length
})

const showDeleteModal = ref(false)
const selectedMessageToDelete = ref(null)
const selectedMessageIndexToDelete = ref(null)

const isPartnerOnline = computed(() => {
  if (!props.other_user) return false
  return globalOnlineUsers.value.includes(props.other_user.id)
})

const openProfileModal = () => {
  showProfileModal.value = true
}

const showGalleryModal = ref(false)

const openGallery = () => {
  if (currentRoomAvatar.value) {
    showGalleryModal.value = true
  }
}

const handleDeleteAvatar = async ({ image, index }) => {
  if (!confirm('آیا از حذف این عکس مطمئن هستید؟')) return

  try {
    axios.defaults.headers.common["X-Socket-Id"] = Echo.socketId()
    await axios.post(`/chat/rooms/${props.room.id}/delete-avatar`, {
      image_url: image
    })

    avatarList.value.splice(index, 1)

    if (avatarList.value.length === 0) {
      showGalleryModal.value = false
    }
  } catch (err) {
    console.error('خطا در حذف عکس:', err)
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

  setTimeout(() => {
    const element = document.getElementById(`msg-${unreadMessageId}`)
    if (element) {
      element.scrollIntoView({ behavior: 'auto', block: 'start' })
    } else {
      scrollToBottom()
    }

    setTimeout(() => {
      isSmooth.value = true
    }, 150)
  }, 50)
}

const showForwardModal = ref(false)
const selectedMessageToForward = ref(null)

const openForwardModal = (message) => {
  activeMenuIndex.value = null
  selectedMessageToForward.value = message
  showForwardModal.value = true
}

const handleConfirmForward = async ({ targetRoom, message }) => {
  try {
    axios.defaults.headers.common["X-Socket-Id"] = Echo.socketId()
    await axios.post(`/chat/${targetRoom.id}/messages`, {
      message: message.message,
      forwarded_from_id: message.id
    })

  } catch (err) {
    console.error('خطا در فوروارد پیام:', err)
  } finally {
    selectedMessageToForward.value = null
  }
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

const scrollToMessage = (msgId) => {
  if (!msgId) return
  const el = document.getElementById(`msg-${msgId}`)
  if (el) {
    el.scrollIntoView({ behavior: 'smooth', block: 'center' })
    el.classList.add('ring-2', 'ring-indigo-500')
    setTimeout(() => el.classList.remove('ring-2', 'ring-indigo-500'), 2000)
  }
}

const initMessages = () => {
  let firstUnreadId = null
  initProfileData()

  let currentUserLastRead = 0

  if (props.room.type === 'private') {
    currentUserLastRead = props.room?.pivot?.last_read_sequence_id || props.user_last_read || 0
  } else {
    const me = props.members?.find(m => Number(m.id) === Number(props.user.id))
    currentUserLastRead = me?.last_read_sequence_id || me?.pivot?.last_read_sequence_id || 0
  }

  messages.value = props.chats.map(msg => {
    const isSender = msg.sender_id === props.user.id

    if (!isSender && msg.sequence_id > currentUserLastRead && !firstUnreadId) {
      firstUnreadId = msg.id
    }


    let fFrom = null
    const rawForward = msg.forwarded_from || msg.forwardedFrom

    if (rawForward) {
      fFrom = {
        sender_name: rawForward.sender?.name || rawForward.sender_name || 'کاربر',
        sender_username: rawForward.sender?.username || rawForward.sender_username || null
      }
    }

    const attachment = msg.attachments && msg.attachments.length > 0 ? msg.attachments[0] : null

    return {
      id: msg.id,
      sequence_id: msg.sequence_id,
      message: msg.message,
      user: isSender ? 'sender' : 'receiver',
      sender_name: msg.sender ? msg.sender.name : (msg.sender_name || 'کاربر'),
      sender_avatar: msg.sender ? msg.sender.avatar : msg.sender_avatar,
      forwarded_from: fFrom,
      file_path: msg.file_path || attachment?.file_path || null,
      file_type: msg.file_type || attachment?.mime_type || null,
      file_name: msg.file_name || attachment?.original_name || null,
      attachment_id: attachment?.id || null,
      reply_to: msg.parent || msg.reply_to || null,
      views_count: msg.views_count || 0,
      created_at: msg.created_at || new Date().toISOString()
    }
  })

  nextTick(() => {
    if (firstUnreadId) {
      scrollToFirstUnread(firstUnreadId)
    } else {
      scrollToBottom()
      setTimeout(() => { isSmooth.value = true }, 100)
    }
  })
}

const markAsRead = async (maxSequenceId) => {
  if (!maxSequenceId) return

  try {
    axios.defaults.headers.common["X-Socket-Id"] = Echo.socketId()
    await axios.post(`/chat/${props.room.id}/read`, {
      sequence_id: maxSequenceId
    })
  } catch (e) {
    console.error('خطا در ثبت وضعیت خوانده‌شده:', e)
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

const openDeleteModal = (message, index) => {
  if (String(message.id).startsWith('temp-') || message.status === 'pending') {
    alert('پیام هنوز ارسال نشده است. لطفا لحظه‌ای صبر کنید.')
    return
  }
  activeMenuIndex.value = null
  selectedMessageToDelete.value = message
  selectedMessageIndexToDelete.value = index
  showDeleteModal.value = true
}

const handleConfirmDelete = async (deleteType) => {
  const message = selectedMessageToDelete.value
  const index = selectedMessageIndexToDelete.value

  showDeleteModal.value = false
  if (!message) return

  messages.value.splice(index, 1)

  try {
    if (message.id) {
      axios.defaults.headers.common["X-Socket-Id"] = Echo.socketId()
      await axios.delete(`/chat/messages/${message.id}/${props.room.id}`, {
        data: { delete_type: deleteType }
      })
    }
  } catch (e) {
    console.error('خطا در حذف پیام:', e)
  } finally {
    selectedMessageToDelete.value = null
    selectedMessageIndexToDelete.value = null
  }
}

let observer = null
let readTimeout = null
let maxObservedSeq = 0

const setupIntersectionObserver = () => {
  if (observer) observer.disconnect()

  observer = new IntersectionObserver((entries) => {
    let newlyObservedMaxSeq = 0

    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const seqId = Number(entry.target.getAttribute('data-sequence-id'))
        const isReceiver = entry.target.getAttribute('data-user') === 'receiver'

        if (seqId && isReceiver && seqId > maxObservedSeq) {
          if (seqId > newlyObservedMaxSeq) {
            newlyObservedMaxSeq = seqId
          }
        }
      }
    })

    if (newlyObservedMaxSeq > maxObservedSeq) {
      maxObservedSeq = newlyObservedMaxSeq

      clearTimeout(readTimeout)
      readTimeout = setTimeout(() => {
        markAsRead(maxObservedSeq)
      }, 300)
    }
  }, { root: chatBox.value, threshold: 0.1 })

  attachObserverToMessages()
}

const attachObserverToMessages = () => {
  if (!observer) return

  nextTick(() => {
    requestAnimationFrame(() => {
      const messageElements = chatBox.value?.querySelectorAll('.message-bubble')
      messageElements?.forEach(el => {
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

  const tempId = 'temp-' + Date.now()

  const tempMessage = {
    id: tempId,
    sequence_id: null,
    message: text,
    user: 'sender',
    reply_to: replyPayload,
    views_count: 0,
    status: 'pending',
    created_at: new Date().toISOString()
  }

  messages.value.push(tempMessage)
  scrollToBottom()

  try {
    axios.defaults.headers.common["X-Socket-Id"] = Echo.socketId()
    const res = await axios.post(`/chat/${props.room.id}/messages`, {
      message: text,
      reply_to_id: replyPayload ? replyPayload.id : null
    })


    const serverMessage = res.data.message || res.data

    const index = messages.value.findIndex(m => m.id === tempId)
    if (index !== -1 && serverMessage) {

      messages.value[index].id = serverMessage.id
      messages.value[index].sequence_id = serverMessage.sequence_id
      messages.value[index].views_count = serverMessage.views_count || 0
      messages.value[index].status = 'sent'


      nextTick(() => {
        attachObserverToMessages()
      })
    }
  } catch(e) {
    console.error('خطا در ارسال:', e)
    const index = messages.value.findIndex(m => m.id === tempId)
    if (index !== -1) {
      messages.value[index].status = 'failed'
    }
  }
}

onMounted(() => {
  initMessages()
  setupIntersectionObserver()

  const channel = Echo.private('message.' + props.room.id)

  channel.listen('MessageEvent', (e) => {
    const isSender = e.sender_id === props.user.id
    const exists = messages.value.some(m => String(m.id) === String(e.id))
    if (exists) return

    messages.value.push({
    id: e.id,
    sequence_id: e.sequence_id,
    message: e.message,
    user: isSender ? 'sender' : 'receiver',
    sender_name: e.sender_name || (e.sender ? e.sender.name : 'کاربر'),
    sender_avatar: e.sender_avatar || (e.sender ? e.sender.avatar : null),
    reply_to: e.reply_to,
    attachment_id: e.attachment_id || null,
    file_path: e.file_path || null,
    file_type: e.mime_type || e.file_type || null,
    file_name: e.original_name || e.file_name || null,
    forwarded_from: e.forwarded_from,
    views_count: e.views_count || 0,
    created_at: e.created_at || new Date().toISOString()
    })

    attachObserverToMessages()

    if (!showScrollDownBtn.value || isSender) {
        scrollToBottom()
    }

    if (!isSender && !showScrollDownBtn.value) {
      nextTick(() => {
        setTimeout(() => {
          markAsRead(e.sequence_id)
        }, 200)
      })
    }
  })

  channel.listen('MessagesReadEvent', (e) => {
  messages.value.forEach(msg => {
    if (msg.user === 'sender' && msg.sequence_id <= e.sequenceId) {
      msg.views_count = (msg.views_count || 0) + 1
    }
  })
})

  channel.listen('DeleteEvent', (e) => {
    const index = messages.value.findIndex(m => String(m.id) === String(e.messageId))
    if (index !== -1) {
      messages.value.splice(index, 1)
    }
  })

  channel.listen('BioEvent', (e) => {
    currentBio.value = e.description
  })

  channel.listen('AvatarEvent', (e) => {
    if (e.path) {
      avatarList.value.unshift(e.path)
    }
  })

  channel.listen('DeleteAvatarEvent', (e) => {
    if (e.path) {
      const targetUrl = e.path
      const index = avatarList.value.findIndex(img => img === targetUrl)

      if (index !== -1) {
        avatarList.value.splice(index, 1)
      }

      if (avatarList.value.length === 0) {
        showGalleryModal.value = false
      }
    }
  })

  channel.listen('UserRoleChangedEvent', (e) => {
  const member = roomMembers.value.find(m => Number(m.id) === Number(e.targetUserId))
  if (member) {
    member.role = e.newRole
  }
  if (Number(e.targetUserId) === Number(props.user.id)) {
    user_role.value = e.newRole
  }
})


channel.listen('UserKickedRoomEvent', (e) => {
  roomMembers.value = roomMembers.value.filter(m => Number(m.id) !== Number(e.targetUserId))

  if (Number(e.targetUserId) === Number(props.user.id)) {
    alert('شما از این گروه اخراج شدید.')
    window.location.href = '/dashboard'
  }
})
})

onUnmounted(() => {
  if (observer) {
    observer.disconnect()
    observer = null
  }

  Echo.leave(`message.${props.room.id}`)
})
</script>
