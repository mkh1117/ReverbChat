<template>
  <div class="h-screen flex flex-col bg-slate-100 relative" @click="closeAllMenus">

    <!-- هدر چت -->
    <header class="bg-white border-b border-gray-200 px-4 py-3 flex items-center gap-3 shadow-sm z-10">
      <!-- دکمه بازگشت -->
        <a :href="route('dashboard')"
            class="w-9 h-9 rounded-full flex items-center justify-center text-gray-500 hover:bg-gray-100 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
          </svg>
        </a>

      <div class="flex flex-col flex-1">
        <h2 class="font-bold text-gray-800 text-base leading-tight">{{ chat_name }}</h2>
        <span class="text-xs font-medium mt-0.5" :class="room.type === 'channel' || room.type === 'group' ? 'text-gray-400' : 'text-green-500'">
          {{ room.type === 'channel' ? 'کانال' : room.type === 'group' ? 'گروه' : 'آنلاین' }}
        </span>
      </div>
    </header>

    <!-- باکس پیام‌ها همراه با لیسنر اسکرول -->
    <div
  ref="chatBox"
  @scroll="handleScroll"
  class="flex-1 overflow-y-auto p-4 space-y-4 relative"
  :class="{ 'scroll-smooth': isSmooth }"
>
      <div
    v-for="(m, index) in messages"
    :key="m.id || index"
    :id="'msg-' + m.id"

    class="message-bubble flex flex-col w-full relative"
    :data-msg-id="m.id"
    :data-user="m.user"
    :data-is-read="m.is_read"

    :class="m.user === 'sender' ? 'items-left text-left' : 'items-right text-right'"
  >
        <!-- حباب پیام همراه با منوی کلیک -->
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
            <!-- نمایش پیام ریپلای شده -->
            <div
              v-if="m.reply_to"
              class="text-xs px-3 py-1.5 border-r-4 mt-2 mx-2 rounded flex flex-col text-right"
              :class="m.user === 'sender'
                ? 'bg-blue-700/40 border-white text-blue-100'
                : 'bg-gray-100 border-blue-500 text-gray-500'"
            >
              <span class="font-bold text-[10px]" :class="m.user === 'sender' ? 'text-white' : 'text-blue-600'">پاسخ به:</span>
              <span class="truncate mt-0.5">{{ truncateText(m.reply_to.message) }}</span>
            </div>

            <!-- متن اصلی پیام و وضعیت تیک -->
            <div class="px-3 pt-3 pb-1.5 whitespace-pre-wrap text-right relative min-w-[70px]">
              {{ m.message }}

              <!-- بخش تیک سین (فقط برای فرستنده نمایش داده می‌شود) -->
              <div v-if="m.user === 'sender'" class="flex justify-end mt-1 text-[10px] opacity-80" dir="ltr">
                <!-- دو تیک آبی (خوانده شده) -->
                <svg v-if="m.is_read" class="w-4 h-4 text-sky-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7M10 17l4 4L23 9" />
                </svg>
                <!-- یک تیک خاکستری (ارسال شده اما خوانده نشده) -->
                <svg v-else class="w-4 h-4 text-blue-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
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
            <button v-if="m.user === 'sender'" @click="deleteMessage(m, index)" class="w-full text-right px-3 py-2 text-xs text-red-600 hover:bg-red-50 flex items-center gap-2">
              <svg class="w-3.5 h-3.5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              حذف پیام
            </button>
          </div>
        </div>

      </div>
    </div>

    <!-- دکمه شناور برای اسکرول به پایین‌ترین نقطه چت -->
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
      <!-- باکس پیش‌نمایش ریپلای -->
      <div v-if="replyingTo" class="flex items-center justify-between bg-blue-50 border-r-4 border-blue-500 px-3 py-2 rounded-lg w-full text-xs animate-fade-in">
        <div class="flex flex-col text-right overflow-hidden">
          <span class="font-bold text-blue-600">پاسخ به پیام</span>
          <span class="text-gray-500 truncate mt-0.5">{{ replyingTo.message }}</span>
        </div>
        <button @click="cancelReply" class="text-gray-400 hover:text-gray-600 p-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>

      <!-- فیلد اصلی ارسال پیام -->
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
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, nextTick , watch} from 'vue'
import axios from 'axios'
import '../echo'

const props = defineProps({
    user:      Object,
    room:      Object,
    chat_name: String,
    chats:     Array,
})

defineEmits(['back'])

const newMessage = ref('')
const messages = ref([])
const chatBox = ref(null)
const showScrollDownBtn = ref(false)
const isSmooth = ref(false) // کنترل داینامیک انیمیشن اسکرول

const activeMenuIndex = ref(null)
const replyingTo = ref(null)

const scrollToBottom = async () => {
    await nextTick()
    if (chatBox.value) {
        chatBox.value.scrollTop = chatBox.value.scrollHeight
    }
}

// اسکرول اختصاصی به اولین پیام خوانده نشده
const scrollToFirstUnread = async (unreadMessageId) => {
    isSmooth.value = false // غیرفعال کردن اسکرول نرم برای لود اولیه
    await nextTick()

    requestAnimationFrame(() => {
        const element = document.getElementById(`msg-${unreadMessageId}`)
        if (element) {
            element.scrollIntoView({ behavior: 'auto', block: 'center' })
        } else {
            scrollToBottom()
        }

        // بعد از اینکه اسکرول اولیه انجام شد، اسکرول نرم را برای پیام‌های بعدی فعال می‌کنیم
        setTimeout(() => {
            isSmooth.value = true
        }, 100)
    })
}

// مانیتور کردن وضعیت اسکرول چت باکس
const handleScroll = () => {
    if (!chatBox.value) return
    const { scrollTop, scrollHeight, clientHeight } = chatBox.value
    showScrollDownBtn.value = (scrollHeight - scrollTop - clientHeight) > 200
}

const initMessages = () => {
    let firstUnreadId = null

    messages.value = props.chats.map(msg => {
        const isSender = msg.sender_id === props.user.id

        // پیدا کردن اولین پیام خوانده‌نشده‌ای که متعلق به مخاطب است
        if (!isSender && (msg.is_read == 0 || msg.is_read === false) && !firstUnreadId) {
            firstUnreadId = msg.id
        }

        return {
            id: msg.id,
            message: msg.message,
            user: isSender ? 'sender' : 'receiver',
            reply_to: msg.reply_to,
            is_read: msg.is_read
        }
    })

    // هندل کردن موقعیت شروع اسکرول بدون تداخل انیمیشن
    if (firstUnreadId) {
        scrollToFirstUnread(firstUnreadId)
    } else {
        scrollToBottom()
        setTimeout(() => {
            isSmooth.value = true
        }, 100)
    }
}

// تغییر متد برای ارسال آی‌دی پیام‌های خاصی که دیده شده‌اند
const markAsRead = async (messageIds) => {
    if (!messageIds || messageIds.length === 0) return

    try {
        axios.defaults.headers.common["X-Socket-Id"] = Echo.socketId()
        await axios.post(`/chat/${props.room.id}/read`, {
            message_ids: messageIds // آرایه‌ای از آی‌دی پیام‌هایی که کاربر واقعاً دیده است
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
const pendingReadIds = new Set() // برای جمع‌آوری آی‌دی پیام‌های خوانده شده
let readTimeout = null

const setupIntersectionObserver = () => {
    const options = {
        root: chatBox.value,
        threshold: 0.1 // کاهش آستانه به 10% جهت تشخیص سریع‌تر پیام در صفحه
    }

    observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const msgId = entry.target.getAttribute('data-msg-id')
                const isReceiver = entry.target.getAttribute('data-user') === 'receiver'
                const isUnread = entry.target.getAttribute('data-is-read') === '0' || entry.target.getAttribute('data-is-read') === 'false' || entry.target.getAttribute('data-is-read') === false

                console.log("id:",msgId,"---isReceiver",isReceiver,"--isUnread",isUnread)
                if (msgId && isReceiver && isUnread) {
                    pendingReadIds.add(Number(msgId))

                    clearTimeout(readTimeout)
                    readTimeout = setTimeout(() => {
                        if (pendingReadIds.size > 0) {
                            const idsToSend = Array.from(pendingReadIds)
                            console.log('👁️ پیام‌های زیر خوانده شدند (ارسال به سرور):', idsToSend)
                            markAsRead(idsToSend)

                            // آپدیت موقت وضعیت در فرانت تا قبل از پاسخ ایونت
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

    // استفاده از nextTick و انیمیشن فریم مرورگر برای اطمینان از رندر کامل تگ‌ها در DOM
    nextTick(() => {
        requestAnimationFrame(() => {
            const messageElements = chatBox.value?.querySelectorAll('.message-bubble')
            messageElements?.forEach(el => {
                // ابتدا رصد قبلی را قطع می‌کنیم تا تداخل ایجاد نشود
                observer.unobserve(el)
                // مجدداً رصد را شروع می‌کنیم
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
      is_read: 0
    })

    scrollToBottom()

    try {
        axios.defaults.headers.common["X-Socket-Id"] = Echo.socketId()
        const res = await axios.post(`/chat/${props.room.id}/messages`, {
            message: text,
            reply_to_id: replyPayload ? replyPayload.id : null
        })
        // if (res.data && res.data.message_id) {
        //     const targetMsg = messages.value.find(m => m.id === tempId)
        //     if (targetMsg) {
        //         targetMsg.id = res.data.message_id
        //     }
        // }
    } catch(e) {
        console.error('خطا در ارسال:', e)
    }
}

onMounted(() => {
    initMessages()
    setupIntersectionObserver()

   // ۱. گوش دادن به پیام‌های جدید
// ۱. گوش دادن به پیام‌های جدید
Echo.private('message.' + props.room.id)
    .listen('MessageEvent', (e) => {
        const isSender = e.sender_id === props.user.id;

        messages.value.push({
            id: e.id,
            message: e.message,
            user: isSender ? 'sender' : 'receiver',
            reply_to: e.reply_to,
            is_read: e.is_read
        })

        attachObserverToMessages()

        if (!showScrollDownBtn.value || isSender) {
            scrollToBottom()
        }

        // اگر گیرنده هستیم و کاربر در انتهای صفحه چت حضور دارد:
        if (!isSender) {
            nextTick(() => {
                // اگر اسکرول در پایین صفحه است، پیام خوانده شده تلقی می‌شود
                if (!showScrollDownBtn.value) {
                    setTimeout(() => {
                        markAsRead([e.id]);
                    }, 200);
                }
            })
        }
    })

   // ۲. گوش دادن به رویداد سین خوردن پیام‌ها
Echo.private('message.' + props.room.id)
    .listen('MessagesReadEvent', (e) => {
        // مطمئن شوید متغیر آرایه در ایونت لاراول وجود دارد
        if (e.message_ids && Array.isArray(e.message_ids)) {
            messages.value.forEach(msg => {
                if (msg.user === 'sender' && e.message_ids.includes(msg.id)) {
                    msg.is_read = 1; // تیک آبی فعال می‌شود
                }
            })
        } else {
            // حالت زاپاس اگر کل چت خوانده شده باشد
            messages.value.forEach(msg => {
                if (msg.user === 'sender') {
                    msg.is_read = 1;
                }
            })
        }
    })
})
</script>
